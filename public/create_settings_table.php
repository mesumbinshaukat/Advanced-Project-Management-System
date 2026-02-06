<?php
/**
 * Create Settings Table - Fix for Shield Settings Error
 * Visit: http://yourdomain.com/create_settings_table.php
 *
 * Creates the missing 'settings' table required by CodeIgniter Shield
 *
 * SECURITY: Delete this file after running!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment variables from .env
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

// Database config from .env
$db_host = $env['database.default.hostname'] ?? 'localhost';
$db_name = $env['database.default.database'] ?? 'project_db';
$db_user = $env['database.default.username'] ?? 'root';
$db_pass = $env['database.default.password'] ?? '';
$db_port = $env['database.default.port'] ?? 3306;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Settings Table</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Create Settings Table</h1>
        <p><strong>Purpose:</strong> Fix "Table 'settings' doesn't exist" error</p>
        
        <?php
        echo '<div class="info">';
        echo '<h3>Step 1: Connecting to Database</h3>';

        try {
            $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
            if ($mysqli->connect_error) {
                throw new Exception('Connection failed: ' . $mysqli->connect_error);
            }
            $mysqli->set_charset('utf8mb4');
            echo '<div class="success">✓ Connected successfully!</div>';
            echo '<pre>';
            echo 'Database: ' . $db_name . "\n";
            echo '</pre>';
        } catch (Exception $e) {
            echo '<div class="error">✗ Connection failed!</div>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            die('</div></div></body></html>');
        }

        echo '</div>';

        // Check if table exists
        echo '<div class="info">';
        echo '<h3>Step 2: Checking if Settings Table Exists</h3>';
        
        $result = $mysqli->query("SHOW TABLES LIKE 'settings'");
        if ($result && $result->num_rows > 0) {
            echo '<div class="success">✓ Settings table already exists! No action needed.</div>';
            echo '</div>';
        } else {
            echo '<div class="warning">⚠️ Settings table does not exist. Creating now...</div>';
            echo '</div>';
            
            // Create settings table
            echo '<div class="info">';
            echo '<h3>Step 3: Creating Settings Table</h3>';
            
            $createTableSQL = "CREATE TABLE `settings` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `class` varchar(255) NOT NULL,
                `key` varchar(255) NOT NULL,
                `value` text DEFAULT NULL,
                `type` varchar(31) NOT NULL DEFAULT 'string',
                `context` varchar(255) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `class_key_context` (`class`,`key`,`context`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            
            try {
                if ($mysqli->query($createTableSQL)) {
                    echo '<div class="success">✓ Settings table created successfully!</div>';
                    echo '<pre>Table structure:
- id (auto increment)
- class (varchar 255)
- key (varchar 255)
- value (text)
- type (varchar 31)
- context (varchar 255)
- created_at (datetime)
- updated_at (datetime)
                    </pre>';
                } else {
                    throw new Exception($mysqli->error);
                }
            } catch (Exception $e) {
                echo '<div class="error">✗ Failed to create table!</div>';
                echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            }
            
            echo '</div>';
        }

        // Verify table structure
        echo '<div class="info">';
        echo '<h3>Step 4: Verifying Table Structure</h3>';
        
        try {
            $result = $mysqli->query("DESCRIBE settings");
            if ($result) {
                echo '<div class="success">✓ Table structure verified!</div>';
                echo '<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
                echo '<tr style="background: #f8f9fa;"><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><strong>' . $row['Field'] . '</strong></td>';
                    echo '<td>' . $row['Type'] . '</td>';
                    echo '<td>' . $row['Null'] . '</td>';
                    echo '<td>' . $row['Key'] . '</td>';
                    echo '<td>' . ($row['Default'] ?? 'NULL') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        } catch (Exception $e) {
            echo '<div class="error">Error verifying table: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        echo '</div>';

        $mysqli->close();
        ?>

        <div class="success">
            <h3>✅ Process Complete!</h3>
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li><strong>DELETE THIS FILE:</strong> <code>public/create_settings_table.php</code></li>
                <li>Clear your browser cache and cookies</li>
                <li>Try accessing your site again</li>
                <li>The "settings table doesn't exist" error should be gone!</li>
            </ol>
        </div>

        <div class="warning">
            <h3>⚠️ Security Warning</h3>
            <p>This file provides direct database access. <strong>Remove it immediately</strong> after running!</p>
        </div>

        <a href="/" class="btn" style="background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;">Go to Home</a>
        <a href="/login" class="btn" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">Go to Login</a>
    </div>
</body>
</html>
