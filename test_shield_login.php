<?php
// Test Shield login process
$mysqli = new mysqli('localhost', 'root', '', 'project_management_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== Shield Login Debug ===\n\n";

$email = 'admin@example.com';
$password = 'admin123';

// Step 1: Find user by email in auth_identities
echo "1. Looking for user by email in auth_identities...\n";
$stmt = $mysqli->prepare("SELECT ai.*, u.* FROM auth_identities ai JOIN users u ON ai.user_id = u.id WHERE ai.type = 'email_password' AND ai.name = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo "   ✓ User found via email identity\n";
    echo "   User ID: {$user['user_id']}\n";
    echo "   Username: {$user['username']}\n";
    echo "   Email: {$user['name']}\n";
    echo "   Active: {$user['active']}\n";
    echo "   Password hash: " . substr($user['secret'], 0, 30) . "...\n\n";
    
    // Step 2: Verify password
    echo "2. Verifying password...\n";
    if (password_verify($password, $user['secret'])) {
        echo "   ✓ Password is correct!\n\n";
        
        // Step 3: Check if user is active
        echo "3. Checking user status...\n";
        if ($user['active'] == 1) {
            echo "   ✓ User is active\n\n";
            echo "   ✅ LOGIN SHOULD WORK!\n\n";
        } else {
            echo "   ✗ User is NOT active (active={$user['active']})\n";
            echo "   Activating user...\n";
            $mysqli->query("UPDATE users SET active=1 WHERE id={$user['user_id']}");
            echo "   ✓ User activated\n\n";
        }
    } else {
        echo "   ✗ Password is INCORRECT!\n";
        echo "   Expected hash: {$user['secret']}\n";
        echo "   Testing password: $password\n\n";
        
        // Generate new hash
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        echo "   Generating new hash: $newHash\n";
        $mysqli->query("UPDATE auth_identities SET secret='$newHash' WHERE user_id={$user['user_id']} AND type='email_password'");
        echo "   ✓ Password updated\n\n";
    }
} else {
    echo "   ✗ User NOT found by email!\n";
    echo "   Shield cannot find user with email: $email\n\n";
    
    // Check if identity exists at all
    echo "   Checking all identities for user_id=1...\n";
    $result = $mysqli->query("SELECT * FROM auth_identities WHERE user_id=1");
    while ($row = $result->fetch_assoc()) {
        echo "   - ID: {$row['id']}, Type: {$row['type']}, Name: '{$row['name']}'\n";
    }
    echo "\n";
}

// Check recent login attempts
echo "4. Recent login attempts:\n";
$result = $mysqli->query("SELECT * FROM auth_logins ORDER BY date DESC LIMIT 3");
while ($row = $result->fetch_assoc()) {
    $status = $row['success'] ? '✓ SUCCESS' : '✗ FAILED';
    echo "   $status - Email: {$row['identifier']}, User ID: " . ($row['user_id'] ?: 'NULL') . ", Date: {$row['date']}\n";
}

$mysqli->close();
echo "\n=== Test Complete ===\n";
