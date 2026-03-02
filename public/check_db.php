<?php
// Quick database check
$mysqli = new mysqli('localhost', 'root', '', 'project_management_db', 3306);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Check system_config table
$result = $mysqli->query("SELECT * FROM system_config WHERE config_key LIKE 'superadmin%'");

if ($result && $result->num_rows > 0) {
    echo "Superadmin credentials found in database:\n\n";
    while ($row = $result->fetch_assoc()) {
        echo "Key: " . $row['config_key'] . "\n";
        echo "Value (encrypted): " . substr($row['config_value'], 0, 50) . "...\n\n";
    }
} else {
    echo "No superadmin credentials found in database.\n";
    echo "Please run /add_missing_columns.php first.\n";
}

$mysqli->close();
?>
