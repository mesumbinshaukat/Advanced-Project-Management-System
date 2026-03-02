<?php
/**
 * Verify and Seed Superadmin Credentials
 * Visit: http://yourdomain.com/verify_and_seed.php
 */

header('Content-Type: text/html; charset=utf-8');

// Load environment variables
$envFile = __DIR__ . '/../.env';
$env = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B'\"");
        $env[$key] = $value;
    }
}

// Database config
$db_host = $env['database.default.hostname'] ?? 'localhost';
$db_name = $env['database.default.database'] ?? 'project_db';
$db_user = $env['database.default.username'] ?? 'root';
$db_pass = $env['database.default.password'] ?? '';
$db_port = $env['database.default.port'] ?? 3306;

// Connect to database
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify and Seed Superadmin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .success { color: green; padding: 10px; background: #e8f5e9; margin: 10px 0; border-left: 4px solid green; }
        .error { color: red; padding: 10px; background: #ffebee; margin: 10px 0; border-left: 4px solid red; }
        .info { color: #1976d2; padding: 10px; background: #e3f2fd; margin: 10px 0; border-left: 4px solid #1976d2; }
        .warning { color: #f57c00; padding: 10px; background: #fff3e0; margin: 10px 0; border-left: 4px solid #f57c00; }
        code { background: #f5f5f5; padding: 2px 5px; border-radius: 3px; }
        h2 { border-bottom: 2px solid #1976d2; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Superadmin Credentials Verification & Seeding</h1>

        <?php
        // Step 1: Check system_config table
        echo '<h2>Step 1: Check System Config Table</h2>';
        $tableCheck = $mysqli->query("SHOW TABLES LIKE 'system_config'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            echo '<div class="warning">⚠ system_config table does not exist. Creating...</div>';
            
            $createSQL = "CREATE TABLE IF NOT EXISTS `system_config` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `config_key` VARCHAR(255) NOT NULL,
                `config_value` TEXT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `config_key` (`config_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($mysqli->query($createSQL)) {
                echo '<div class="success">✓ system_config table created successfully</div>';
            } else {
                echo '<div class="error">✗ Failed to create system_config table: ' . htmlspecialchars($mysqli->error) . '</div>';
                die();
            }
        } else {
            echo '<div class="success">✓ system_config table exists</div>';
        }

        // Step 2: Check if superadmin credentials exist
        echo '<h2>Step 2: Check Existing Credentials</h2>';
        $checkResult = $mysqli->query("SELECT config_key FROM system_config WHERE config_key IN ('superadmin_email', 'superadmin_password', 'superadmin_username')");
        
        if ($checkResult && $checkResult->num_rows > 0) {
            echo '<div class="info">ℹ Superadmin credentials already exist in database</div>';
            
            // Show what's in the database
            $checkResult = $mysqli->query("SELECT config_key, config_value FROM system_config WHERE config_key IN ('superadmin_email', 'superadmin_password', 'superadmin_username')");
            while ($row = $checkResult->fetch_assoc()) {
                echo '<div class="info">Found: <code>' . htmlspecialchars($row['config_key']) . '</code> (encrypted, length: ' . strlen($row['config_value']) . ')</div>';
            }
        } else {
            echo '<div class="warning">⚠ No superadmin credentials found. Seeding now...</div>';
            
            // Step 3: Seed credentials
            echo '<h2>Step 3: Seed Superadmin Credentials</h2>';
            
            $encryptionKey = '7a3b9c2e8f1d6a5b4c9e2f8a1d7b3c9e2f8a1d7b3c9e2f8a1d7b3c9e2f8a1d7';
            $email = 'hafizsyedhanzala@gmail.com';
            $password = 'admin123';
            $username = 'Hanzala';
            
            // Encrypt email
            $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encryptedEmail = base64_encode($iv . openssl_encrypt($email, 'aes-256-cbc', $encryptionKey, 0, $iv));
            
            // Encrypt password
            $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encryptedPassword = base64_encode($iv . openssl_encrypt($password, 'aes-256-cbc', $encryptionKey, 0, $iv));
            
            // Encrypt username
            $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encryptedUsername = base64_encode($iv . openssl_encrypt($username, 'aes-256-cbc', $encryptionKey, 0, $iv));
            
            // Insert into database
            $mysqli->begin_transaction();
            try {
                $stmt1 = $mysqli->prepare("INSERT INTO system_config (config_key, config_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $stmt1->bind_param('ss', $key, $value);
                
                $key = 'superadmin_email';
                $value = $encryptedEmail;
                $stmt1->execute();
                
                $key = 'superadmin_password';
                $value = $encryptedPassword;
                $stmt1->execute();
                
                $key = 'superadmin_username';
                $value = $encryptedUsername;
                $stmt1->execute();
                
                $stmt1->close();
                $mysqli->commit();
                
                echo '<div class="success">✓ Superadmin credentials seeded successfully</div>';
                echo '<div class="info">Email: <code>' . htmlspecialchars($email) . '</code></div>';
                echo '<div class="info">Password: <code>' . htmlspecialchars($password) . '</code></div>';
                echo '<div class="info">Username: <code>' . htmlspecialchars($username) . '</code></div>';
            } catch (Exception $e) {
                $mysqli->rollback();
                echo '<div class="error">✗ Error seeding credentials: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }

        // Step 4: Test decryption
        echo '<h2>Step 4: Test Decryption</h2>';
        
        $encryptionKey = '7a3b9c2e8f1d6a5b4c9e2f8a1d7b3c9e2f8a1d7b3c9e2f8a1d7b3c9e2f8a1d7';
        $testResult = $mysqli->query("SELECT config_key, config_value FROM system_config WHERE config_key IN ('superadmin_email', 'superadmin_password', 'superadmin_username')");
        
        if ($testResult && $testResult->num_rows > 0) {
            $decrypted = [];
            while ($row = $testResult->fetch_assoc()) {
                $data = base64_decode($row['config_value'], true);
                if ($data === false) {
                    echo '<div class="error">✗ base64_decode failed for ' . htmlspecialchars($row['config_key']) . '</div>';
                    continue;
                }

                $ivLength = openssl_cipher_iv_length('aes-256-cbc');
                if (strlen($data) < $ivLength) {
                    echo '<div class="error">✗ Data too short for ' . htmlspecialchars($row['config_key']) . '</div>';
                    continue;
                }

                $iv = substr($data, 0, $ivLength);
                $encrypted = substr($data, $ivLength);
                $decryptedValue = openssl_decrypt($encrypted, 'aes-256-cbc', $encryptionKey, 0, $iv);
                
                if ($decryptedValue === false) {
                    echo '<div class="error">✗ Decryption failed for ' . htmlspecialchars($row['config_key']) . '</div>';
                } else {
                    echo '<div class="success">✓ ' . htmlspecialchars($row['config_key']) . ': <code>' . htmlspecialchars($decryptedValue) . '</code></div>';
                    $decrypted[$row['config_key']] = $decryptedValue;
                }
            }
            
            // Test login
            echo '<h2>Step 5: Test Login Credentials</h2>';
            if (isset($decrypted['superadmin_email']) && isset($decrypted['superadmin_password'])) {
                if ($decrypted['superadmin_email'] === 'hafizsyedhanzala@gmail.com' && $decrypted['superadmin_password'] === 'admin123') {
                    echo '<div class="success">✓ Login credentials are correct!</div>';
                    echo '<div class="info">You can now login at: <code>/x9k2m8p5q7/login</code></div>';
                } else {
                    echo '<div class="error">✗ Login credentials do not match expected values</div>';
                    echo '<div class="info">Expected email: <code>hafizsyedhanzala@gmail.com</code></div>';
                    echo '<div class="info">Got email: <code>' . htmlspecialchars($decrypted['superadmin_email']) . '</code></div>';
                }
            }
        }

        $mysqli->close();
        ?>

        <h2>Next Steps</h2>
        <div class="info">
            <p>If everything is working correctly:</p>
            <ol>
                <li>Visit <code>/x9k2m8p5q7/login</code></li>
                <li>Enter email: <code>hafizsyedhanzala@gmail.com</code></li>
                <li>Enter password: <code>admin123</code></li>
                <li>You should be redirected to the superadmin dashboard</li>
            </ol>
        </div>

        <div class="warning">
            <p><strong>Security Note:</strong> Delete this file (<code>verify_and_seed.php</code>) after verification!</p>
        </div>
    </div>
</body>
</html>
