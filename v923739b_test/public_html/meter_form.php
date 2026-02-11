<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$successMsg = $_SESSION['meter_success'] ?? '';
$errorMsg   = $_SESSION['meter_error'] ?? '';
unset($_SESSION['meter_success'], $_SESSION['meter_error']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отправка показаний</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-6">

<div class="card shadow">
<div class="card-header bg-info text-white">
    <h4 class="mb-0">Отправить показания счётчика</h4>
</div>

<div class="card-body">

<?php if ($successMsg): ?>
<div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
<?php endif; ?>

<?php if ($errorMsg): ?>
<div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<form action="submit_meter.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>

<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

<div class="mb-3">
<label class="form-label">ID счётчика</label>
<input type="number" name="meter_id" class="form-control" required min="1">
</div>

<div class="mb-3">
<label class="form-label">Показания</label>
<input type="number" step="0.01" name="value" class="form-control" required min="0">
</div>

<div class="mb-3">
<label class="form-label">Фото счётчика</label>
<input type="file" name="photo" class="form-control" accept="image/*" required>
</div>

<div class="d-flex justify-content-between">
<button type="submit" class="btn btn-info">Отправить</button>
<a href="index.php" class="btn btn-secondary">🏠 Главная</a>
</div>

</form>
</div>
</div>

</div>
</div>
</div>

</body>
</html>
