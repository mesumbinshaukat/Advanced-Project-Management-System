<?php
/**
 * Seeder Runner - Advanced Project Management System
 * Visit: http://yourdomain.com/run_seeders.php
 *
 * Creates initial admin user and developer accounts for the system
 *
 * SECURITY: Delete this file after running!
 */

// Set proper encoding
header('Content-Type: text/html; charset=utf-8');

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

// Default credentials
$adminEmail = 'admin@example.com';
$adminPassword = 'admin123';
$devEmail = 'developer@example.com';
$devPassword = 'developer123';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Seeder Runner - PM System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
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
            color: #198754;
            border-bottom: 3px solid #198754;
            padding-bottom: 10px;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            border-left: 4px solid #198754;
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
            font-size: 13px;
        }
        .credentials {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .credentials h3 {
            margin-top: 0;
            color: #856404;
        }
        .credentials code {
            background: #fff;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 14px;
            color: #d63384;
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
        <h1>Seeder Runner - Create Initial Users</h1>
        <p><strong>Purpose:</strong> Create admin and developer accounts for first-time setup</p>
        
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
            echo '<pre>';
            echo 'Database: ' . $db_name . "\n";
            echo '</pre>';
        } catch (Exception $e) {
            echo '<div class="error">Database connection failed!</div>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '<div class="warning">Check your .env file for correct database credentials.</div>';
            die('</div></div></body></html>');
        }

        echo '</div>';

        // Check required tables exist
        echo '<div class="step">';
        echo '<h3>Step 2: Verifying Required Tables</h3>';
        
        $requiredTables = ['users', 'auth_identities', 'auth_groups_users'];
        $missingTables = [];
        
        foreach ($requiredTables as $table) {
            // Use SHOW TABLES to check existence
            $result = $mysqli->query("SHOW TABLES");
            $tableExists = false;
            
            if ($result) {
                while ($row = $result->fetch_array()) {
                    if ($row[0] === $table) {
                        $tableExists = true;
                        break;
                    }
                }
            }
            
            if (!$tableExists) {
                $missingTables[] = $table;
            }
        }
        
        if (!empty($missingTables)) {
            echo '<div class="error">Missing required tables: ' . implode(', ', $missingTables) . '</div>';
            echo '<div class="warning">Please run migrations first using run_migrations.php</div>';
            echo '<div class="info">Tip: Visit run_migrations.php to set up the database tables first.</div>';
            die('</div></div></body></html>');
        }
        
        echo '<div class="success">All required tables exist</div>';
        echo '<ul>';
        foreach ($requiredTables as $table) {
            echo '<li>' . $table . '</li>';
        }
        echo '</ul>';
        echo '</div>';

        // Create Admin User
        echo '<div class="step">';
        echo '<h3>Step 3: Creating Admin User</h3>';
        
        try {
            // Check if admin exists
            $result = $mysqli->query("SELECT id FROM users WHERE username = 'admin'");
            
            if ($result && $result->num_rows > 0) {
                $existingUser = $result->fetch_assoc();
                $userId = $existingUser['id'];
                
                echo '<div class="info">Admin user already exists (ID: ' . $userId . '). Removing old data...</div>';
                
                // Delete related records
                $mysqli->query("DELETE FROM auth_identities WHERE user_id = $userId");
                $mysqli->query("DELETE FROM auth_groups_users WHERE user_id = $userId");
                $mysqli->query("DELETE FROM users WHERE id = $userId");
                
                echo '<div class="success">Old admin user removed</div>';
            }
            
            // Create fresh admin user
            $stmt = $mysqli->prepare("INSERT INTO users (username, active, created_at, updated_at) VALUES (?, 1, NOW(), NOW())");
            $username = 'admin';
            $stmt->bind_param('s', $username);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create admin user: ' . $stmt->error);
            }
            
            $adminUserId = $mysqli->insert_id;
            
            // Create identity
            $passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare("INSERT INTO auth_identities (user_id, type, name, secret, secret2, force_reset, created_at, updated_at) VALUES (?, 'email_password', NULL, ?, ?, 0, NOW(), NOW())");
            $stmt->bind_param('iss', $adminUserId, $adminEmail, $passwordHash);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create admin identity: ' . $stmt->error);
            }
            
            // Assign admin group
            $stmt = $mysqli->prepare("INSERT INTO auth_groups_users (user_id, `group`, created_at) VALUES (?, 'admin', NOW())");
            $stmt->bind_param('i', $adminUserId);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to assign admin group: ' . $stmt->error);
            }
            
            echo '<div class="success">Admin user created successfully (ID: ' . $adminUserId . ')</div>';
            echo '<div class="success">Admin group assigned</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">Error creating admin user</div>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        }
        
        echo '</div>';

        // Create Developer User
        echo '<div class="step">';
        echo '<h3>Step 4: Creating Developer User</h3>';
        
        try {
            // Check if developer exists
            $result = $mysqli->query("SELECT id FROM users WHERE username = 'developer'");
            
            if ($result && $result->num_rows > 0) {
                $existingUser = $result->fetch_assoc();
                $userId = $existingUser['id'];
                
                echo '<div class="info">Developer user already exists (ID: ' . $userId . '). Removing old data...</div>';
                
                // Delete related records
                $mysqli->query("DELETE FROM auth_identities WHERE user_id = $userId");
                $mysqli->query("DELETE FROM auth_groups_users WHERE user_id = $userId");
                $mysqli->query("DELETE FROM users WHERE id = $userId");
                
                echo '<div class="success">Old developer user removed</div>';
            }
            
            // Create fresh developer user
            $stmt = $mysqli->prepare("INSERT INTO users (username, active, created_at, updated_at) VALUES (?, 1, NOW(), NOW())");
            $username = 'developer';
            $stmt->bind_param('s', $username);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create developer user: ' . $stmt->error);
            }
            
            $devUserId = $mysqli->insert_id;
            
            // Create identity
            $passwordHash = password_hash($devPassword, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare("INSERT INTO auth_identities (user_id, type, name, secret, secret2, force_reset, created_at, updated_at) VALUES (?, 'email_password', NULL, ?, ?, 0, NOW(), NOW())");
            $stmt->bind_param('iss', $devUserId, $devEmail, $passwordHash);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create developer identity: ' . $stmt->error);
            }
            
            // Assign developer group
            $stmt = $mysqli->prepare("INSERT INTO auth_groups_users (user_id, `group`, created_at) VALUES (?, 'developer', NOW())");
            $stmt->bind_param('i', $devUserId);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to assign developer group: ' . $stmt->error);
            }
            
            echo '<div class="success">Developer user created successfully (ID: ' . $devUserId . ')</div>';
            echo '<div class="success">Developer group assigned</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">Error creating developer user</div>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        }
        
        echo '</div>';

        // Verify users
        echo '<div class="step">';
        echo '<h3>Step 5: Verifying Created Users</h3>';
        
        try {
            $result = $mysqli->query("
                SELECT u.id, u.username, u.active, agu.group, ai.secret as email
                FROM users u
                LEFT JOIN auth_groups_users agu ON u.id = agu.user_id
                LEFT JOIN auth_identities ai ON u.id = ai.user_id AND ai.type = 'email_password'
                WHERE u.username IN ('admin', 'developer')
                ORDER BY u.id
            ");
            
            if ($result && $result->num_rows > 0) {
                echo '<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
                echo '<tr style="background: #f8f9fa;"><th>ID</th><th>Username</th><th>Email</th><th>Group</th><th>Active</th></tr>';
                
                while ($row = $result->fetch_assoc()) {
                    $activeStatus = $row['active'] ? '<span style="color: #155724;">Yes</span>' : '<span style="color: #721c24;">No</span>';
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td><strong>' . $row['username'] . '</strong></td>';
                    echo '<td>' . $row['email'] . '</td>';
                    echo '<td><span style="background: #0d6efd; color: white; padding: 3px 8px; border-radius: 3px;">' . $row['group'] . '</span></td>';
                    echo '<td>' . $activeStatus . '</td>';
                    echo '</tr>';
                }
                
                echo '</table>';
                echo '<div class="success">All users verified and active!</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">Error verifying users: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        echo '</div>';

        $mysqli->close();
        ?>

        <div class="credentials">
            <h3>Login Credentials</h3>
            <p><strong>IMPORTANT:</strong> Save these credentials securely!</p>
            
            <div style="margin: 15px 0;">
                <strong>Admin Account:</strong><br>
                Email: <code><?= $adminEmail ?></code><br>
                Password: <code><?= $adminPassword ?></code><br>
                Role: Full system access
            </div>
            
            <div style="margin: 15px 0;">
                <strong>Developer Account:</strong><br>
                Email: <code><?= $devEmail ?></code><br>
                Password: <code><?= $devPassword ?></code><br>
                Role: Limited to assigned projects/tasks
            </div>
            
            <p style="margin-top: 15px; font-size: 13px; color: #856404;">
                <strong>Change these passwords immediately after first login!</strong>
            </p>
        </div>

        <div class="success">
            <h3>Seeding Complete!</h3>
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li><strong>DELETE THIS FILE:</strong> <code>public/run_seeders.php</code></li>
                <li>Visit the login page and test both accounts</li>
                <li><strong>Change default passwords immediately</strong></li>
                <li>Start creating clients, projects, and tasks!</li>
            </ol>
        </div>

        <div class="warning">
            <h3>CRITICAL SECURITY WARNING</h3>
            <p>This file creates users with default passwords. <strong>DELETE IT IMMEDIATELY</strong> after running to prevent unauthorized account creation!</p>
            <p>Command to delete: <code>rm public/run_seeders.php</code></p>
        </div>

        <a href="/login" class="btn" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">Go to Login</a>
        <a href="/dashboard" class="btn" style="background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;">Go to Dashboard</a>
    </div>
</body>
</html>
