<?php
session_start();
require 'db.php';
require 'check_admin.php';

// CSRF токен
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Получаем товар
$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    die("Ошибка выборки товара: " . $e->getMessage());
}

if (!$product) {
    die("Товар не найден");
}

// POST: сохраняем изменения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("CSRF Attack blocked");
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $quantity_old = (int)$product['quantity'];
    $quantity_new = (int)($_POST['quantity'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');

    // Обновляем товар
    try {
        $update_stmt = $pdo->prepare("UPDATE products SET title=?, description=?, price=?, quantity=? WHERE id=?");
        $update_stmt->execute([$title, $description, $price, $quantity_new, $id]);
    } catch (PDOException $e) {
        die("Ошибка обновления товара: " . $e->getMessage());
    }

    // Если изменилось количество, записываем в историю
    if ($quantity_new != $quantity_old) {
        $change_type = $quantity_new > $quantity_old ? 'приход' : 'расход';
        $amount = abs($quantity_new - $quantity_old);

        try {
            $history_stmt = $pdo->prepare("
                INSERT INTO history (product_id, change_type, amount, reason, changed_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $history_stmt->execute([$id, $change_type, $amount, $reason]);
        } catch (PDOException $e) {
            die("Ошибка записи в историю: " . $e->getMessage());
        }
    }

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Редактировать товар</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Редактирование товара</h2>
    <form method="POST" class="mt-4">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="mb-3">
            <label class="form-label">Название</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($product['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Описание</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Цена</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Количество</label>
            <input type="number" name="quantity" class="form-control" value="<?= $product['quantity'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Причина изменения (для истории)</label>
            <input type="text" name="reason" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="index.php" class="btn btn-secondary">Назад</a>
    </form>
</div>
</body>
</html>
