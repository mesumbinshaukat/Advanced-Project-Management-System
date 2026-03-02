<?php
/**
 * Test Superadmin Credentials
 * Visit: http://yourdomain.com/test_superadmin.php
 */

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
    <title>Test Superadmin Credentials</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; padding: 10px; background: #e8f5e9; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #ffebee; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #e3f2fd; margin: 10px 0; }
        code { background: #f5f5f5; padding: 5px; }
    </style>
</head>
<body>
    <h1>Superadmin Credentials Test</h1>

    <?php
    // Check if system_config table exists
    $tableCheck = $mysqli->query("SHOW TABLES LIKE 'system_config'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        echo '<div class="error">❌ system_config table does not exist</div>';
    } else {
        echo '<div class="success">✓ system_config table exists</div>';
    }

    // Check if superadmin credentials exist
    $result = $mysqli->query("SELECT config_key, config_value FROM system_config WHERE config_key IN ('superadmin_email', 'superadmin_password', 'superadmin_username')");
    
    if (!$result) {
        echo '<div class="error">❌ Query failed: ' . $mysqli->error . '</div>';
    } else {
        $credentials = [];
        while ($row = $result->fetch_assoc()) {
            $credentials[$row['config_key']] = $row['config_value'];
        }

        if (empty($credentials)) {
            echo '<div class="error">❌ No superadmin credentials found in database</div>';
            echo '<div class="info">Please run <code>/add_missing_columns.php</code> to seed the credentials</div>';
        } else {
            echo '<div class="success">✓ Superadmin credentials found in database</div>';
            
            // Test decryption
            $encryptionKey = '7a3b9c2e8f1d6a5b4c9e2f8a1d7b3c9e2f8a1d7b3c9e2f8a1d7b3c9e2f8a1d7';
            
            foreach ($credentials as $key => $encryptedValue) {
                echo '<div class="info"><strong>' . htmlspecialchars($key) . ':</strong></div>';
                
                // Try to decrypt
                try {
                    $data = base64_decode($encryptedValue, true);
                    if ($data === false) {
                        echo '<div class="error">❌ base64_decode failed</div>';
                        continue;
                    }

                    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
                    if (strlen($data) < $ivLength) {
                        echo '<div class="error">❌ Data too short to contain IV (length: ' . strlen($data) . ', expected: ' . $ivLength . ')</div>';
                        continue;
                    }

                    $iv = substr($data, 0, $ivLength);
                    $encrypted = substr($data, $ivLength);
                    
                    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $encryptionKey, 0, $iv);
                    
                    if ($decrypted === false) {
                        echo '<div class="error">❌ openssl_decrypt failed</div>';
                    } else {
                        echo '<div class="success">✓ Decrypted: <code>' . htmlspecialchars($decrypted) . '</code></div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="error">❌ Exception: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }

            // Test login
            echo '<hr>';
            echo '<h2>Login Test</h2>';
            
            $testEmail = 'hafizsyedhanzala@gmail.com';
            $testPassword = 'admin123';
            
            echo '<div class="info">Testing with email: <code>' . htmlspecialchars($testEmail) . '</code></div>';
            echo '<div class="info">Testing with password: <code>' . htmlspecialchars($testPassword) . '</code></div>';
            
            // Decrypt stored credentials
            $storedEmail = null;
            $storedPassword = null;
            
            if (isset($credentials['superadmin_email'])) {
                $data = base64_decode($credentials['superadmin_email'], true);
                $ivLength = openssl_cipher_iv_length('aes-256-cbc');
                $iv = substr($data, 0, $ivLength);
                $encrypted = substr($data, $ivLength);
                $storedEmail = openssl_decrypt($encrypted, 'aes-256-cbc', $encryptionKey, 0, $iv);
            }
            
            if (isset($credentials['superadmin_password'])) {
                $data = base64_decode($credentials['superadmin_password'], true);
                $ivLength = openssl_cipher_iv_length('aes-256-cbc');
                $iv = substr($data, 0, $ivLength);
                $encrypted = substr($data, $ivLength);
                $storedPassword = openssl_decrypt($encrypted, 'aes-256-cbc', $encryptionKey, 0, $iv);
            }
            
            if ($storedEmail === $testEmail && $storedPassword === $testPassword) {
                echo '<div class="success">✓ Login credentials match!</div>';
            } else {
                echo '<div class="error">❌ Login credentials do not match</div>';
                echo '<div class="info">Stored email: <code>' . htmlspecialchars($storedEmail) . '</code></div>';
                echo '<div class="info">Stored password: <code>' . htmlspecialchars($storedPassword) . '</code></div>';
            }
        }
    }

    $mysqli->close();
    ?>

</body>
</html>
