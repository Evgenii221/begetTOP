<?php
session_start();
require 'db.php';

// Только авторизованный пользователь
if (!isset($_SESSION['user_id'])) {
    die('Доступ запрещён');
}

/* ======================
   CSRF-ПРОВЕРКА
====================== */
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die('Ошибка CSRF-токена');
}

/* ======================
   ПОЛУЧАЕМ ДАННЫЕ
====================== */
$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirm     = $_POST['new_password_confirm'] ?? '';

if ($oldPassword === '' || $newPassword === '' || $confirm === '') {
    die('Заполните все поля');
}

/* ======================
   ВАЛИДАЦИЯ
====================== */
if (strlen($newPassword) < 8) {
    die('Новый пароль должен быть не короче 8 символов');
}

if ($newPassword !== $confirm) {
    die('Пароли не совпадают');
}

/* ======================
   ПРОВЕРКА СТАРОГО ПАРОЛЯ
====================== */
$stmt = $pdo->prepare(
    "SELECT password_hash FROM users WHERE id = ? LIMIT 1"
);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
    die('Старый пароль неверный');
}

/* ======================
   ХЕШИРОВАНИЕ И СОХРАНЕНИЕ
====================== */
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "UPDATE users SET password_hash = ? WHERE id = ?"
);
$stmt->execute([$newHash, $_SESSION['user_id']]);

// Обновляем CSRF-токен (плюс к безопасности)
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

echo "Пароль успешно изменён";
