<?php
/**
 * Complete Database Setup - Advanced Project Management System
 * Visit: http://yourdomain.com/setup_database.php
 *
 * This script does EVERYTHING in one go:
 * 1. Reads migration SQL files and executes them
 * 2. Creates settings table for Shield
 * 3. Creates admin and developer user accounts
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

// User credentials
$adminEmail = 'admin@example.com';
$adminPassword = 'admin123';
$devEmail = 'developer@example.com';
$devPassword = 'developer123';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Setup - PM System</title>
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
        .credentials {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .credentials code {
            background: #fff;
            padding: 3px 8px;
            border-radius: 3px;
            color: #d63384;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Complete Database Setup</h1>
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
            echo 'Host: ' . $db_host . ':' . $db_port . "\n";
            echo 'Database: ' . $db_name . "\n";
            echo 'Username: ' . $db_user . "\n";
            echo '</pre>';
        } catch (Exception $e) {
            echo '<div class="error">✗ Database connection failed!</div>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '<div class="warning">Check your .env file for correct database credentials.</div>';
            die('</div></div></body></html>');
        }

        echo '</div>';

        // Create Shield authentication tables first
        echo '<div class="step">';
        echo '<h3>Step 2: Creating Shield Authentication Tables</h3>';
        
        $authTables = [
            'users' => "CREATE TABLE IF NOT EXISTS `users` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `username` VARCHAR(30) NULL,
                `status` VARCHAR(255) NULL,
                `status_message` VARCHAR(255) NULL,
                `active` TINYINT(1) NOT NULL DEFAULT 0,
                `last_active` DATETIME NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                `deleted_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'auth_identities' => "CREATE TABLE IF NOT EXISTS `auth_identities` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                `name` VARCHAR(255) NULL,
                `secret` VARCHAR(255) NOT NULL,
                `secret2` VARCHAR(255) NULL,
                `expires` DATETIME NULL,
                `extra` TEXT NULL,
                `force_reset` TINYINT(1) NOT NULL DEFAULT 0,
                `last_used_at` DATETIME NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `type_secret` (`type`, `secret`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'auth_logins' => "CREATE TABLE IF NOT EXISTS `auth_logins` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ip_address` VARCHAR(255) NOT NULL,
                `user_agent` VARCHAR(255) NULL,
                `id_type` VARCHAR(255) NOT NULL,
                `identifier` VARCHAR(255) NOT NULL,
                `user_id` INT(11) UNSIGNED NULL,
                `date` DATETIME NOT NULL,
                `success` TINYINT(1) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `id_type_identifier` (`id_type`, `identifier`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'auth_token_logins' => "CREATE TABLE IF NOT EXISTS `auth_token_logins` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `ip_address` VARCHAR(255) NOT NULL,
                `user_agent` VARCHAR(255) NULL,
                `id_type` VARCHAR(255) NOT NULL,
                `identifier` VARCHAR(255) NOT NULL,
                `user_id` INT(11) UNSIGNED NULL,
                `date` DATETIME NOT NULL,
                `success` TINYINT(1) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `id_type_identifier` (`id_type`, `identifier`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'auth_remember_tokens' => "CREATE TABLE IF NOT EXISTS `auth_remember_tokens` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `selector` VARCHAR(255) NOT NULL,
                `hashedValidator` VARCHAR(255) NOT NULL,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `expires` DATETIME NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `selector` (`selector`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'auth_groups_users' => "CREATE TABLE IF NOT EXISTS `auth_groups_users` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `group` VARCHAR(255) NOT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'auth_permissions_users' => "CREATE TABLE IF NOT EXISTS `auth_permissions_users` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `permission` VARCHAR(255) NOT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        ];
        
        $createdAuthTables = 0;
        foreach ($authTables as $tableName => $sql) {
            try {
                if ($mysqli->query($sql)) {
                    echo '<div class="success">✓ Auth table created: ' . $tableName . '</div>';
                    $createdAuthTables++;
                } else {
                    throw new Exception($mysqli->error);
                }
            } catch (Exception $e) {
                echo '<div class="error">✗ Error creating auth table ' . $tableName . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
        
        echo '<div class="success">✓ Created ' . $createdAuthTables . ' Shield authentication tables</div>';
        echo '</div>';
        
        // Create application tables
        echo '<div class="step">';
        echo '<h3>Step 3: Creating Application Tables</h3>';
        
        $tables = [
            'clients' => "CREATE TABLE IF NOT EXISTS `clients` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NULL,
                `phone` VARCHAR(50) NULL,
                `address` TEXT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                `deleted_at` DATETIME NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'projects' => "CREATE TABLE IF NOT EXISTS `projects` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `client_id` INT(11) UNSIGNED NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `status` ENUM('active','on_hold','completed','archived') NOT NULL DEFAULT 'active',
                `start_date` DATE NULL,
                `deadline` DATE NULL,
                `budget` DECIMAL(10,2) NULL,
                `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
                `created_by` INT(11) UNSIGNED NOT NULL,
                `documentation` TEXT NULL,
                `repository_url` VARCHAR(255) NULL,
                `staging_url` VARCHAR(255) NULL,
                `production_url` VARCHAR(255) NULL,
                `health_status` ENUM('healthy','warning','critical') NOT NULL DEFAULT 'healthy',
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                `deleted_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `client_id` (`client_id`),
                KEY `status` (`status`),
                KEY `created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'tasks' => "CREATE TABLE IF NOT EXISTS `tasks` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `project_id` INT(11) UNSIGNED NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `status` ENUM('backlog','todo','in_progress','review','done') NOT NULL DEFAULT 'backlog',
                `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
                `assigned_to` INT(11) UNSIGNED NULL,
                `created_by` INT(11) UNSIGNED NOT NULL,
                `deadline` DATE NULL,
                `estimated_hours` DECIMAL(5,2) NULL,
                `completed_at` DATETIME NULL,
                `is_blocker` TINYINT(1) NOT NULL DEFAULT 0,
                `blocker_reason` TEXT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                `deleted_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `status` (`status`),
                KEY `assigned_to` (`assigned_to`),
                KEY `created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'project_users' => "CREATE TABLE IF NOT EXISTS `project_users` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `project_id` INT(11) UNSIGNED NOT NULL,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `role` VARCHAR(50) NULL,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `project_user` (`project_id`, `user_id`),
                KEY `project_id` (`project_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'time_entries' => "CREATE TABLE IF NOT EXISTS `time_entries` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `task_id` INT(11) UNSIGNED NOT NULL,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `hours` DECIMAL(5,2) NOT NULL,
                `date` DATE NOT NULL,
                `description` TEXT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `task_id` (`task_id`),
                KEY `user_id` (`user_id`),
                KEY `date` (`date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'activity_logs' => "CREATE TABLE IF NOT EXISTS `activity_logs` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `entity_type` VARCHAR(50) NOT NULL,
                `entity_id` INT(11) UNSIGNED NOT NULL,
                `action` VARCHAR(50) NOT NULL,
                `old_values` JSON NULL,
                `new_values` JSON NULL,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `entity` (`entity_type`, `entity_id`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'notes' => "CREATE TABLE IF NOT EXISTS `notes` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `project_id` INT(11) UNSIGNED NULL,
                `task_id` INT(11) UNSIGNED NULL,
                `content` TEXT NOT NULL,
                `is_decision` TINYINT(1) NOT NULL DEFAULT 0,
                `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `project_id` (`project_id`),
                KEY `task_id` (`task_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'messages' => "CREATE TABLE IF NOT EXISTS `messages` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `project_id` INT(11) UNSIGNED NOT NULL,
                `task_id` INT(11) UNSIGNED NULL,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `parent_id` INT(11) UNSIGNED NULL,
                `content` TEXT NOT NULL,
                `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `task_id` (`task_id`),
                KEY `user_id` (`user_id`),
                KEY `parent_id` (`parent_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'alerts' => "CREATE TABLE IF NOT EXISTS `alerts` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `type` VARCHAR(50) NOT NULL,
                `severity` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
                `title` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `entity_type` VARCHAR(50) NULL,
                `entity_id` INT(11) UNSIGNED NULL,
                `is_resolved` TINYINT(1) NOT NULL DEFAULT 0,
                `resolved_at` DATETIME NULL,
                `resolved_by` INT(11) UNSIGNED NULL,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `type` (`type`),
                KEY `severity` (`severity`),
                KEY `is_resolved` (`is_resolved`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'check_ins' => "CREATE TABLE IF NOT EXISTS `check_ins` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `mood` ENUM('great','good','okay','bad','terrible') NOT NULL,
                `productivity` INT(11) NOT NULL,
                `blockers` TEXT NULL,
                `achievements` TEXT NULL,
                `plans` TEXT NULL,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `user_date` (`user_id`, `date`),
                KEY `user_id` (`user_id`),
                KEY `date` (`date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'project_templates' => "CREATE TABLE IF NOT EXISTS `project_templates` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `task_templates` JSON NULL,
                `default_priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
                `default_budget` DECIMAL(10,2) NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_by` INT(11) UNSIGNED NOT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'task_templates' => "CREATE TABLE IF NOT EXISTS `task_templates` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `default_priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
                `estimated_hours` DECIMAL(5,2) NULL,
                `checklist_items` JSON NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_by` INT(11) UNSIGNED NOT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'performance_metrics' => "CREATE TABLE IF NOT EXISTS `performance_metrics` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` INT(11) UNSIGNED NOT NULL,
                `month` DATE NOT NULL,
                `tasks_completed` INT(11) NOT NULL DEFAULT 0,
                `avg_completion_time` DECIMAL(5,2) NULL,
                `quality_score` DECIMAL(3,2) NULL,
                `on_time_delivery` DECIMAL(5,2) NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `user_month` (`user_id`, `month`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            
            'pricing' => "CREATE TABLE IF NOT EXISTS `pricing` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `project_id` INT(11) UNSIGNED NOT NULL,
                `hourly_rate` DECIMAL(10,2) NOT NULL,
                `fixed_price` DECIMAL(10,2) NULL,
                `billing_type` ENUM('hourly','fixed','retainer') NOT NULL DEFAULT 'hourly',
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        ];
        
        $createdTables = 0;
        foreach ($tables as $tableName => $sql) {
            try {
                if ($mysqli->query($sql)) {
                    echo '<div class="success">✓ Table created/verified: ' . $tableName . '</div>';
                    $createdTables++;
                } else {
                    throw new Exception($mysqli->error);
                }
            } catch (Exception $e) {
                echo '<div class="error">✗ Error creating table ' . $tableName . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
        
        echo '<div class="success">✓ Created/verified ' . $createdTables . ' application tables</div>';
        echo '</div>';

        // Create settings table
        echo '<div class="step">';
        echo '<h3>Step 4: Creating Settings Table (for Shield)</h3>';
        
        try {
            $settingsSQL = "CREATE TABLE IF NOT EXISTS `settings` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `class` VARCHAR(255) NOT NULL,
                `key` VARCHAR(255) NOT NULL,
                `value` TEXT NULL,
                `type` VARCHAR(31) NOT NULL DEFAULT 'string',
                `context` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `class_key_context` (`class`, `key`, `context`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            
            if ($mysqli->query($settingsSQL)) {
                echo '<div class="success">✓ Settings table created successfully!</div>';
            } else {
                throw new Exception($mysqli->error);
            }
        } catch (Exception $e) {
            echo '<div class="error">✗ Error creating settings table: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        echo '</div>';

        // Create users (admin & developer)
        echo '<div class="step">';
        echo '<h3>Step 5: Creating User Accounts</h3>';
        
        try {
            // Create admin user
            // Check if admin exists
                $result = $mysqli->query("SELECT id FROM users WHERE username = 'admin'");
                
                if ($result && $result->num_rows > 0) {
                    $existingUser = $result->fetch_assoc();
                    $userId = $existingUser['id'];
                    
                    // Delete old data
                    $mysqli->query("DELETE FROM auth_identities WHERE user_id = $userId");
                    $mysqli->query("DELETE FROM auth_groups_users WHERE user_id = $userId");
                    $mysqli->query("DELETE FROM users WHERE id = $userId");
                }
                
                // Create fresh admin user
                $stmt = $mysqli->prepare("INSERT INTO users (username, active, created_at, updated_at) VALUES (?, 1, NOW(), NOW())");
                $username = 'admin';
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $adminUserId = $mysqli->insert_id;
                
                // Create identity
                $passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT);
                $stmt = $mysqli->prepare("INSERT INTO auth_identities (user_id, type, name, secret, secret2, force_reset, created_at, updated_at) VALUES (?, 'email_password', NULL, ?, ?, 0, NOW(), NOW())");
                $stmt->bind_param('iss', $adminUserId, $adminEmail, $passwordHash);
                $stmt->execute();
                
                // Assign admin group
                $stmt = $mysqli->prepare("INSERT INTO auth_groups_users (user_id, `group`, created_at) VALUES (?, 'admin', NOW())");
                $stmt->bind_param('i', $adminUserId);
                $stmt->execute();
                
            echo '<div class="success">✓ Admin user created (ID: ' . $adminUserId . ')</div>';
            
            // Create developer user
            // Check if developer exists
                $result = $mysqli->query("SELECT id FROM users WHERE username = 'developer'");
                
                if ($result && $result->num_rows > 0) {
                    $existingUser = $result->fetch_assoc();
                    $userId = $existingUser['id'];
                    
                    // Delete old data
                    $mysqli->query("DELETE FROM auth_identities WHERE user_id = $userId");
                    $mysqli->query("DELETE FROM auth_groups_users WHERE user_id = $userId");
                    $mysqli->query("DELETE FROM users WHERE id = $userId");
                }
                
                // Create fresh developer user
                $stmt = $mysqli->prepare("INSERT INTO users (username, active, created_at, updated_at) VALUES (?, 1, NOW(), NOW())");
                $username = 'developer';
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $devUserId = $mysqli->insert_id;
                
                // Create identity
                $passwordHash = password_hash($devPassword, PASSWORD_BCRYPT);
                $stmt = $mysqli->prepare("INSERT INTO auth_identities (user_id, type, name, secret, secret2, force_reset, created_at, updated_at) VALUES (?, 'email_password', NULL, ?, ?, 0, NOW(), NOW())");
                $stmt->bind_param('iss', $devUserId, $devEmail, $passwordHash);
                $stmt->execute();
                
                // Assign developer group
                $stmt = $mysqli->prepare("INSERT INTO auth_groups_users (user_id, `group`, created_at) VALUES (?, 'developer', NOW())");
                $stmt->bind_param('i', $devUserId);
                $stmt->execute();
                
            echo '<div class="success">✓ Developer user created (ID: ' . $devUserId . ')</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">✗ Error creating users: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        echo '</div>';

        // Verify everything
        echo '<div class="step">';
        echo '<h3>Step 6: Verification</h3>';
        
        try {
            $allTables = $mysqli->query("SHOW TABLES");
            $tableCount = $allTables->num_rows;
            
            echo '<div class="success">✓ Total tables in database: ' . $tableCount . '</div>';
            
            // Check users
            $result = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE username IN ('admin', 'developer')");
            if ($result) {
                $row = $result->fetch_assoc();
                echo '<div class="success">✓ User accounts created: ' . $row['count'] . '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="warning">Could not verify: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        echo '</div>';

        $mysqli->close();
        ?>

        <div class="credentials">
            <h3>🔑 Login Credentials</h3>
            <div style="margin: 15px 0;">
                <strong>Admin Account:</strong><br>
                Email: <code><?= $adminEmail ?></code><br>
                Password: <code><?= $adminPassword ?></code>
            </div>
            <div style="margin: 15px 0;">
                <strong>Developer Account:</strong><br>
                Email: <code><?= $devEmail ?></code><br>
                Password: <code><?= $devPassword ?></code>
            </div>
            <p style="margin-top: 15px; color: #856404;">
                <strong>⚠️ Change these passwords immediately after first login!</strong>
            </p>
        </div>

        <div class="success">
            <h3>✅ Setup Complete!</h3>
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li><strong>DELETE THIS FILE:</strong> <code>public/setup_database.php</code></li>
                <li>Visit <a href="/login">the login page</a> and test both accounts</li>
                <li>Change default passwords immediately</li>
                <li>Start using your project management system!</li>
            </ol>
        </div>

        <div class="warning">
            <h3>⚠️ CRITICAL SECURITY WARNING</h3>
            <p>This file creates tables and users with default passwords. <strong>DELETE IT IMMEDIATELY</strong> after running!</p>
            <p>Command: <code>rm public/setup_database.php</code></p>
        </div>

        <a href="/login" style="display:inline-block;padding:10px 18px;border-radius:6px;text-decoration:none;background:#d4edda;color:#155724;border:1px solid #c3e6cb;margin:5px;">Go to Login</a>
        <a href="/dashboard" style="display:inline-block;padding:10px 18px;border-radius:6px;text-decoration:none;background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb;margin:5px;">Go to Dashboard</a>
    </div>
</body>
</html>
