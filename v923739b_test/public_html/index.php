<?php
session_start();
require 'db.php';

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Поиск
$where = [];
$params = [];
if (!empty($_GET['q'])) {
    $where[] = "title LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Общее количество
$count_sql = "SELECT COUNT(*) FROM products $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Добавляем фильтр is_deleted = 0
if ($where_sql) {
    $where_sql .= " AND is_deleted = 0";
} else {
    $where_sql = "WHERE is_deleted = 0";
}

$sql = "SELECT * FROM products $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мой Магазин</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f7fb; }
        .navbar { border-radius: 0 0 16px 16px; }
        .hero { background: white; border-radius: 20px; }
        .product-card { border-radius: 16px; transition: all .25s ease; }
        .product-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(0,0,0,.12); }
        .product-card img { height: 200px; object-fit: cover; border-radius: 16px 16px 0 0; }
        .price { font-size: 1.1rem; font-weight: 700; color: #0d6efd; }
    </style>
</head>
<body>

<!-- НАВИГАЦИЯ -->
<nav class="navbar navbar-light bg-white px-4 mb-4 shadow-sm">
    <span class="navbar-brand fw-bold">🛍 Мой Магазин</span>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="me-3 text-muted">Привет, <?= htmlspecialchars($_SESSION['email']) ?>!</span>
            <a href="meter_form.php" class="btn btn-outline-info btn-sm me-1">📊 Показания</a>
            <a href="meter_readings_list.php" class="btn btn-outline-secondary btn-sm me-2">📋 История</a>

            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="admin_panel.php" class="btn btn-outline-danger btn-sm me-1">Админка</a>
                <a href="add_item.php" class="btn btn-success btn-sm me-1">+ Товар</a>
                <a href="history.php" class="btn btn-warning btn-sm me-1">📊 Учет и Инвентаризация</a>
                <a href="profile.php" class="btn btn-outline-success btn-sm me-2">ЛК</a>
            <?php endif; ?>

            <a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-sm">Войти</a>
            <a href="register.php" class="btn btn-outline-primary btn-sm">Регистрация</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">

    <!-- HERO -->
    <div class="hero p-5 mb-5 shadow-sm">
        <h1 class="fw-bold mb-2">Каталог товаров</h1>
        <p class="text-muted mb-0">Найди нужный товар быстро и удобно</p>
    </div>

    <!-- ПОИСК -->
    <div class="card border-0 shadow-sm mb-5 rounded-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-9">
                    <input type="text" name="q" class="form-control form-control-lg" placeholder="🔍 Поиск по названию..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-primary btn-lg">Найти</button>
                </div>
            </form>
            <?php if (!empty($_GET['q'])): ?>
                <div class="text-end mt-2">
                    <a href="index.php" class="small text-muted">Сбросить фильтр</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ТОВАРЫ -->
    <div class="row">
        <?php foreach ($products as $product): ?>
            <?php $img = $product['image_url'] ?: 'https://via.placeholder.com/400x300'; ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 product-card border-0 shadow-sm">
                    <img src="<?= htmlspecialchars($img) ?>" alt="">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-2"><?= htmlspecialchars($product['title']) ?></h5>
                        <p class="text-muted small mb-3 text-truncate"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price"><?= htmlspecialchars($product['price']) ?> ₽</span>
                            <a href="make_order.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary btn-sm">Купить</a>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <div class="card-footer bg-transparent border-0 d-flex gap-2">
                            <a href="edit_item.php?id=<?= $product['id'] ?>" class="btn btn-outline-warning btn-sm">✏️</a>
                            <form method="POST" action="delete_item.php" onsubmit="return confirm('Удалить товар?');">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">🗑</button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>

        <?php if (!$products): ?>
            <p class="text-center text-muted">Ничего не найдено 😔</p>
        <?php endif; ?>
    </div>

    <!-- ПАГИНАЦИЯ -->
    <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= !empty($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>
</body>
</html>
