<?php
session_start();
require 'config/db.php';

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

<style>

body {
    background: #f4f6f9;
    font-family: 'Segoe UI', sans-serif;
}

.form-wrapper {
    max-width: 520px;
    margin: 60px auto;
}

.card-modern {
    background: white;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.06);
}

.title {
    font-weight: 600;
    margin-bottom: 20px;
}

.form-control {
    border-radius: 12px;
    padding: 12px;
}

.form-control:focus {
    box-shadow: 0 0 0 2px rgba(13,110,253,0.2);
}

.upload-box {
    border: 2px dashed #ced4da;
    border-radius: 14px;
    padding: 22px;
    text-align: center;
    cursor: pointer;
    transition: 0.25s;
}

.upload-box:hover {
    border-color: #0d6efd;
    background: #f8f9fa;
}

.preview {
    width: 100%;
    border-radius: 12px;
    margin-top: 12px;
    display: none;
}

.btn-primary {
    border-radius: 12px;
    padding: 12px;
    font-weight: 600;
}

.btn-secondary {
    border-radius: 12px;
}

.helper {
    font-size: 13px;
    color: #6c757d;
}

</style>

</head>

<body>

<div class="form-wrapper">

<div class="card-modern">

<h4 class="title">
📊 Отправить показания
</h4>

<?php if ($successMsg): ?>

<div class="alert alert-success">
<?= htmlspecialchars($successMsg) ?>
</div>

<?php endif; ?>

<?php if ($errorMsg): ?>

<div class="alert alert-danger">
<?= htmlspecialchars($errorMsg) ?>
</div>

<?php endif; ?>

<form action="submit_meter.php" method="POST" enctype="multipart/form-data">

<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

<div class="mb-3">

<label class="form-label">
ID счётчика
</label>

<input
class="form-control"
type="number"
name="meter_id"
required
min="1"
placeholder="Например: 12345">

<div class="helper">
Введите номер вашего счётчика
</div>

</div>

<div class="mb-3">

<label class="form-label">
Показания
</label>

<input
class="form-control"
type="number"
step="0.01"
name="value"
required
min="0"
placeholder="Например: 254.36">

</div>

<div class="mb-4">

<label class="form-label">
Фото счётчика
</label>

<div class="upload-box" onclick="document.getElementById('fileInput').click()">

📷 Нажмите, чтобы выбрать фото

<input
id="fileInput"
type="file"
name="photo"
accept="image/*"
required
style="display:none"
onchange="previewImage(event)">

<img id="preview" class="preview">

</div>

</div>

<div class="d-flex justify-content-between">

<button type="submit" class="btn btn-primary">
🚀 Отправить
</button>

<a href="index.php" class="btn btn-secondary">
🏠 Главная
</a>

</div>

</form>

</div>

</div>

<script>

function previewImage(event) {

    const file = event.target.files[0];

    if (!file) return;

    const reader = new FileReader();

    reader.onload = function(e) {

        const preview = document.getElementById('preview');

        preview.src = e.target.result;
        preview.style.display = 'block';

    };

    reader.readAsDataURL(file);

}

</script>

</body>
</html>