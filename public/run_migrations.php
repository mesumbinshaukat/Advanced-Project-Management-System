<?php
/**
 * Migration Runner - Advanced Project Management System
 * Visit: http://yourdomain.com/run_migrations.php
 *
 * Runs all pending database migrations using CodeIgniter's migration system
 *
 * SECURITY: Delete this file after running!
 */

// Set proper encoding
header('Content-Type: text/html; charset=utf-8');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define path constants - use realpath for reliable path resolution
$FCPATH = realpath(__DIR__) . DIRECTORY_SEPARATOR;
$ROOTPATH = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;

define('FCPATH', $FCPATH);
define('ROOTPATH', $ROOTPATH);

// Verify critical paths exist
if (!is_dir($ROOTPATH . 'vendor')) {
    die('<h1>Error: Vendor directory not found</h1><p>Expected at: ' . htmlspecialchars($ROOTPATH . 'vendor') . '</p>');
}

if (!file_exists($ROOTPATH . '.env')) {
    die('<h1>Error: .env file not found</h1><p>Expected at: ' . htmlspecialchars($ROOTPATH . '.env') . '</p>');
}

// Load Composer autoloader first
require $ROOTPATH . 'vendor/autoload.php';

// Load environment manually (simpler approach)
$envFile = $ROOTPATH . '.env';
$env = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
        continue;
    }
    [$key, $value] = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value, " \t\n\r\0\x0B'\"");
    $_ENV[$key] = $value;
}

// Define environment if not set
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', $_ENV['CI_ENVIRONMENT'] ?? 'production');
}

// Get database connection directly from .env without loading Paths
$db_host = $_ENV['database.default.hostname'] ?? 'localhost';
$db_name = $_ENV['database.default.database'] ?? 'project_db';
$db_user = $_ENV['database.default.username'] ?? 'root';
$db_pass = $_ENV['database.default.password'] ?? '';
$db_port = $_ENV['database.default.port'] ?? 3306;

// Create direct database connection
try {
    $db = new \mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    if ($db->connect_error) {
        throw new Exception('Connection failed: ' . $db->connect_error);
    }
    $db->set_charset('utf8mb4');
} catch (Exception $e) {
    die('<h1>Database Connection Failed</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
}

// Test connection
try {
    $db->query('SELECT 1');
} catch (Exception $e) {
    die('<h1>Database Connection Failed</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Migration Runner - PM System</title>
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
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
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
        <h1>Migration Runner - Advanced Project Management System</h1>
        <p><strong>Mode:</strong> Standalone (No SSH required)</p>
        
        <?php
        echo '<div class="step">';
        echo '<h3>Step 1: Testing Database Connection</h3>';

        echo '<div class="success">Database connection successful!</div>';
        echo '<pre>';
        echo 'Host: ' . htmlspecialchars($db_host) . ':' . htmlspecialchars($db_port) . "\n";
        echo 'Database: ' . htmlspecialchars($db_name) . "\n";
        echo 'Driver: MySQLi' . "\n";
        echo '</pre>';

        echo '</div>';

        // Check existing migrations
        echo '<div class="step">';
        echo '<h3>Step 2: Checking Existing Migrations</h3>';
        
        try {
            $result = $db->query("SHOW TABLES LIKE 'migrations'");
            if ($result && $result->num_rows > 0) {
                echo '<div class="success">Migrations table exists</div>';
                
                $migrationsResult = $db->query("SELECT * FROM migrations ORDER BY id");
                if ($migrationsResult && $migrationsResult->num_rows > 0) {
                    echo '<p>Previously run migrations: ' . $migrationsResult->num_rows . '</p>';
                    echo '<table border="1" cellpadding="5" style="border-collapse:collapse;width:100%;margin:10px 0;">';
                    echo '<tr><th>ID</th><th>Version</th><th>Class</th><th>Batch</th></tr>';
                    while ($row = $migrationsResult->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['version']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['class']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['batch']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="info">No migrations have been run yet.</div>';
                }
            } else {
                echo '<div class="info">Migrations table does not exist. Will be created automatically.</div>';
            }
        } catch (Exception $e) {
            echo '<div class="warning">Could not check migrations: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';

        // Run migrations using CodeIgniter's migration runner
        echo '<div class="step">';
        echo '<h3>Step 3: Running Migrations</h3>';
        
        echo '<div class="info">To run migrations, please execute the following command from your server terminal:</div>';
        echo '<pre style="background:#f8f9fa;padding:10px;border-radius:5px;"><code>cd ' . htmlspecialchars($ROOTPATH) . ' && php spark migrate</code></pre>';
        echo '<div class="info">This will apply all pending database migrations using CodeIgniter\'s migration system.</div>';
        echo '</div>';


        // Verify tables
        echo '<div class="step">';
        echo '<h3>Step 4: Verifying Database Tables</h3>';
        
        $expectedTables = [
            'users', 'auth_identities', 'auth_logins', 'auth_token_logins', 
            'auth_remember_tokens', 'auth_groups_users', 'auth_permissions_users',
            'clients', 'projects', 'tasks', 'project_users', 'time_entries',
            'activity_logs', 'notes', 'messages', 'alerts', 'check_ins',
            'project_templates', 'task_templates', 'performance_metrics',
            'pricing', 'migrations'
        ];
        
        try {
            // Get list of existing tables using MySQLi
            $result = $db->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . $db->real_escape_string($db_name) . "'");
            $existingTables = [];
            while ($row = $result->fetch_assoc()) {
                $existingTables[] = $row['TABLE_NAME'];
            }
            
            echo '<table border="1" cellpadding="5" style="border-collapse:collapse;width:100%;margin:10px 0;">';
            echo '<tr><th>Table Name</th><th>Status</th></tr>';
            
            foreach ($expectedTables as $table) {
                $exists = in_array($table, $existingTables);
                $status = $exists ? '<span style="color: #155724;">Exists</span>' : '<span style="color: #721c24;">Missing</span>';
                echo '<tr><td><strong>' . htmlspecialchars($table) . '</strong></td><td>' . $status . '</td></tr>';
            }
            
            echo '</table>';
            
            $missingCount = count(array_diff($expectedTables, $existingTables));
            if ($missingCount === 0) {
                echo '<div class="success">All expected tables are present!</div>';
            } else {
                echo '<div class="warning">' . $missingCount . ' table(s) are missing. Review the migration errors above.</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">Error verifying tables: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        echo '</div>';
        ?>

        <div class="success">
            <h3>Migration Process Complete!</h3>
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li><strong>IMPORTANT:</strong> Delete this file immediately: <code>public/run_migrations.php</code></li>
                <li>Clear application cache if you use any caching</li>
                <li>Test the application to ensure everything works correctly</li>
                <li>Check that you can login and access the dashboard</li>
            </ol>
        </div>

        <div class="warning">
            <h3>CRITICAL SECURITY WARNING</h3>
            <p>This file provides direct database access and migration execution. <strong>DELETE IT IMMEDIATELY</strong> after running to prevent unauthorized access or abuse.</p>
            <p>Command to delete: <code>rm public/run_migrations.php</code></p>
        </div>

        <a href="/" class="btn" style="background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;">Go to Home</a>
        <a href="/dashboard" class="btn" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">Go to Dashboard</a>
    </div>
</body>
</html>
