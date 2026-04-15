<?php

$config = require_once __DIR__ . '/env.php';

$host = $config['db_host'];
$db   = $config['db_name'];
$user = $config['db_user'];
$pass = getenv('DB_PASS') ?: $config['db_pass'];
$charset = $config['db_charset'];

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}