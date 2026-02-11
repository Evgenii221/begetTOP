<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$year = $_GET['year'] ?? '';

$sql = "
SELECT id, meter_id, value, photo, created_at
FROM meter_readings
WHERE 1
";

$params = [];

if ($year) {
    $sql .= " AND YEAR(created_at) = ?";
    $params[] = $year;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$readings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>История показаний</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-3 mb-3">
<div class="d-flex justify-content-between">
<h2>История показаний</h2>
<a href="index.php" class="btn btn-primary btn-sm">🏠 Главная</a>
</div>

<form method="GET" class="row g-2 mt-2">
<div class="col-auto">
<select name="year" class="form-select">
<option value="">Все годы</option>
<?php for ($y = date('Y'); $y >= 2020; $y--): ?>
<option value="<?= $y ?>" <?= ($year == $y ? 'selected' : '') ?>>
<?= $y ?>
</option>
<?php endfor; ?>
</select>
</div>
<div class="col-auto">
<button class="btn btn-dark">Найти</button>
</div>
</form>
</div>

<div class="container">
<table class="table table-bordered table-striped align-middle">
<thead class="table-dark">
<tr>
<th>#</th>
<th>ID</th>
<th>Значение</th>
<th>Дата</th>
<th>Фото</th>
</tr>
</thead>
<tbody>

<?php if (!$readings): ?>
<tr><td colspan="5" class="text-center">Нет данных</td></tr>
<?php endif; ?>

<?php foreach ($readings as $row): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= $row['meter_id'] ?></td>
<td><?= $row['value'] ?></td>
<td><?= $row['created_at'] ?></td>
<td>
<?php if ($row['photo']): ?>
<a href="<?= $row['photo'] ?>" target="_blank">
<img src="<?= $row['photo'] ?>" width="70">
</a>
<?php else: ?>
—
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>

</body>
</html>
