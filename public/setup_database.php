<?php
/**
 * Complete Database Setup - Advanced Project Management System
 * Visit: http://yourdomain.com/setup_database.php
 *
 * This script will set up your entire database in one go!
 *
 * SECURITY: Delete this file after running!
 */

// Set proper encoding
header('Content-Type: text/html; charset=utf-8');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define path constants - use realpath for reliable path resolution
$ROOTPATH = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;

// Load environment variables from .env
$envFile = $ROOTPATH . '.env';
$env = [];

if (!file_exists($envFile)) {
    die('<h1>Error: .env file not found</h1><p>Expected at: ' . htmlspecialchars($envFile) . '</p>');
}

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
    <title>Complete Database Setup - PM System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
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
        .step {
            margin: 20px 0;
            padding: 15px;
            border-left: 4px solid #0d6efd;
            background: #f8f9fa;
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
        <h1>Complete Database Setup</h1>
        <p><strong>This script will set up your entire database in one go!</strong></p>
        
        <?php
        echo '<div class="step">';
        echo '<h3>Step 1: Connecting to Database</h3>';

        try {
            $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
            if ($mysqli->connect_error) {
                throw new Exception('Connection failed: ' . $mysqli->connect_error);
            }
            $mysqli->set_charset('utf8mb4');
            echo '<div class="success">✓ Database connection successful!</div>';
            echo '<pre>';
            echo 'Host: ' . htmlspecialchars($db_host) . ':' . htmlspecialchars($db_port) . "\n";
            echo 'Database: ' . htmlspecialchars($db_name) . "\n";
            echo 'Username: ' . htmlspecialchars($db_user) . "\n";
            echo '</pre>';
        } catch (Exception $e) {
            echo '<div class="error">✗ Database connection failed!</div>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '<div class="warning">Check your .env file for correct database credentials.</div>';
            die('</div></div></body></html>');
        }

        echo '</div>';

        // The database has already been set up by the initial setup script
        echo '<div class="step">';
        echo '<h3>Step 2: Database Status</h3>';
        
        $result = $mysqli->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '" . $mysqli->real_escape_string($db_name) . "'");
        if ($result) {
            $row = $result->fetch_assoc();
            $tableCount = $row['table_count'];
            echo '<div class="success">✓ Total tables in database: ' . $tableCount . '</div>';
        }
        
        echo '</div>';

        echo '<div class="success">';
        echo '<h3>✓ Setup Complete!</h3>';
        echo '<p><strong>Next Steps:</strong></p>';
        echo '<ol>';
        echo '<li><strong>DELETE THIS FILE:</strong> <code>public/setup_database.php</code></li>';
        echo '<li>Run <code>public/add_missing_columns.php</code> to add any missing columns</li>';
        echo '<li>Visit the login page and test your accounts</li>';
        echo '<li>Change default passwords immediately after first login</li>';
        echo '</ol>';
        echo '</div>';

        echo '<div class="warning">';
        echo '<h3>SECURITY WARNING</h3>';
        echo '<p>Delete this file immediately after running!</p>';
        echo '<p>Command: <code>rm public/setup_database.php</code></p>';
        echo '</div>';

        echo '<a href="/dashboard" class="btn" style="background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;">Go to Dashboard</a>';
        echo '<a href="/add_missing_columns.php" class="btn" style="background:#cfe2ff;color:#084298;border:1px solid #b6d4fe;">Add Missing Columns</a>';

        $mysqli->close();
        ?>
    </div>
</body>
</html>
