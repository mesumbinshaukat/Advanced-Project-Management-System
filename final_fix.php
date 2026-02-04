<?php
// The issue: Shield's findByCredentials() looks for email in the 'secret' field
// but we have it in the 'name' field. We need to put the email in the 'secret' field
// and the password hash in 'secret2' field.

$mysqli = new mysqli('localhost', 'root', '', 'project_management_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== Final Fix for Shield Authentication ===\n\n";

$email = 'admin@example.com';
$password = 'admin123';

// Get current identity
$result = $mysqli->query("SELECT * FROM auth_identities WHERE user_id=1 AND type='email_password'");
$identity = $result->fetch_assoc();

echo "Current state:\n";
echo "  name: {$identity['name']}\n";
echo "  secret: " . substr($identity['secret'], 0, 20) . "...\n";
echo "  secret2: " . substr($identity['secret2'], 0, 20) . "...\n\n";

// Shield expects:
// - secret = email (for lookup)
// - secret2 = password hash (for verification)
echo "Fixing to match Shield's expectations:\n";
echo "  secret = email (for lookup)\n";
echo "  secret2 = password hash (for verification)\n\n";

$passwordHash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $mysqli->prepare("UPDATE auth_identities SET secret=?, secret2=?, name=? WHERE user_id=1 AND type='email_password'");
$stmt->bind_param('sss', $email, $passwordHash, $email);
$stmt->execute();

echo "✓ Updated auth_identities\n";
echo "  secret = $email\n";
echo "  secret2 = " . substr($passwordHash, 0, 30) . "...\n";
echo "  name = $email\n\n";

// Verify
$result = $mysqli->query("SELECT * FROM auth_identities WHERE user_id=1 AND type='email_password'");
$verify = $result->fetch_assoc();

echo "Verification:\n";
if ($verify['secret'] === $email) {
    echo "  ✓ secret field contains email\n";
} else {
    echo "  ✗ secret field incorrect\n";
}

if (password_verify($password, $verify['secret2'])) {
    echo "  ✓ secret2 field contains valid password hash\n";
} else {
    echo "  ✗ secret2 field password verification failed\n";
}

echo "\n=== Fix Complete ===\n";
$mysqli->close();
