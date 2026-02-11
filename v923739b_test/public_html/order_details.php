<?php
$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// ПРОВЕРКА ВЛАДЕЛЬЦА
// Мы ищем заказ с таким ID И (AND) принадлежащий этому юзеру.
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    // Важно: Не пишите "Это чужой заказ". Пишите "Заказ не найден".
    // Это защита от перебора (User Enumeration).
    die("Заказ не найден или у вас нет прав на его просмотр.");
}