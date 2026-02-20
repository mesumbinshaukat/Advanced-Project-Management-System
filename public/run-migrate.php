<?php

/**
 * Minimal HTTP trigger to run migrations. Requires a token defined via
 * env var `MIGRATE_WEBHOOK_TOKEN`. Only a GET request with the correct token
 * can execute `php spark migrate --all` from the project root.
 */

$token = $_GET['token'] ?? null;
$expected = getenv('MIGRATE_WEBHOOK_TOKEN');

if ($expected !== false && $token !== $expected) {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

chdir(__DIR__ . '/..');
$command = escapeshellcmd(PHP_BINARY) . ' spark migrate --all';
$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$process = proc_open($command, $descriptorSpec, $pipes);

if (!is_resource($process)) {
    http_response_code(500);
    echo "Failed to start migration command\n";
    exit;
}

fclose($pipes[0]);
$output = stream_get_contents($pipes[1]);
$error = stream_get_contents($pipes[2]);
foreach ($pipes as $pipe) {
    if (is_resource($pipe)) {
        fclose($pipe);
    }
}
$exitCode = proc_close($process);

header('Content-Type: text/plain');
echo "Exit code: $exitCode\n";
echo "Output:\n$output";
if ($error !== '') {
    echo "\nErrors:\n$error";
}
