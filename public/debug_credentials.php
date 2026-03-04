<?php
// Debug credentials access for project 29
$mysqli = new mysqli('localhost', 'root', '', 'project_management_db', 3306);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "<h2>Debug Credentials Access for Project 29</h2>\n";

// Check project_users table
echo "<h3>Project Users (project_users table)</h3>\n";
$result = $mysqli->query("SELECT pu.*, u.username FROM project_users pu LEFT JOIN users u ON u.id = pu.user_id WHERE pu.project_id = 29");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "User ID: " . $row['user_id'] . " (" . $row['username'] . "), Role: " . $row['role'] . "<br>\n";
    }
} else {
    echo "No users assigned via project_users table<br>\n";
}

// Check legacy task assignments
echo "<h3>Legacy Task Assignments (tasks.assigned_to)</h3>\n";
$result = $mysqli->query("SELECT DISTINCT assigned_to, u.username FROM tasks t LEFT JOIN users u ON u.id = t.assigned_to WHERE t.project_id = 29 AND t.assigned_to IS NOT NULL AND t.deleted_at IS NULL");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "User ID: " . $row['assigned_to'] . " (" . $row['username'] . ")<br>\n";
    }
} else {
    echo "No users assigned via legacy tasks.assigned_to<br>\n";
}

// Check new task assignments
echo "<h3>New Task Assignments (task_assignments table)</h3>\n";
$result = $mysqli->query("SELECT DISTINCT ta.user_id, u.username FROM task_assignments ta LEFT JOIN tasks t ON t.id = ta.task_id LEFT JOIN users u ON u.id = ta.user_id WHERE t.project_id = 29 AND t.deleted_at IS NULL");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "User ID: " . $row['user_id'] . " (" . $row['username'] . ")<br>\n";
    }
} else {
    echo "No users assigned via task_assignments table<br>\n";
}

// Check all users and their groups
echo "<h3>All Users and Groups</h3>\n";
$result = $mysqli->query("SELECT u.id, u.username, ag.group FROM users u LEFT JOIN auth_groups_users agu ON agu.user_id = u.id LEFT JOIN auth_groups ag ON ag.id = agu.group_id WHERE u.active = 1");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "User ID: " . $row['id'] . " (" . $row['username'] . "), Group: " . ($row['group'] ?? 'none') . "<br>\n";
    }
}

// Check project credentials
echo "<h3>Project Credentials</h3>\n";
$result = $mysqli->query("SELECT * FROM project_credentials WHERE project_id = 29");
if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " credentials for project 29<br>\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Label: " . $row['label'] . ", Type: " . $row['credential_type'] . "<br>\n";
    }
} else {
    echo "No credentials found for project 29<br>\n";
}

$mysqli->close();
?>
