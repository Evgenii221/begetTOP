<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === Генерация CSRF токена ===
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
 * Файл конфигурации базы данных
 * Используется паттерн PDO (PHP Data Objects)
 */

// 1. Настройки (Beget: host всегда localhost)
$host = 'localhost';
$db   = 'v923739b_test';
$user = 'v923739b_test';
$pass = 'phpMyAdmin2';
$charset = 'utf8mb4';

// 2. DSN
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// 3. Options для PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
