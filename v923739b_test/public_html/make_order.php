<?php
session_start();
require 'db.php';

// 1. Проверка: Вошел ли пользователь?
if (!isset($_SESSION['user_id'])) {
    die("Сначала войдите в систему! <a href='login.php'>Вход</a>");
}

// 2. Получаем ID товара из ссылки (например, make_order.php?id=5)
$product_id = (int)$_GET['id']; 
$user_id = $_SESSION['user_id'];

if ($product_id > 0) {
    
    // ПРОВЕРКА БЕЗОПАСНОСТИ №2: А есть ли такой товар в таблице products?
    $check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $check->execute([$product_id]);
    $exists = $check->fetch();

    if (!$exists) {
        // Если товар не найден, прекращаем выполнение
        die("Ошибка: Попытка заказать несуществующий товар! Ваш IP записан.");
    }

    // 3. Если проверка пройдена, создаем заказ
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id) VALUES (?, ?)");
    try {
        $stmt->execute([$user_id, $product_id]);
        echo "Заказ успешно оформлен! Менеджер свяжется с вами. <a href='index.php'>Вернуться</a>";
    } catch (PDOException $e) {
        // Ошибка базы данных (например, проблемы с соединением)
        echo "Ошибка при создании заказа: " . $e->getMessage();
    }

} else {
    echo "Неверный ID товара.";
}
?>
