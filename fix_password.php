<?php
$mysqli = new mysqli('localhost', 'root', '', 'project_management_db');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Updating password for admin user...\n";
echo "New hash: $hash\n\n";

// Update both secret and secret2 fields
$stmt = $mysqli->prepare("UPDATE auth_identities SET secret = ?, secret2 = ? WHERE user_id = 1 AND type = 'email_password'");
$stmt->bind_param('ss', $hash, $hash);
$stmt->execute();

echo "✓ Password updated in both secret and secret2 fields\n";
echo "\nLogin with:\n";
echo "Email: admin@example.com\n";
echo "Password: admin123\n";

$mysqli->close();
