<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    $_SESSION['meter_error'] = "Ошибка CSRF";
    header("Location: meter_form.php");
    exit;
}

$meter_id  = (int)$_POST['meter_id'];
$new_value = (float)$_POST['value'];

if ($new_value < 0) {
    $_SESSION['meter_error'] = "Показания не могут быть отрицательными";
    header("Location: meter_form.php");
    exit;
}

// Проверка предыдущих
$stmt = $pdo->prepare("
    SELECT value FROM meter_readings
    WHERE meter_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$meter_id]);
$last = $stmt->fetch();

if ($last && $new_value < $last['value']) {
    $_SESSION['meter_error'] = "Новое значение меньше предыдущего";
    header("Location: meter_form.php");
    exit;
}

/* ===== ЗАГРУЗКА ФОТО ===== */
$photoPath = null;

if (!empty($_FILES['photo']['name'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . uniqid() . '.' . $ext;
    $photoPath = $uploadDir . $fileName;

    move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
}

/* ===== СОХРАНЕНИЕ ===== */
$insert = $pdo->prepare("
    INSERT INTO meter_readings (meter_id, value, photo)
    VALUES (?, ?, ?)
");
$insert->execute([$meter_id, $new_value, $photoPath]);

$_SESSION['meter_success'] = "Показания успешно сохранены";
header("Location: meter_form.php");
exit;
