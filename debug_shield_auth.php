<?php
// Deep dive into Shield's authentication process
$mysqli = new mysqli('localhost', 'root', '', 'project_management_db');

$email = 'admin@example.com';
$password = 'admin123';

echo "=== Deep Shield Authentication Debug ===\n\n";

// Check the exact query Shield uses to find users
echo "1. Shield's user lookup query:\n";
$query = "SELECT ai.*, u.* 
          FROM auth_identities ai 
          JOIN users u ON ai.user_id = u.id 
          WHERE ai.type = 'email_password' 
          AND ai.name = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "   ✗ Shield cannot find user with this query!\n";
    echo "   Checking what's in auth_identities:\n";
    
    $check = $mysqli->query("SELECT id, user_id, type, name FROM auth_identities WHERE type='email_password'");
    while ($row = $check->fetch_assoc()) {
        echo "   - ID: {$row['id']}, User: {$row['user_id']}, Name: '{$row['name']}'\n";
    }
    exit(1);
}

echo "   ✓ User found via Shield's query\n";
echo "   User ID: {$user['user_id']}\n";
echo "   Username: {$user['username']}\n";
echo "   Active: {$user['active']}\n\n";

// Check password verification
echo "2. Password verification:\n";
$secret = $user['secret'];
$secret2 = $user['secret2'];

echo "   Testing against 'secret' field...\n";
if (password_verify($password, $secret)) {
    echo "   ✓ Password matches 'secret'\n";
} else {
    echo "   ✗ Password does NOT match 'secret'\n";
    echo "   Secret hash: $secret\n";
}

echo "   Testing against 'secret2' field...\n";
if (password_verify($password, $secret2)) {
    echo "   ✓ Password matches 'secret2'\n";
} else {
    echo "   ✗ Password does NOT match 'secret2'\n";
    echo "   Secret2 hash: $secret2\n";
}

// Check if user is active
echo "\n3. User status check:\n";
if ($user['active'] == 1) {
    echo "   ✓ User is active\n";
} else {
    echo "   ✗ User is NOT active (value: {$user['active']})\n";
    $mysqli->query("UPDATE users SET active=1 WHERE id={$user['user_id']}");
    echo "   ✓ User activated\n";
}

// Check for force_reset
echo "\n4. Force reset check:\n";
if ($user['force_reset'] == 0) {
    echo "   ✓ No forced password reset\n";
} else {
    echo "   ✗ Force reset is enabled!\n";
    $mysqli->query("UPDATE auth_identities SET force_reset=0 WHERE user_id={$user['user_id']} AND type='email_password'");
    echo "   ✓ Force reset disabled\n";
}

// Check deleted_at
echo "\n5. Soft delete check:\n";
if ($user['deleted_at'] === null) {
    echo "   ✓ User is not soft-deleted\n";
} else {
    echo "   ✗ User is soft-deleted: {$user['deleted_at']}\n";
    $mysqli->query("UPDATE users SET deleted_at=NULL WHERE id={$user['user_id']}");
    echo "   ✓ Soft delete removed\n";
}

// Generate fresh password hash and update
echo "\n6. Regenerating password hash:\n";
$freshHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
echo "   New hash: " . substr($freshHash, 0, 30) . "...\n";

$stmt = $mysqli->prepare("UPDATE auth_identities SET secret=?, secret2=? WHERE user_id=? AND type='email_password'");
$stmt->bind_param('ssi', $freshHash, $freshHash, $user['user_id']);
$stmt->execute();
echo "   ✓ Password updated in both fields\n";

// Verify the update
echo "\n7. Verifying update:\n";
$verify = $mysqli->query("SELECT secret FROM auth_identities WHERE user_id={$user['user_id']} AND type='email_password'")->fetch_assoc();
if (password_verify($password, $verify['secret'])) {
    echo "   ✓ New password hash verified successfully\n";
} else {
    echo "   ✗ Verification failed!\n";
}

echo "\n=== Configuration Complete ===\n";
$mysqli->close();
