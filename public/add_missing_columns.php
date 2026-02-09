<?php
/**
 * Add Missing Columns - Advanced Project Management System
 * Visit: http://yourdomain.com/add_missing_columns.php
 *
 * Adds missing columns to existing database tables
 *
 * SECURITY: Delete this file after running!
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Missing Columns - PM System</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Add Missing Database Columns</h1>
        <p><strong>This script adds missing columns to your existing database.</strong></p>
        
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
        } catch (Exception $e) {
            echo '<div class="error">✗ Database connection failed!</div>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            die('</div></div></body></html>');
        }

        echo '</div>';

        // Show current tasks table structure
        echo '<div class="step">';
        echo '<h3>Step 2: Current Tasks Table Structure</h3>';
        
        $result = $mysqli->query("DESCRIBE tasks");
        if ($result) {
            echo '<table border="1" cellpadding="5" style="border-collapse:collapse;width:100%;margin:10px 0;">';
            echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
            $existingColumns = [];
            while ($row = $result->fetch_assoc()) {
                $existingColumns[] = $row['Field'];
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['Field']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Type']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Null']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Key']) . '</td>';
                echo '<td>' . htmlspecialchars($row['Default'] ?? 'NULL') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        echo '</div>';
        
        // Add missing columns
        echo '<div class="step">';
        echo '<h3>Step 3: Adding Missing Columns</h3>';
        
        // Define all columns that should exist based on code usage
        $requiredColumns = [
            ['table' => 'clients', 'column' => 'company', 'sql' => "ALTER TABLE `clients` ADD COLUMN `company` VARCHAR(255) NULL AFTER `phone`"],
            ['table' => 'clients', 'column' => 'notes', 'sql' => "ALTER TABLE `clients` ADD COLUMN `notes` TEXT NULL AFTER `address`"],
            ['table' => 'clients', 'column' => 'is_active', 'sql' => "ALTER TABLE `clients` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `notes`"],
            ['table' => 'tasks', 'column' => 'start_date', 'sql' => "ALTER TABLE `tasks` ADD COLUMN `start_date` DATE NULL AFTER `deadline`"],
            ['table' => 'tasks', 'column' => 'actual_hours', 'sql' => "ALTER TABLE `tasks` ADD COLUMN `actual_hours` DECIMAL(5,2) NULL DEFAULT 0 AFTER `estimated_hours`"],
            ['table' => 'tasks', 'column' => 'completed_at', 'sql' => "ALTER TABLE `tasks` ADD COLUMN `completed_at` DATETIME NULL AFTER `actual_hours`"],
            ['table' => 'tasks', 'column' => 'order_position', 'sql' => "ALTER TABLE `tasks` ADD COLUMN `order_position` INT(11) NULL DEFAULT 0 AFTER `completed_at`"],
            ['table' => 'time_entries', 'column' => 'is_billable', 'sql' => "ALTER TABLE `time_entries` ADD COLUMN `is_billable` TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`"],
        ];
        
        $added = 0;
        $skipped = 0;
        
        foreach ($requiredColumns as $col) {
            $table = $col['table'];
            $column = $col['column'];
            $sql = $col['sql'];
            
            // Check if column exists in the specific table
            $checkResult = $mysqli->query("DESCRIBE `$table` `$column`");
            
            if ($checkResult && $checkResult->num_rows > 0) {
                echo '<div class="info">⊘ Column already exists: ' . $table . '.' . $column . '</div>';
                $skipped++;
            } else {
                try {
                    if ($mysqli->query($sql)) {
                        echo '<div class="success">✓ Added column: ' . $table . '.' . $column . '</div>';
                        $added++;
                    } else {
                        throw new Exception($mysqli->error);
                    }
                } catch (Exception $e) {
                    echo '<div class="error">✗ Error adding column ' . $table . '.' . $column . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
        }
        
        if ($added === 0 && $skipped === 0) {
            echo '<div class="info">ℹ No columns needed to be added</div>';
        } else {
            echo '<div class="success">✓ Added ' . $added . ' column(s), skipped ' . $skipped . ' existing column(s)</div>';
        }
        echo '</div>';

        $mysqli->close();
        ?>

        <div class="success">
            <h3>✅ Column Update Complete!</h3>
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li><strong>DELETE THIS FILE:</strong> <code>public/add_missing_columns.php</code></li>
                <li>Refresh your dashboard and test the application</li>
            </ol>
        </div>

        <div class="warning">
            <h3>⚠️ SECURITY WARNING</h3>
            <p>Delete this file immediately after running!</p>
            <p>Command: <code>rm public/add_missing_columns.php</code></p>
        </div>

        <a href="/dashboard" style="display:inline-block;padding:10px 18px;border-radius:6px;text-decoration:none;background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;margin:5px;">Go to Dashboard</a>
    </div>
</body>
</html>
