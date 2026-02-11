<?php
session_start();
require 'db.php';
require 'check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный метод запроса");
}

// CSRF защита
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die("CSRF Attack blocked");
}

$id = (int)($_POST['id'] ?? 0);

// Получаем товар
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=? AND is_deleted=0");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    die("Ошибка выборки товара: " . $e->getMessage());
}

if ($product) {
    $quantity = (int)$product['quantity'];

    // Записываем в историю удаление
    if ($quantity > 0) {
        try {
            $history_stmt = $pdo->prepare("
                INSERT INTO history (product_id, change_type, amount, reason, changed_at)
                VALUES (?, 'расход', ?, 'Удаление товара', NOW())
            ");
            $history_stmt->execute([$id, $quantity]);
        } catch (PDOException $e) {
            die("Ошибка записи в историю: " . $e->getMessage());
        }
    }

    // Soft delete: помечаем товар как удалённый
    try {
        $delete_stmt = $pdo->prepare("UPDATE products SET is_deleted = 1 WHERE id=?");
        $delete_stmt->execute([$id]);
    } catch (PDOException $e) {
        die("Ошибка удаления товара: " . $e->getMessage());
    }
}

header("Location: index.php");
exit;
