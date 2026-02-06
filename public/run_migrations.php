<?php
/**
 * Migration Runner - Advanced Project Management System
 * Visit: http://yourdomain.com/run_migrations.php
 *
 * Runs all pending database migrations using CodeIgniter's migration system
 *
 * SECURITY: Delete this file after running!
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define path constants
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Prevent full app initialization - we only need migrations
$_SERVER['argv'] = ['spark', 'migrate'];
$_SERVER['argc'] = 2;

// Load Paths config
require realpath(FCPATH . '../app/Config/Paths.php') ?: FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();

// Define ROOTPATH
define('ROOTPATH', realpath(FCPATH . '../') . DIRECTORY_SEPARATOR);

// Load Composer autoloader
require ROOTPATH . 'vendor/autoload.php';

// Load environment first
$dotenv = new \CodeIgniter\Config\DotEnv(ROOTPATH);
$dotenv->load();

// Define environment if not set
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', $_ENV['CI_ENVIRONMENT'] ?? $_SERVER['CI_ENVIRONMENT'] ?? 'production');
}

// Load constants
require $paths->systemDirectory . '/Config/Constants.php';

// Load common functions
require $paths->systemDirectory . '/Common.php';

// Get database connection
$db = \Config\Database::connect();

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
        <h1>🚀 Migration Runner - Advanced Project Management System</h1>
        <p><strong>Mode:</strong> Standalone (No SSH required)</p>
        
        <?php
        echo '<div class="step">';
        echo '<h3>Step 1: Testing Database Connection</h3>';

        try {
            $dbConfig = $db->getConnectSettings();
            echo '<div class="success">✓ Database connection successful!</div>';
            echo '<pre>';
            echo 'Database: ' . ($dbConfig['database'] ?? 'N/A') . "\n";
            echo 'Driver: ' . ($dbConfig['DBDriver'] ?? 'N/A') . "\n";
            echo '</pre>';
        } catch (Exception $e) {
            echo '<div class="error">✗ Database connection failed!</div>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            die('</div></div></body></html>');
        }

        echo '</div>';

        // Check existing migrations
        echo '<div class="step">';
        echo '<h3>Step 2: Checking Existing Migrations</h3>';
        
        try {
            if ($db->tableExists('migrations')) {
                echo '<div class="success">✓ Migrations table exists</div>';
                
                $existingMigrations = $db->table('migrations')->orderBy('id')->get()->getResultArray();
                if (!empty($existingMigrations)) {
                    echo '<p>Previously run migrations: ' . count($existingMigrations) . '</p>';
                    echo '<table><tr><th>ID</th><th>Version</th><th>Class</th><th>Batch</th></tr>';
                    foreach ($existingMigrations as $row) {
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . $row['version'] . '</td>';
                        echo '<td>' . htmlspecialchars($row['class']) . '</td>';
                        echo '<td>' . $row['batch'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="info">ℹ️ No migrations have been run yet.</div>';
                }
            } else {
                echo '<div class="info">ℹ️ Migrations table does not exist. Will be created automatically.</div>';
            }
        } catch (Exception $e) {
            echo '<div class="warning">Could not check migrations: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';

        // Run migrations using CodeIgniter's migration runner
        echo '<div class="step">';
        echo '<h3>Step 3: Running Migrations</h3>';
        
        try {
            $migrate = \Config\Services::migrations();
            
            // Run all migrations
            if ($migrate->latest()) {
                echo '<div class="success">✓ All migrations executed successfully!</div>';
            } else {
                $error = $migrate->getCliMessages();
                if (empty($error)) {
                    echo '<div class="info">ℹ️ No new migrations to run. Database is up to date!</div>';
                } else {
                    echo '<div class="warning">⚠️ Migration completed with messages:</div>';
                    echo '<pre>' . htmlspecialchars(implode("\n", $error)) . '</pre>';
                }
            }
        } catch (Exception $e) {
            echo '<div class="error">✗ Migration error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
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
            $existingTables = $db->listTables();
            
            echo '<table>';
            echo '<tr><th>Table Name</th><th>Status</th></tr>';
            
            foreach ($expectedTables as $table) {
                $exists = in_array($table, $existingTables);
                $status = $exists ? '<span style="color: #155724;">✓ Exists</span>' : '<span style="color: #721c24;">✗ Missing</span>';
                echo '<tr><td><strong>' . $table . '</strong></td><td>' . $status . '</td></tr>';
            }
            
            echo '</table>';
            
            $missingCount = count(array_diff($expectedTables, $existingTables));
            if ($missingCount === 0) {
                echo '<div class="success">✓ All expected tables are present!</div>';
            } else {
                echo '<div class="warning">⚠️ ' . $missingCount . ' table(s) are missing. Review the migration errors above.</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">Error verifying tables: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        echo '</div>';
        ?>

        <div class="success">
            <h3>✅ Migration Process Complete!</h3>
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li><strong>IMPORTANT:</strong> Delete this file immediately: <code>public/run_migrations.php</code></li>
                <li>Clear application cache if you use any caching</li>
                <li>Test the application to ensure everything works correctly</li>
                <li>Check that you can login and access the dashboard</li>
            </ol>
        </div>

        <div class="warning">
            <h3>⚠️ CRITICAL SECURITY WARNING</h3>
            <p>This file provides direct database access and migration execution. <strong>DELETE IT IMMEDIATELY</strong> after running to prevent unauthorized access or abuse.</p>
            <p>Command to delete: <code>rm public/run_migrations.php</code></p>
        </div>

        <a href="/" class="btn" style="background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;">Go to Home</a>
        <a href="/dashboard" class="btn" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">Go to Dashboard</a>
    </div>
</body>
</html>
