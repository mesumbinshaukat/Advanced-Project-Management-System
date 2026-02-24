<?php
/**
 * Add Missing Columns - Advanced Project Management System
 * Visit: http://yourdomain.com/add_missing_columns.php
 *
 * Adds missing columns to existing database tables
 *
 * SECURITY: Delete this file after running!
 */

// Set proper encoding
header('Content-Type: text/html; charset=utf-8');

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
        <h1>Add Missing Database Columns</h1>
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
            echo '<div class="success">Database connection successful!</div>';
        } catch (Exception $e) {
            echo '<div class="error">Database connection failed!</div>';
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
            // Tasks table - CRITICAL COLUMNS
            ['table' => 'tasks', 'column' => 'is_blocked', 'sql' => "ALTER TABLE `tasks` ADD COLUMN `is_blocked` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`"],
            ['table' => 'tasks', 'column' => 'blocker_reason', 'sql' => "ALTER TABLE `tasks` ADD COLUMN `blocker_reason` TEXT NULL AFTER `is_blocked`"],
            ['table' => 'tasks', 'column' => 'tags', 'sql' => "ALTER TABLE `tasks` ADD COLUMN `tags` VARCHAR(255) NULL AFTER `description`"],
            ['table' => 'tasks', 'column' => 'order_position', 'sql' => "ALTER TABLE `tasks` ADD COLUMN `order_position` INT(11) NOT NULL DEFAULT 0 AFTER `completed_at`"],
            
            // Projects table - CRITICAL COLUMNS
            ['table' => 'projects', 'column' => 'documentation', 'sql' => "ALTER TABLE `projects` ADD COLUMN `documentation` TEXT NULL AFTER `description`"],
            ['table' => 'projects', 'column' => 'repository_url', 'sql' => "ALTER TABLE `projects` ADD COLUMN `repository_url` VARCHAR(255) NULL AFTER `documentation`"],
            ['table' => 'projects', 'column' => 'staging_url', 'sql' => "ALTER TABLE `projects` ADD COLUMN `staging_url` VARCHAR(255) NULL AFTER `repository_url`"],
            ['table' => 'projects', 'column' => 'production_url', 'sql' => "ALTER TABLE `projects` ADD COLUMN `production_url` VARCHAR(255) NULL AFTER `staging_url`"],
            ['table' => 'projects', 'column' => 'health_status', 'sql' => "ALTER TABLE `projects` ADD COLUMN `health_status` ENUM('healthy', 'warning', 'critical') DEFAULT 'healthy' AFTER `status`"],
            
            // Clients table
            ['table' => 'clients', 'column' => 'company', 'sql' => "ALTER TABLE `clients` ADD COLUMN `company` VARCHAR(255) NULL AFTER `phone`"],
            ['table' => 'clients', 'column' => 'notes', 'sql' => "ALTER TABLE `clients` ADD COLUMN `notes` TEXT NULL AFTER `address`"],
            ['table' => 'clients', 'column' => 'is_active', 'sql' => "ALTER TABLE `clients` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `notes`"],
            
            // Time entries - CRITICAL COLUMNS
            ['table' => 'time_entries', 'column' => 'deleted_at', 'sql' => "ALTER TABLE `time_entries` ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`"],
            ['table' => 'time_entries', 'column' => 'is_billable', 'sql' => "ALTER TABLE `time_entries` ADD COLUMN `is_billable` TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`"],
            
            // Users/Performance columns
            ['table' => 'users', 'column' => 'email', 'sql' => "ALTER TABLE `users` ADD COLUMN `email` VARCHAR(255) NULL UNIQUE AFTER `username`"],
            ['table' => 'users', 'column' => 'password_hash', 'sql' => "ALTER TABLE `users` ADD COLUMN `password_hash` VARCHAR(255) NULL AFTER `email`"],
            ['table' => 'users', 'column' => 'performance_score', 'sql' => "ALTER TABLE `users` ADD COLUMN `performance_score` DECIMAL(5,2) NULL DEFAULT 50 AFTER `deleted_at`"],
            ['table' => 'users', 'column' => 'deadline_score', 'sql' => "ALTER TABLE `users` ADD COLUMN `deadline_score` DECIMAL(5,2) NULL DEFAULT 50 AFTER `performance_score`"],
            ['table' => 'users', 'column' => 'speed_score', 'sql' => "ALTER TABLE `users` ADD COLUMN `speed_score` DECIMAL(5,2) NULL DEFAULT 50 AFTER `deadline_score`"],
            ['table' => 'users', 'column' => 'engagement_score', 'sql' => "ALTER TABLE `users` ADD COLUMN `engagement_score` DECIMAL(5,2) NULL DEFAULT 50 AFTER `speed_score`"],
            ['table' => 'users', 'column' => 'last_check_in', 'sql' => "ALTER TABLE `users` ADD COLUMN `last_check_in` DATETIME NULL AFTER `engagement_score`"],
            
            // Alerts
            ['table' => 'alerts', 'column' => 'user_id', 'sql' => "ALTER TABLE `alerts` ADD COLUMN `user_id` INT(11) UNSIGNED NULL AFTER `id`"],
            
            // Messages table - CRITICAL COLUMN
            ['table' => 'messages', 'column' => 'message', 'sql' => "ALTER TABLE `messages` ADD COLUMN `message` TEXT NULL AFTER `parent_id`"],
            
            // Notes table - missing columns
            ['table' => 'notes', 'column' => 'title', 'sql' => "ALTER TABLE `notes` ADD COLUMN `title` VARCHAR(255) NULL AFTER `task_id`"],
            ['table' => 'notes', 'column' => 'type', 'sql' => "ALTER TABLE `notes` ADD COLUMN `type` ENUM('note','decision','blocker','update') DEFAULT 'note' AFTER `content`"],
            ['table' => 'notes', 'column' => 'is_pinned', 'sql' => "ALTER TABLE `notes` ADD COLUMN `is_pinned` TINYINT(1) DEFAULT 0 AFTER `type`"],
            
            // Soft delete columns for other tables
            ['table' => 'messages', 'column' => 'deleted_at', 'sql' => "ALTER TABLE `messages` ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`"],
            ['table' => 'notes', 'column' => 'deleted_at', 'sql' => "ALTER TABLE `notes` ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`"],
            ['table' => 'project_templates', 'column' => 'deleted_at', 'sql' => "ALTER TABLE `project_templates` ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`"],
            
            // Activity logs - CRITICAL COLUMNS
            ['table' => 'activity_logs', 'column' => 'description', 'sql' => "ALTER TABLE `activity_logs` ADD COLUMN `description` TEXT NULL AFTER `action`"],
            ['table' => 'activity_logs', 'column' => 'ip_address', 'sql' => "ALTER TABLE `activity_logs` ADD COLUMN `ip_address` VARCHAR(45) NULL AFTER `new_values`"],
            ['table' => 'activity_logs', 'column' => 'user_agent', 'sql' => "ALTER TABLE `activity_logs` ADD COLUMN `user_agent` TEXT NULL AFTER `ip_address`"],
            ['table' => 'activity_logs', 'column' => 'metadata', 'sql' => "ALTER TABLE `activity_logs` ADD COLUMN `metadata` JSON NULL AFTER `new_values`"],
            
            // Task templates - soft delete
            ['table' => 'task_templates', 'column' => 'deleted_at', 'sql' => "ALTER TABLE `task_templates` ADD COLUMN `deleted_at` DATETIME NULL AFTER `updated_at`"],
            
            // Project templates - missing columns
            ['table' => 'project_templates', 'column' => 'estimated_duration_days', 'sql' => "ALTER TABLE `project_templates` ADD COLUMN `estimated_duration_days` INT(11) NULL AFTER `default_priority`"],
            
            // Project users - missing columns
            ['table' => 'project_users', 'column' => 'assigned_at', 'sql' => "ALTER TABLE `project_users` ADD COLUMN `assigned_at` DATETIME NULL AFTER `role`"],
        ];
        
        $added = 0;
        $skipped = 0;
        
        // First, create missing tables
        echo '<div class="info">Checking for required tables...</div>';
        
        // Create financials table if it doesn't exist
        echo '<div class="info">Checking for financials table...</div>';
        $tableCheck = $mysqli->query("SHOW TABLES LIKE 'financials'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            echo '<div class="info">Creating financials table...</div>';
            $createFinancials = "CREATE TABLE IF NOT EXISTS `financials` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `project_id` INT(11) UNSIGNED NOT NULL,
                `hourly_rate` DECIMAL(10,2) NULL,
                `fixed_price` DECIMAL(10,2) NULL,
                `total_cost` DECIMAL(10,2) NOT NULL DEFAULT 0,
                `total_revenue` DECIMAL(10,2) NOT NULL DEFAULT 0,
                `profit_margin` DECIMAL(5,2) NOT NULL DEFAULT 0,
                `billing_type` ENUM('hourly','fixed','retainer') NOT NULL DEFAULT 'hourly',
                `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                CONSTRAINT `financials_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($mysqli->query($createFinancials)) {
                echo '<div class="success">Created financials table</div>';
                $added++;
            } else {
                echo '<div class="error">Error creating financials table: ' . htmlspecialchars($mysqli->error) . '</div>';
            }
        } else {
            echo '<div class="info">Financials table already exists</div>';
            $skipped++;
        }
        
        // Create daily_check_ins table if it doesn't exist
        echo '<div class="info">Checking for daily_check_ins table...</div>';
        $tableCheck = $mysqli->query("SHOW TABLES LIKE 'daily_check_ins'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            echo '<div class="info">Creating daily_check_ins table...</div>';
            $createCheckIns = "CREATE TABLE IF NOT EXISTS `daily_check_ins` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `check_in_date` DATE NOT NULL,
                `mood` VARCHAR(50) NULL,
                `achievements` TEXT NULL,
                `plans` TEXT NULL,
                `blockers` TEXT NULL,
                `notes` TEXT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `check_in_date` (`check_in_date`),
                UNIQUE KEY `unique_user_date` (`user_id`, `check_in_date`),
                CONSTRAINT `daily_check_ins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($mysqli->query($createCheckIns)) {
                echo '<div class="success">Created daily_check_ins table</div>';
                $added++;
            } else {
                echo '<div class="error">Error creating daily_check_ins table: ' . htmlspecialchars($mysqli->error) . '</div>';
            }
        } else {
            echo '<div class="info">Daily check-ins table already exists</div>';
            $skipped++;
        }
        
        // Create user_skills table if it doesn't exist
        echo '<div class="info">Checking for user_skills table...</div>';
        $tableCheck = $mysqli->query("SHOW TABLES LIKE 'user_skills'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            echo '<div class="info">Creating user_skills table...</div>';
            $createUserSkills = "CREATE TABLE IF NOT EXISTS `user_skills` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `skill` VARCHAR(100) NOT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `skill` (`skill`),
                CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($mysqli->query($createUserSkills)) {
                echo '<div class="success">Created user_skills table</div>';
                $added++;
            } else {
                echo '<div class="error">Error creating user_skills table: ' . htmlspecialchars($mysqli->error) . '</div>';
            }
        } else {
            echo '<div class="info">user_skills table already exists</div>';
            $skipped++;
        }
        
        foreach ($requiredColumns as $col) {
            $table = $col['table'];
            $column = $col['column'];
            $sql = $col['sql'];
            
            // Check if table exists first
            $tableExists = $mysqli->query("SHOW TABLES LIKE '$table'");
            if (!$tableExists || $tableExists->num_rows === 0) {
                echo '<div class="warning">Table does not exist: ' . htmlspecialchars($table) . '. Skipping column ' . htmlspecialchars($column) . '</div>';
                continue;
            }
            
            // Check if column exists in the specific table
            $checkResult = $mysqli->query("DESCRIBE `$table` `$column`");
            
            if ($checkResult && $checkResult->num_rows > 0) {
                echo '<div class="info">Column already exists: ' . $table . '.' . $column . '</div>';
                $skipped++;
            } else {
                try {
                    if ($mysqli->query($sql)) {
                        echo '<div class="success">Added column: ' . $table . '.' . $column . '</div>';
                        $added++;
                    } else {
                        throw new Exception($mysqli->error);
                    }
                } catch (Exception $e) {
                    echo '<div class="error">Error adding column ' . $table . '.' . $column . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
        }
        
        if ($added === 0 && $skipped === 0) {
            echo '<div class="info">Γä╣ No columns needed to be added</div>';
        } else {
            echo '<div class="success">Added ' . $added . ' column(s), skipped ' . $skipped . ' existing column(s)</div>';
        }
        echo '</div>';

        // Apply 2026-02-16-180000_UpdateProjectsClientAndBudget migration logic
        echo '<div class="step">';
        echo '<h3>Step 4: Applying migration 2026-02-16-180000_UpdateProjectsClientAndBudget</h3>';

        $projectsTableExists = $mysqli->query("SHOW TABLES LIKE 'projects'");

        if ($projectsTableExists && $projectsTableExists->num_rows > 0) {
            try {
                $mysqli->begin_transaction();

                // Drop existing foreign key on client_id if present
                $fkQuery = sprintf(
                    "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'client_id' AND REFERENCED_TABLE_NAME IS NOT NULL",
                    $mysqli->real_escape_string($db_name)
                );
                $fkResult = $mysqli->query($fkQuery);
                if ($fkResult && $fkResult->num_rows > 0) {
                    while ($fkRow = $fkResult->fetch_assoc()) {
                        if (!empty($fkRow['CONSTRAINT_NAME'])) {
                            $constraintName = $fkRow['CONSTRAINT_NAME'];
                            if (!$mysqli->query("ALTER TABLE `projects` DROP FOREIGN KEY `{$constraintName}`")) {
                                throw new Exception('Failed to drop foreign key ' . $constraintName . ': ' . $mysqli->error);
                            }
                            echo '<div class="info">Dropped foreign key: ' . htmlspecialchars($constraintName) . '</div>';
                            break;
                        }
                    }
                }

                // Allow client_id to be nullable
                if (!$mysqli->query("ALTER TABLE `projects` MODIFY `client_id` INT(11) UNSIGNED NULL")) {
                    throw new Exception('Failed to modify client_id column: ' . $mysqli->error);
                }
                echo '<div class="success">Updated client_id column to allow NULL values.</div>';

                // Drop budget column if it exists
                $budgetColumn = $mysqli->query("SHOW COLUMNS FROM `projects` LIKE 'budget'");
                if ($budgetColumn && $budgetColumn->num_rows > 0) {
                    if (!$mysqli->query("ALTER TABLE `projects` DROP COLUMN `budget`")) {
                        throw new Exception('Failed to drop budget column: ' . $mysqli->error);
                    }
                    echo '<div class="success">Removed deprecated budget column.</div>';
                } else {
                    echo '<div class="info">Budget column already removed.</div>';
                }

                // Add new foreign key with SET NULL behavior if it does not already exist
                $newConstraint = 'projects_client_id_foreign';
                $fkExistsQuery = sprintf(
                    "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME = 'projects' AND CONSTRAINT_NAME = '%s' AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
                    $mysqli->real_escape_string($db_name),
                    $mysqli->real_escape_string($newConstraint)
                );
                $fkExists = $mysqli->query($fkExistsQuery);
                if (!$fkExists || $fkExists->num_rows === 0) {
                    $addFkSql = "ALTER TABLE `projects` ADD CONSTRAINT `{$newConstraint}` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE";
                    if (!$mysqli->query($addFkSql)) {
                        throw new Exception('Failed to add new foreign key: ' . $mysqli->error);
                    }
                    echo '<div class="success">Added new client_id foreign key with SET NULL behavior.</div>';
                } else {
                    echo '<div class="info">Client_id foreign key already configured.</div>';
                }

                $mysqli->commit();
                echo '<div class="success"><strong>Migration applied successfully.</strong></div>';
            } catch (Exception $e) {
                $mysqli->rollback();
                echo '<div class="error">Migration failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            echo '<div class="warning">Projects table not found. Skipping migration.</div>';
        }

        echo '</div>';

        // Allow time entries to omit task references for general timers
        echo '<div class="step">';
        echo '<h3>Step 5: Allowing time_entries.task_id to be nullable</h3>';
        $timeTableCheck = $mysqli->query("SHOW TABLES LIKE 'time_entries'");
        if (!$timeTableCheck || $timeTableCheck->num_rows === 0) {
            echo '<div class="warning">time_entries table missing, skipping task_id alteration.</div>';
        } else {
            $taskColumnCheck = $mysqli->query("SHOW COLUMNS FROM `time_entries` LIKE 'task_id'");
            if ($taskColumnCheck && $taskColumnCheck->num_rows > 0) {
                $columnInfo = $taskColumnCheck->fetch_assoc();
                if (stripos($columnInfo['Null'], 'YES') === false) {
                    if ($mysqli->query("ALTER TABLE `time_entries` MODIFY `task_id` INT(11) UNSIGNED NULL")) {
                        echo '<div class="success">Made time_entries.task_id nullable.</div>';
                    } else {
                        echo '<div class="error">Failed to alter task_id: ' . htmlspecialchars($mysqli->error) . '</div>';
                    }
                } else {
                    echo '<div class="info">time_entries.task_id already nullable.</div>';
                }
            } else {
                echo '<div class="warning">task_id column missing in time_entries table.</div>';
            }
        }

        echo '</div>';

        // Ensure daily_check_ins has the latest required columns
        echo '<div class="step">';
        echo '<h3>Step 5: Applying migration 2026-02-20-090000_AddDailyCheckInFields</h3>';
        $dailyColumns = [
            ['column' => 'yesterday_accomplishments', 'sql' => "ALTER TABLE `daily_check_ins` ADD COLUMN `yesterday_accomplishments` TEXT NULL AFTER `mood`"],
            ['column' => 'today_plan', 'sql' => "ALTER TABLE `daily_check_ins` ADD COLUMN `today_plan` TEXT NULL AFTER `yesterday_accomplishments`"],
            ['column' => 'blockers', 'sql' => "ALTER TABLE `daily_check_ins` ADD COLUMN `blockers` TEXT NULL AFTER `today_plan`"],
            ['column' => 'needs_help', 'sql' => "ALTER TABLE `daily_check_ins` ADD COLUMN `needs_help` TINYINT(1) DEFAULT 0 AFTER `blockers`"],
            ['column' => 'last_activity', 'sql' => "ALTER TABLE `daily_check_ins` ADD COLUMN `last_activity` DATETIME NULL AFTER `needs_help`"],
        ];

        $dailyTableCheck = $mysqli->query("SHOW TABLES LIKE 'daily_check_ins'");
        if (!$dailyTableCheck || $dailyTableCheck->num_rows === 0) {
            echo '<div class="warning">Daily check-ins table missing, skipping field updates.</div>';
        } else {
            foreach ($dailyColumns as $col) {
                $colName = $col['column'];
                $colCheck = $mysqli->query("SHOW COLUMNS FROM `daily_check_ins` LIKE '{$colName}'");
                if ($colCheck && $colCheck->num_rows > 0) {
                    echo '<div class="info">Column already exists: daily_check_ins.' . htmlspecialchars($colName) . '</div>';
                    continue;
                }

                if ($mysqli->query($col['sql'])) {
                    echo '<div class="success">Added column: daily_check_ins.' . htmlspecialchars($colName) . '</div>';
                } else {
                    echo '<div class="error">Failed to add column daily_check_ins.' . htmlspecialchars($colName) . ': ' . htmlspecialchars($mysqli->error) . '</div>';
                }
            }
        }

        echo '</div>';

        // Ensure checkout timestamps match 2026-02-24-150000_AddCheckInTimestamps
        echo '<div class="step">';
        echo '<h3>Step 6: Applying migration 2026-02-24-150000_AddCheckInTimestamps</h3>';

        $timestampColumns = [
            ['column' => 'checked_in_at', 'sql' => "ALTER TABLE `daily_check_ins` ADD COLUMN `checked_in_at` DATETIME NULL AFTER `check_in_date`"],
            ['column' => 'checked_out_at', 'sql' => "ALTER TABLE `daily_check_ins` ADD COLUMN `checked_out_at` DATETIME NULL AFTER `checked_in_at`"],
            ['column' => 'checkout_ready', 'sql' => "ALTER TABLE `daily_check_ins` ADD COLUMN `checkout_ready` TINYINT(1) NOT NULL DEFAULT 0 AFTER `checked_out_at`"],
        ];

        $dailyTableCheck = $mysqli->query("SHOW TABLES LIKE 'daily_check_ins'");

        if (!$dailyTableCheck || $dailyTableCheck->num_rows === 0) {
            echo '<div class="warning">daily_check_ins table missing, skipping timestamp updates.</div>';
        } else {
            foreach ($timestampColumns as $col) {
                $colName = $col['column'];
                $colCheck = $mysqli->query("SHOW COLUMNS FROM `daily_check_ins` LIKE '{$colName}'");

                if ($colCheck && $colCheck->num_rows > 0) {
                    echo '<div class="info">Column already exists: daily_check_ins.' . htmlspecialchars($colName) . '</div>';
                    continue;
                }

                if ($mysqli->query($col['sql'])) {
                    echo '<div class="success">Added column: daily_check_ins.' . htmlspecialchars($colName) . '</div>';
                } else {
                    echo '<div class="error">Failed to add column daily_check_ins.' . htmlspecialchars($colName) . ': ' . htmlspecialchars($mysqli->error) . '</div>';
                }
            }

            // Backfill timestamps similar to migration
            $backfillSql = "UPDATE daily_check_ins
                SET checked_in_at = COALESCE(checked_in_at, created_at, CONCAT(check_in_date, ' 09:00:00'))
                WHERE checked_in_at IS NULL";

            if ($mysqli->query($backfillSql)) {
                echo '<div class="success">Backfilled checked_in_at for legacy records.</div>';
            } else {
                echo '<div class="error">Failed to backfill checked_in_at: ' . htmlspecialchars($mysqli->error) . '</div>';
            }

            $readySql = "UPDATE daily_check_ins SET checkout_ready = 0 WHERE checkout_ready IS NULL";

            if ($mysqli->query($readySql)) {
                echo '<div class="success">Normalized checkout_ready flags.</div>';
            } else {
                echo '<div class="error">Failed to normalize checkout_ready: ' . htmlspecialchars($mysqli->error) . '</div>';
            }
        }

        echo '</div>';

        // Check and fix admin user authentication
        echo '<div class="section">';
        echo '<h2>Admin User Authentication Check</h2>';
        
        // Check if Shield tables exist
        $authGroupsExists = $mysqli->query("SHOW TABLES LIKE 'auth_groups'");
        $authGroupsUsersExists = $mysqli->query("SHOW TABLES LIKE 'auth_groups_users'");
        $authIdentitiesExists = $mysqli->query("SHOW TABLES LIKE 'auth_identities'");
        
        // Create Shield tables if they don't exist
        if (!$authIdentitiesExists || $authIdentitiesExists->num_rows === 0) {
            echo '<div class="warning">Creating missing Shield auth_identities table...</div>';
            $createIdentities = "CREATE TABLE IF NOT EXISTS `auth_identities` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                `secret` TEXT NOT NULL,
                `secret2` TEXT NULL,
                `expires` DATETIME NULL,
                `extra` TEXT NULL,
                `force_reset` TINYINT(1) DEFAULT 0,
                `last_used_at` DATETIME NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `user_id_type` (`user_id`, `type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            if ($mysqli->query($createIdentities)) {
                echo '<div class="success">Created auth_identities table</div>';
            } else {
                echo '<div class="error">Failed to create auth_identities: ' . htmlspecialchars($mysqli->error) . '</div>';
            }
        }
        
        if (!$authGroupsExists || $authGroupsExists->num_rows === 0) {
            echo '<div class="warning">Creating missing Shield auth_groups table...</div>';
            $createGroups = "CREATE TABLE IF NOT EXISTS `auth_groups` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `name` VARCHAR(255) NOT NULL UNIQUE,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            if ($mysqli->query($createGroups)) {
                echo '<div class="success">Created auth_groups table</div>';
                
                // Insert default groups
                $mysqli->query("INSERT INTO `auth_groups` (`title`, `description`, `name`, `created_at`) VALUES ('Admin', 'Administrator group with full system access', 'admin', NOW())");
                $mysqli->query("INSERT INTO `auth_groups` (`title`, `description`, `name`, `created_at`) VALUES ('Developer', 'Developer group with limited access', 'developer', NOW())");
                echo '<div class="success">Created default admin and developer groups</div>';
            } else {
                echo '<div class="error">Failed to create auth_groups: ' . htmlspecialchars($mysqli->error) . '</div>';
            }
        }
        
        if (!$authGroupsUsersExists || $authGroupsUsersExists->num_rows === 0) {
            echo '<div class="warning">Creating missing Shield auth_groups_users table...</div>';
            $createGroupsUsers = "CREATE TABLE IF NOT EXISTS `auth_groups_users` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `group_id` INT(11) UNSIGNED NOT NULL,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `user_id_group_id` (`user_id`, `group_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            if ($mysqli->query($createGroupsUsers)) {
                echo '<div class="success">Created auth_groups_users table</div>';
            } else {
                echo '<div class="error">Failed to create auth_groups_users: ' . htmlspecialchars($mysqli->error) . '</div>';
            }
        } else {
            // Table exists, check if group_id column exists
            $columnCheck = $mysqli->query("DESCRIBE `auth_groups_users` `group_id`");
            if (!$columnCheck || $columnCheck->num_rows === 0) {
                echo '<div class="warning">Adding missing group_id column to auth_groups_users table...</div>';
                $addColumn = "ALTER TABLE `auth_groups_users` ADD COLUMN `group_id` INT(11) UNSIGNED NOT NULL AFTER `user_id`";
                if ($mysqli->query($addColumn)) {
                    echo '<div class="success">Added group_id column to auth_groups_users</div>';
                } else {
                    echo '<div class="error">Failed to add group_id column: ' . htmlspecialchars($mysqli->error) . '</div>';
                }
            }
        }
        
        // Re-check if tables now exist
        $authGroupsExists = $mysqli->query("SHOW TABLES LIKE 'auth_groups'");
        $authGroupsUsersExists = $mysqli->query("SHOW TABLES LIKE 'auth_groups_users'");
        
        if (!$authGroupsExists || $authGroupsExists->num_rows === 0 || !$authGroupsUsersExists || $authGroupsUsersExists->num_rows === 0) {
            echo '<div class="error">Shield tables could not be created. Please run CodeIgniter migrations manually.</div>';
        } else {
            // Check if admin user exists
            $adminResult = $mysqli->query("SELECT id, username FROM users WHERE username = 'admin' LIMIT 1");
            if ($adminResult && $adminResult->num_rows > 0) {
                $admin = $adminResult->fetch_assoc();
                $adminId = $admin['id'];
                echo '<div class="info">Found admin user: ' . htmlspecialchars($admin['username']) . ' (ID: ' . $adminId . ')</div>';
                
                // Check if admin has email identity
                $identityResult = $mysqli->query("SELECT id FROM auth_identities WHERE user_id = {$adminId} AND type = 'email_password' LIMIT 1");
                
                if (!$identityResult || $identityResult->num_rows === 0) {
                    echo '<div class="warning">Admin user has no email identity! Attempting to fix...</div>';
                    
                    // Get admin email from auth_identities if it exists with different type
                    $emailResult = $mysqli->query("SELECT secret FROM auth_identities WHERE user_id = {$adminId} LIMIT 1");
                    if ($emailResult && $emailResult->num_rows > 0) {
                        $emailRow = $emailResult->fetch_assoc();
                        $adminEmail = $emailRow['secret'];
                        
                        // Create email_password identity
                        $insertSql = "INSERT INTO auth_identities (user_id, type, secret, created_at) VALUES ({$adminId}, 'email_password', '" . $mysqli->real_escape_string($adminEmail) . "', NOW())";
                        if ($mysqli->query($insertSql)) {
                            echo '<div class="success">Created email identity for admin user</div>';
                        } else {
                            echo '<div class="error">Failed to create email identity: ' . htmlspecialchars($mysqli->error) . '</div>';
                        }
                    } else {
                        echo '<div class="error">Could not find email for admin user. Please manually set the email identity.</div>';
                    }
                } else {
                    echo '<div class="success">Admin user has valid email identity</div>';
                }
                
                // Check if admin is in admin group
                $groupResult = $mysqli->query("SELECT agu.id FROM auth_groups_users agu JOIN auth_groups g ON g.id = agu.group_id WHERE agu.user_id = {$adminId} AND g.name = 'admin' LIMIT 1");
                
                if (!$groupResult || $groupResult->num_rows === 0) {
                    echo '<div class="warning">Admin user not in admin group! Attempting to fix...</div>';
                    
                    // Get admin group id
                    $adminGroupResult = $mysqli->query("SELECT id FROM auth_groups WHERE name = 'admin' LIMIT 1");
                    if ($adminGroupResult && $adminGroupResult->num_rows > 0) {
                        $adminGroup = $adminGroupResult->fetch_assoc();
                        $adminGroupId = $adminGroup['id'];
                        
                        // Add user to admin group
                        $insertGroupSql = "INSERT INTO auth_groups_users (user_id, group_id) VALUES ({$adminId}, {$adminGroupId})";
                        if ($mysqli->query($insertGroupSql)) {
                            echo '<div class="success">Added admin user to admin group</div>';
                        } else {
                            echo '<div class="error">Failed to add admin user to group: ' . htmlspecialchars($mysqli->error) . '</div>';
                        }
                    } else {
                        echo '<div class="error">Admin group not found in database</div>';
                    }
                } else {
                    echo '<div class="success">Admin user is in admin group</div>';
                }
                
                // Check password hash
                $passwordCheck = $mysqli->query("SELECT password_hash FROM users WHERE id = {$adminId}");
                if ($passwordCheck && $passwordCheck->num_rows > 0) {
                    $pwRow = $passwordCheck->fetch_assoc();
                    if (empty($pwRow['password_hash'])) {
                        echo '<div class="error">Admin user has no password hash! Setting default password...</div>';
                        // Set a default password: admin123456
                        $defaultPassword = password_hash('admin123456', PASSWORD_BCRYPT);
                        $updatePwSql = "UPDATE users SET password_hash = '" . $mysqli->real_escape_string($defaultPassword) . "' WHERE id = {$adminId}";
                        if ($mysqli->query($updatePwSql)) {
                            echo '<div class="success">Set admin password to: admin123456 (CHANGE THIS IMMEDIATELY!)</div>';
                            echo '<div class="warning"><strong>SECURITY WARNING:</strong> Default password has been set. Please login and change it immediately!</div>';
                        } else {
                            echo '<div class="error">Failed to set password: ' . htmlspecialchars($mysqli->error) . '</div>';
                        }
                    } else {
                        echo '<div class="success">Admin user has valid password hash</div>';
                    }
                } else {
                    echo '<div class="error">Could not check admin password</div>';
                }
            } else {
                echo '<div class="error">No admin user found in database!</div>';
            }
        }
        
        echo '</div>';

        $mysqli->close();
        ?>

        <div class="success">
            <h3>Column Update Complete!</h3>
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li><strong>DELETE THIS FILE:</strong> <code>public/add_missing_columns.php</code></li>
                <li>Refresh your dashboard and test the application</li>
            </ol>
        </div>

        <div class="warning">
            <h3>SECURITY WARNING</h3>
            <p>Delete this file immediately after running!</p>
            <p>Command: <code>rm public/add_missing_columns.php</code></p>
        </div>

        <a href="/dashboard" style="display:inline-block;padding:10px 18px;border-radius:6px;text-decoration:none;background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;margin:5px;">Go to Dashboard</a>
    </div>
</body>
</html>
