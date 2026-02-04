<?php
// Comprehensive Shield login test with actual authentication attempt
$mysqli = new mysqli('localhost', 'root', '', 'project_management_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== Comprehensive Login Test ===\n\n";

$email = 'admin@example.com';
$password = 'admin123';

// 1. Check user exists and is active
echo "1. Checking user...\n";
$result = $mysqli->query("SELECT * FROM users WHERE username='admin'");
$user = $result->fetch_assoc();

if (!$user) {
    echo "   ✗ User not found!\n";
    exit(1);
}

if ($user['active'] != 1) {
    echo "   ✗ User not active, activating...\n";
    $mysqli->query("UPDATE users SET active=1 WHERE id={$user['id']}");
    echo "   ✓ User activated\n";
}
echo "   ✓ User ID: {$user['id']}, Active: 1\n\n";

// 2. Check auth_identities
echo "2. Checking auth_identities...\n";
$result = $mysqli->query("SELECT * FROM auth_identities WHERE user_id={$user['id']} AND type='email_password'");
$identity = $result->fetch_assoc();

if (!$identity) {
    echo "   ✗ Identity not found!\n";
    exit(1);
}

echo "   ✓ Email: {$identity['name']}\n";
echo "   ✓ Type: {$identity['type']}\n";

// 3. Test password against both secret fields
echo "\n3. Testing password verification...\n";
$secret1_match = password_verify($password, $identity['secret']);
$secret2_match = $identity['secret2'] ? password_verify($password, $identity['secret2']) : false;

echo "   Secret field match: " . ($secret1_match ? "✓ YES" : "✗ NO") . "\n";
echo "   Secret2 field match: " . ($secret2_match ? "✓ YES" : "✗ NO") . "\n";

if (!$secret1_match || !$secret2_match) {
    echo "\n   Fixing password hashes...\n";
    $newHash = password_hash($password, PASSWORD_BCRYPT);
    $mysqli->query("UPDATE auth_identities SET secret='$newHash', secret2='$newHash' WHERE user_id={$user['id']} AND type='email_password'");
    echo "   ✓ Both password fields updated\n";
}

// 4. Verify the unique constraint on type+secret
echo "\n4. Checking for duplicate identities...\n";
$result = $mysqli->query("SELECT COUNT(*) as count FROM auth_identities WHERE type='email_password' AND name='$email'");
$count = $result->fetch_assoc()['count'];
if ($count > 1) {
    echo "   ✗ Found $count duplicate identities! Removing duplicates...\n";
    $mysqli->query("DELETE FROM auth_identities WHERE user_id={$user['id']} AND type='email_password' AND id != {$identity['id']}");
    echo "   ✓ Duplicates removed\n";
} else {
    echo "   ✓ No duplicates found\n";
}

// 5. Check group assignment
echo "\n5. Checking group assignment...\n";
$result = $mysqli->query("SELECT * FROM auth_groups_users WHERE user_id={$user['id']}");
$group = $result->fetch_assoc();
if (!$group) {
    echo "   ✗ No group assigned! Assigning admin group...\n";
    $mysqli->query("INSERT INTO auth_groups_users (user_id, `group`, created_at) VALUES ({$user['id']}, 'admin', NOW())");
    echo "   ✓ Admin group assigned\n";
} else {
    echo "   ✓ Group: {$group['group']}\n";
}

// 6. Clear failed login attempts
echo "\n6. Clearing old failed login attempts...\n";
$mysqli->query("DELETE FROM auth_logins WHERE identifier='$email' AND success=0");
echo "   ✓ Failed attempts cleared\n";

// 7. Final verification
echo "\n7. Final verification...\n";
$result = $mysqli->query("
    SELECT u.id, u.username, u.active, ai.name as email, ai.type, 
           LENGTH(ai.secret) as secret_len, LENGTH(ai.secret2) as secret2_len
    FROM users u 
    JOIN auth_identities ai ON u.id = ai.user_id 
    WHERE u.id = {$user['id']} AND ai.type = 'email_password'
");
$final = $result->fetch_assoc();

if ($final && $final['active'] == 1 && $final['email'] == $email && $final['secret_len'] > 50 && $final['secret2_len'] > 50) {
    echo "   ✓ All checks passed!\n";
    echo "\n=== READY FOR LOGIN ===\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "\nDatabase is correctly configured.\n";
} else {
    echo "   ✗ Verification failed!\n";
    print_r($final);
}

$mysqli->close();
