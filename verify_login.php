<?php
// Direct database test - no CodeIgniter bootstrap
$mysqli = new mysqli('localhost', 'root', '', 'project_management_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== Testing Login Credentials ===\n\n";

$email = 'admin@example.com';
$password = 'admin123';

echo "Testing with:\n";
echo "Email: $email\n";
echo "Password: $password\n\n";

// Check user
echo "1. Checking user...\n";
$result = $mysqli->query("SELECT * FROM users WHERE username='admin'");
$user = $result->fetch_assoc();

if ($user) {
    echo "   ✓ User found: ID={$user['id']}, Username={$user['username']}, Active={$user['active']}\n\n";
} else {
    echo "   ✗ User not found!\n\n";
    exit(1);
}

// Check identity
echo "2. Checking auth_identities...\n";
$result = $mysqli->query("SELECT * FROM auth_identities WHERE user_id={$user['id']} AND type='email_password'");
$identity = $result->fetch_assoc();

if ($identity) {
    echo "   ✓ Identity found: Name={$identity['name']}, Type={$identity['type']}\n";
    echo "   Password hash: " . substr($identity['secret'], 0, 30) . "...\n\n";
} else {
    echo "   ✗ Identity not found!\n\n";
    exit(1);
}

// Check group
echo "3. Checking group assignment...\n";
$result = $mysqli->query("SELECT * FROM auth_groups_users WHERE user_id={$user['id']}");
$group = $result->fetch_assoc();

if ($group) {
    echo "   ✓ Group assigned: {$group['group']}\n\n";
} else {
    echo "   ✗ No group assigned!\n\n";
}

// Test password
echo "4. Testing password verification...\n";
$passwordVerified = password_verify($password, $identity['secret']);

if ($passwordVerified) {
    echo "   ✓ Password verification successful!\n\n";
} else {
    echo "   ✗ Password verification FAILED!\n";
    echo "   Current hash: {$identity['secret']}\n";
    echo "   Generating new hash for '$password'...\n";
    $newHash = password_hash($password, PASSWORD_BCRYPT);
    echo "   New hash: $newHash\n\n";
    
    $stmt = $mysqli->prepare("UPDATE auth_identities SET secret=? WHERE user_id=? AND type='email_password'");
    $stmt->bind_param('si', $newHash, $user['id']);
    $stmt->execute();
    
    echo "   ✓ Password updated in database\n";
    echo "   Please try logging in again with: $email / $password\n\n";
}

// Verify user is active
if ($user['active'] != 1) {
    echo "5. User is NOT active! Activating...\n";
    $mysqli->query("UPDATE users SET active=1 WHERE id={$user['id']}");
    echo "   ✓ User activated\n\n";
}

echo "=== Test Complete ===\n";
echo "\nLogin credentials:\n";
echo "Email: $email\n";
echo "Password: $password\n";

$mysqli->close();
