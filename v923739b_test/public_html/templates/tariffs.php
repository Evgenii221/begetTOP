<?php
session_start();
require 'config/db.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Нет доступа");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$service = $_POST['service_name'];
$price = $_POST['price'];

$stmt = $pdo->prepare(
"INSERT INTO tariffs (service_name, price_per_unit)
VALUES (?, ?)"
);

$stmt->execute([$service, $price]);
}

$tariffs = $pdo->query(
"SELECT * FROM tariffs"
)->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Тарифы</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

<h2>Тарифы</h2>

<form method="POST" class="row g-2 mb-4">

<div class="col-md-5">
<input name="service_name" class="form-control" placeholder="Услуга">
</div>

<div class="col-md-5">
<input name="price" type="number" step="0.01" class="form-control" placeholder="Цена">
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">
Добавить
</button>
</div>

</form>

<table class="table table-bordered">

<tr>
<th>ID</th>
<th>Услуга</th>
<th>Цена</th>
</tr>

<?php foreach ($tariffs as $t): ?>

<tr>
<td><?= $t['id'] ?></td>
<td><?= $t['service_name'] ?></td>
<td><?= $t['price_per_unit'] ?> ₽</td>
</tr>

<?php endforeach; ?>

</table>

</div>

</body>
</html>