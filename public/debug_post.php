<?php
// Debug POST requests
header('Content-Type: text/plain');

echo "=== POST REQUEST DEBUG ===\n\n";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set') . "\n\n";

echo "=== POST DATA ===\n";
echo json_encode($_POST, JSON_PRETTY_PRINT) . "\n\n";

echo "=== GET DATA ===\n";
echo json_encode($_GET, JSON_PRETTY_PRINT) . "\n\n";

echo "=== REQUEST DATA ===\n";
echo json_encode($_REQUEST, JSON_PRETTY_PRINT) . "\n\n";

echo "=== RAW INPUT ===\n";
$input = file_get_contents('php://input');
echo $input . "\n\n";

echo "=== HEADERS ===\n";
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
?>
