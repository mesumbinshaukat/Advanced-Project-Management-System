<?php
// Quick test to verify database updates work
$mysqli = new mysqli('localhost', 'root', '', 'project_management_db', 3306);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Get a time entry
$result = $mysqli->query("SELECT * FROM time_entries LIMIT 1");
if ($result && $result->num_rows > 0) {
    $entry = $result->fetch_assoc();
    echo "Current entry: " . json_encode($entry, JSON_PRETTY_PRINT) . "\n\n";
    
    // Try to update it
    $id = $entry['id'];
    $newHours = 5.5;
    $newDesc = "Test update from script";
    
    $updateResult = $mysqli->query("UPDATE time_entries SET hours = $newHours, description = '$newDesc' WHERE id = $id");
    
    if ($updateResult) {
        echo "Update executed\n";
        echo "Affected rows: " . $mysqli->affected_rows . "\n\n";
        
        // Fetch again
        $result2 = $mysqli->query("SELECT * FROM time_entries WHERE id = $id");
        $updated = $result2->fetch_assoc();
        echo "Updated entry: " . json_encode($updated, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Update failed: " . $mysqli->error . "\n";
    }
} else {
    echo "No time entries found\n";
}

$mysqli->close();
?>
