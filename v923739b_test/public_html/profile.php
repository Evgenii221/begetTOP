<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = null;

function uploadImage($file, $folder, $prefix, $user_id, $pdo, $column) {

$allowed_types = ['image/jpeg','image/png','image/webp'];

if ($file['error'] !== 0) return "Ошибка загрузки";

if (!in_array($file['type'],$allowed_types))
return "Разрешены JPG PNG WEBP";

if ($file['size'] > 5 * 1024 * 1024)
return "Макс размер 5MB";

if (!is_dir($folder)) {
mkdir($folder,0755,true);
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$filename = $prefix . "_" . $user_id . "_" . time() . "." . $extension;

$path = $folder . $filename;

if (move_uploaded_file($file['tmp_name'],$path)) {

$stmt = $pdo->prepare("SELECT $column FROM users WHERE id=?");
$stmt->execute([$user_id]);
$old = $stmt->fetch();

if (!empty($old[$column]) && file_exists($old[$column])) {
unlink($old[$column]);
}

$stmt = $pdo->prepare("UPDATE users SET $column=? WHERE id=?");
$stmt->execute([$path,$user_id]);

return null;
}

return "Ошибка сохранения файла";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

if(isset($_POST['username'])){

$new_name = trim($_POST['username']);

if($new_name == ""){
$error = "Имя не может быть пустым";
}else{

$stmt = $pdo->prepare("UPDATE users SET username=? WHERE id=?");
$stmt->execute([$new_name,$user_id]);

header("Location: profile.php");
exit;
}
}

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {

$error = uploadImage(
$_FILES['avatar'],
'uploads/avatars/',
'avatar',
$user_id,
$pdo,
'avatar'
);
}

if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {

$error = uploadImage(
$_FILES['banner'],
'uploads/banners/',
'banner',
$user_id,
$pdo,
'banner'
);
}

if (!$error) {
header("Location: profile.php");
exit;
}

}

$stmt = $pdo->prepare("
SELECT username, avatar, banner 
FROM users 
WHERE id=?
");

$stmt->execute([$user_id]);
$user = $stmt->fetch();

$sql = "
SELECT 
orders.id as order_id,
orders.created_at,
orders.status,
products.title,
products.price
FROM orders
JOIN products ON orders.product_id = products.id
WHERE orders.user_id = ?
ORDER BY orders.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$my_orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Профиль</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

body{
font-family:'Inter',sans-serif;
background:#f4f6fb;
}

/* NAVBAR */

.navbar{
background:white;
box-shadow:0 4px 20px rgba(0,0,0,0.08);
}

/* CARD */

.card{
border:none;
border-radius:16px;
box-shadow:0 10px 35px rgba(0,0,0,0.08);
}

/* BANNER */

.profile-banner{
height:260px;
background-position:center;
background-size:cover;
border-radius:16px 16px 0 0;
position:relative;
}

/* banner overlay FIX */

.profile-banner::after{
display:none;
}

/* banner form */

.banner-form{
position:absolute;
top:15px;
right:15px;
width:220px;
z-index:10;
}

/* AVATAR */

.avatar-wrapper{
margin-top:-70px;
}

.avatar-img{
width:160px;
height:160px;
border-radius:50%;
object-fit:cover;
border:5px solid white;
box-shadow:0 10px 25px rgba(0,0,0,0.2);
transition:0.3s;
}

.avatar-img:hover{
transform:scale(1.05);
}

/* BUTTONS */

.btn{
border-radius:10px;
font-weight:600;
}

/* ORDER CARD */

.order-card{

background:white;
border-radius:12px;
padding:15px;
margin-bottom:12px;
box-shadow:0 5px 15px rgba(0,0,0,0.06);
transition:0.2s;

}

.order-card:hover{
transform:translateY(-3px);
box-shadow:0 10px 20px rgba(0,0,0,0.1);
}

</style>

</head>

<body>

<nav class="navbar">

<div class="container">

<a class="navbar-brand fw-bold" href="index.php">
БПАН PRODUCTION
</a>

<div class="d-flex gap-2">

<a href="change_password.php" class="btn btn-outline-dark btn-sm">
Смена пароля
</a>


<a href="logout.php" class="btn btn-danger btn-sm">
Выйти
</a>

</div>

</div>

</nav>


<div class="container mt-4">


<div class="card mb-4 overflow-hidden">


<div class="profile-banner"
style="background-image:url('<?= !empty($user['banner']) ? htmlspecialchars($user['banner']) : 'https://picsum.photos/1200/400' ?>');">

<form method="POST" enctype="multipart/form-data" class="banner-form">

<input type="file" name="banner" class="form-control form-control-sm mb-1" required>

<button class="btn btn-dark btn-sm w-100">
Изменить баннер
</button>

</form>

</div>


<div class="card-body text-center">


<div class="avatar-wrapper">

<img src="<?= !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'https://i.pravatar.cc/300' ?>" class="avatar-img">

</div>


<h3 class="mt-3 fw-bold">

<?= htmlspecialchars($user['username'] ?? 'Без имени') ?>

</h3>

<p class="text-muted">

Заказов: <?= count($my_orders) ?>

</p>


<form method="POST" class="mt-3" style="max-width:300px;margin:auto;">

<input 
type="text"
name="username"
class="form-control mb-2"
placeholder="Введите новое имя"
value="<?= htmlspecialchars($user['username'] ?? '') ?>"
required
>

<button class="btn btn-success w-100">
Изменить имя
</button>

</form>


<?php if ($error): ?>

<div class="alert alert-danger mt-3">

<?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>


<form method="POST" enctype="multipart/form-data"
class="mt-3"
style="max-width:300px;margin:auto;">

<input type="file" name="avatar" class="form-control mb-2" required>

<button class="btn btn-primary w-100">
Изменить аватар
</button>

</form>

</div>

</div>



<div class="card">

<div class="card-body">

<h5 class="fw-bold mb-3">
Мои заказы
</h5>


<?php if ($my_orders): ?>


<?php foreach ($my_orders as $order): ?>


<div class="order-card">

<div class="d-flex justify-content-between">

<div>

<b><?= htmlspecialchars($order['title']) ?></b><br>

<small class="text-muted">

<?= date('d.m.Y H:i',strtotime($order['created_at'])) ?>

</small>

</div>

<div class="text-end">

<b><?= number_format($order['price'],0,'',' ') ?> ₽</b><br>

<span class="badge bg-secondary">

<?= htmlspecialchars($order['status']) ?>

</span>

</div>

</div>

</div>


<?php endforeach; ?>


<?php else: ?>


<p class="text-muted">

У вас пока нет заказов

</p>


<?php endif; ?>


</div>

</div>

</div>

</body>
</html>