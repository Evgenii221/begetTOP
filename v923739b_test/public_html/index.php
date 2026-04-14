<?php
session_start();
require 'config/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 9;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if (!empty($_GET['q'])) {
    $where[] = "title LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

$count_sql = "SELECT COUNT(*) FROM products $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);

$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

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
<title>БПАН PRODUCTION</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

body{
font-family:'Inter',sans-serif;
background:linear-gradient(180deg,#f8f9ff,#eef1ff);
}

/* NAVBAR */

.navbar{
backdrop-filter:blur(12px);
background:rgba(255,255,255,.9);
border-radius:0 0 20px 20px;
box-shadow:0 10px 25px rgba(0,0,0,.08);
}

/* HERO */

.hero{
background:linear-gradient(135deg,#4f7cff,#7a5cff);
color:white;
border-radius:28px;
padding:70px;
margin-bottom:40px;
box-shadow:0 30px 70px rgba(0,0,0,.2);
position:relative;
overflow:hidden;
}

.hero:after{
content:"";
position:absolute;
width:500px;
height:500px;
background:rgba(255,255,255,.1);
border-radius:50%;
top:-200px;
right:-200px;
}

/* SEARCH */

.search-card{
border-radius:20px;
border:none;
}

/* PRODUCT CARD */

.product-card{
border-radius:22px;
overflow:hidden;
background:white;
transition:.35s;
}

.product-card:hover{
transform:translateY(-10px);
box-shadow:0 30px 60px rgba(0,0,0,.18);
}

.product-card img{
height:230px;
object-fit:cover;
transition:transform .4s;
}

.product-card:hover img{
transform:scale(1.07);
}

/* TEXT */

.product-card h5{
font-weight:600;
}

.product-card p{
font-size:14px;
color:#666;
}

/* PRICE */

.price{
font-size:1.25rem;
font-weight:700;
color:#4f7cff;
}

/* BUTTON */

.btn{
border-radius:12px;
transition:.25s;
}

.btn:hover{
transform:translateY(-2px);
}

/* PAGINATION */

.pagination .page-link{
border:none;
border-radius:14px;
margin:0 4px;
color:#4f7cff;
font-weight:500;
}

.pagination .active .page-link{
background:#4f7cff;
color:white;
box-shadow:0 10px 25px rgba(79,124,255,.5);
}

/* FOOTER */

.footer{
margin-top:80px;
padding:40px;
background:#111827;
color:#9ca3af;
border-radius:20px 20px 0 0;
}

.footer h5{
color:white;
}

/* ANIMATION */

.fade-item{
opacity:0;
transform:translateY(30px);
transition:all .6s ease;
}

.fade-item.show{
opacity:1;
transform:translateY(0);
}

</style>
</head>

<body>

<nav class="navbar navbar-light px-4 mb-4">

<span class="navbar-brand fw-bold">🛍 БПАН PRODUCTION</span>

<div>

<?php if (isset($_SESSION['user_id'])): ?>

<span class="me-3 text-muted">
Привет, <?= htmlspecialchars($_SESSION['email']) ?>!
</span>

<a href="profile.php" class="btn btn-outline-success btn-sm me-2">ЛК</a>
<a href="blog.php" class="btn btn-outline-dark btn-sm me-1">📰 Блог</a>
<a href="my_posts.php" class="btn btn-outline-primary btn-sm me-1">✍ Мои посты</a>
<a href="meter_form.php" class="btn btn-outline-info btn-sm me-1">📊 Показания</a>
<a href="meter_readings_list.php" class="btn btn-outline-secondary btn-sm me-2">📋 История</a>

<?php if ($_SESSION['user_role'] === 'admin'): ?>

<a href="admin_panel.php" class="btn btn-outline-danger btn-sm">Админка</a>
<a href="add_item.php" class="btn btn-success btn-sm me-1">+ Товар</a>
<a href="history.php" class="btn btn-warning btn-sm me-1">📊 Учет</a>
<a href="admin/index.php" class="btn btn-outline-danger btn-sm me-1">Блог редакт</a>

<?php endif; ?>

<a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>

<?php else: ?>

<a href="login.php" class="btn btn-primary btn-sm">Войти</a>

<a href="register.php" class="btn btn-outline-primary btn-sm">Регистрация</a>

<?php endif; ?>

</div>

</nav>

<div class="container">

<div class="hero">

<h1 class="fw-bold">Каталог товаров</h1>

<p>Найди нужный товар быстро и удобно</p>

</div>

<div class="card search-card shadow-sm mb-5">

<div class="card-body">

<form method="GET" class="row g-2">

<div class="col-md-9">

<input type="text"
name="q"
class="form-control form-control-lg"
placeholder="🔍 Поиск товара..."
value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

</div>

<div class="col-md-3 d-grid">

<button class="btn btn-primary btn-lg">
Найти
</button>

</div>

</form>

</div>

</div>

<div class="row">

<?php foreach ($products as $product): ?>


<?php $img = $product['image_url'] ?: 'https://via.placeholder.com/400x300'; ?>

<div class="col-md-4 mb-4 fade-item">

<div class="card product-card border-0 shadow-sm">

<img src="<?= htmlspecialchars($img) ?>">

<div class="card-body">

<h5><?= htmlspecialchars($product['title']) ?></h5>

<p><?= htmlspecialchars($product['description']) ?></p>

<div class="d-flex justify-content-between align-items-center">

<span class="price">
<?= htmlspecialchars($product['price']) ?> ₽
</span>

<a href="make_order.php?id=<?= $product['id'] ?>"
class="btn btn-outline-primary btn-sm">
Купить
</a>
</div>
</div>

<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
<div class="card-footer bg-transparent border-0 d-flex gap-2">

<a href="edit_item.php?id=<?= $product['id'] ?>"
class="btn btn-outline-warning btn-sm">✏️</a>

<form method="POST" action="delete_item.php"
onsubmit="return confirm('Удалить товар?');">
    <input type="hidden" name="id" value="<?= $product['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <button type="submit"
    class="btn btn-outline-danger btn-sm">🗑</button>
    </button>
    </form>
    </div>
    <?php endif; ?>
</div>

</div>

<?php endforeach; ?>

</div>

<?php if ($total_pages > 1): ?>

<nav class="mt-5">

<ul class="pagination justify-content-center">

<?php for ($i = 1; $i <= $total_pages; $i++): ?>

<li class="page-item <?= ($i == $page) ? 'active' : '' ?>">

<a class="page-link"
href="?page=<?= $i ?><?= !empty($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '' ?>">

<?= $i ?>

</a>

</li>

<?php endfor; ?>

</ul>

</nav>

<?php endif; ?>

</div>

<div class="footer text-center">

<h5>БПАН PRODUCTION</h5>

<p>© <?= date("Y") ?> Все права защищены</p>

</div>

<script>

const items=document.querySelectorAll('.fade-item');

const observer=new IntersectionObserver(entries=>{
entries.forEach(entry=>{
if(entry.isIntersecting){
entry.target.classList.add('show');
}
});
});

items.forEach(el=>observer.observe(el));

</script>

</body>
</html>