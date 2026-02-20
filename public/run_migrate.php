<?php

/**
 * Simple migration trigger (no framework boot). Use carefully and delete after running.
 */

$candidates = array_filter([
    getenv('PHP_CLI_BINARY') ?: null,
    '/usr/bin/php8.3',
    '/usr/bin/php8.2',
    '/usr/bin/php8.1',
    '/usr/bin/php8',
    '/usr/bin/php',
    PHP_BINARY,
]);

$phpCli = null;
foreach ($candidates as $candidate) {
    if (!$candidate || !is_executable($candidate)) {
        continue;
    }

    $esc = escapeshellcmd($candidate);
    $testOutput = [];
    $testExit = 0;
    exec($esc . ' -r "echo PHP_SAPI;"', $testOutput, $testExit);

    if ($testExit === 0 && isset($testOutput[0]) && trim($testOutput[0]) === 'cli') {
        $phpCli = $candidate;
        break;
    }
}

header('Content-Type: text/plain');

if (!$phpCli) {
    echo "No PHP CLI binary available.\n";
    exit(1);
}

chdir(__DIR__ . '/..');
$cmd = escapeshellcmd($phpCli) . ' spark migrate --all';
exec($cmd . ' 2>&1', $output, $exitCode);

echo "Using PHP binary: {$phpCli}\n";
echo "Exit code: {$exitCode}\n";
echo "Output:\n" . implode('\n', $output);
