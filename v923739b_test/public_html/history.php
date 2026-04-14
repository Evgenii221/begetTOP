<?php
session_start();
require 'config/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Проверка на админа
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Доступ запрещён!");
}

// Получаем историю изменений
$sql = "SELECT h.*, p.title 
        FROM history h
        LEFT JOIN products p ON h.product_id = p.id
        ORDER BY h.changed_at DESC";
$stmt = $pdo->query($sql);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Учет и Инвентаризация</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>История изменений товаров</h2>
    <button class="btn btn-primary mb-3" onclick="window.print()">🖨 Печать отчета</button>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Товар</th>
                <th>Тип изменения</th>
                <th>Количество</th>
                <th>Причина</th>
                <th>Дата и время</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($history): ?>
                <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?= htmlspecialchars($h['id']) ?></td>
                        <td><?= htmlspecialchars($h['title'] ?? 'Товар удален') ?></td>
                        <td><?= htmlspecialchars($h['change_type']) ?></td>
                        <td><?= htmlspecialchars($h['amount']) ?></td>
                        <td><?= htmlspecialchars($h['reason']) ?></td>
                        <td><?= htmlspecialchars($h['changed_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">История пока пустая 😔</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="index.php" class="btn btn-secondary mt-3">← Назад</a>
</div>
</body>
</html>
