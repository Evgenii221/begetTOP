<?php
require 'check_admin.php'; // Только админ!
require 'db.php';

try {
    // САМОЕ СЛОЖНОЕ: Объединяем 3 таблицы в одном запросе
    // orders (главная) + users (чтобы взять email) + products (чтобы взять название)
    $sql = "
        SELECT 
            orders.id as order_id,
            orders.created_at,
            users.email,
            products.title,
            products.price
        FROM orders
        JOIN users ON orders.user_id = users.id
        JOIN products ON orders.product_id = products.id
        ORDER BY orders.id DESC
    ";

    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Ошибка подключения к базе данных: " . htmlspecialchars($e->getMessage());
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h1>Все заказы</h1>
    <a href="index.php">На главную</a>
    
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID Заказа</th>
                <th>Дата</th>
                <th>Клиент (Email)</th>
                <th>Товар</th>
                <th>Цена</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= htmlspecialchars($order['order_id']) ?></td>
                <td><?= htmlspecialchars($order['created_at']) ?></td>
                <td><?= htmlspecialchars($order['email']) ?></td>
                <td><?= htmlspecialchars($order['title']) ?></td>
                <td><?= htmlspecialchars($order['price']) ?> ₽</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
