<?php
// DELETE THIS FILE AFTER DEBUGGING

// Step 1: confirm password_hash/password_verify works at all
$selfTest = password_hash('test', PASSWORD_BCRYPT);
echo 'Self-test (should be true): ';
var_dump(password_verify('test', $selfTest));

// Step 2: load hash from .env
$envFile = dirname(__DIR__) . '/.env';
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$key, $value] = explode('=', $line, 2);
    $_ENV[trim($key)] = rtrim(preg_replace('/[^\x20-\x7E]/', '', trim($value)), '%');
}

$hash = $_ENV['ADMIN_PASSWORD_HASH'] ?? '';
echo 'Hash from .env (length ' . strlen($hash) . '): ' . $hash . "\n";

// Step 3: generate a fresh hash of 'test' and put it in .env temporarily
$fresh = password_hash('test', PASSWORD_BCRYPT);
echo "\nFresh hash of 'test': " . $fresh . "\n";
echo 'Verify fresh hash: ';
var_dump(password_verify('test', $fresh));
echo "\nPaste the fresh hash above into ADMIN_PASSWORD_HASH in .env, then try logging in with password: test\n";
