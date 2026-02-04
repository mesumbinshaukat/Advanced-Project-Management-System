<?php
// Shield actually stores:
// - name = email (for display/lookup)
// - secret = password hash (for verification)
// - secret2 = password hash backup (for verification)

$mysqli = new mysqli('localhost', 'root', '', 'project_management_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== Correct Shield Configuration ===\n\n";

$email = 'admin@example.com';
$password = 'admin123';

// Generate proper password hash
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

echo "Setting up identity correctly:\n";
echo "  name = $email (for lookup)\n";
echo "  secret = password_hash (for verification)\n";
echo "  secret2 = password_hash (backup)\n\n";

// Update with correct structure
$stmt = $mysqli->prepare("UPDATE auth_identities SET name=?, secret=?, secret2=? WHERE user_id=1 AND type='email_password'");
$stmt->bind_param('sss', $email, $passwordHash, $passwordHash);
$stmt->execute();

echo "✓ Identity updated\n\n";

// Verify
$result = $mysqli->query("SELECT name, LEFT(secret, 30) as secret_start, LEFT(secret2, 30) as secret2_start FROM auth_identities WHERE user_id=1 AND type='email_password'");
$verify = $result->fetch_assoc();

echo "Verification:\n";
echo "  name: {$verify['name']}\n";
echo "  secret: {$verify['secret_start']}...\n";
echo "  secret2: {$verify['secret2_start']}...\n\n";

if ($verify['name'] === $email) {
    echo "  ✓ Email in name field\n";
}

$fullResult = $mysqli->query("SELECT secret, secret2 FROM auth_identities WHERE user_id=1 AND type='email_password'")->fetch_assoc();
if (password_verify($password, $fullResult['secret']) && password_verify($password, $fullResult['secret2'])) {
    echo "  ✓ Password hashes verified\n";
}

echo "\n=== Configuration Complete ===\n";
$mysqli->close();
