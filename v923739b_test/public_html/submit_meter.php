<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

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

/* проверка предыдущих показаний пользователя */
$stmt = $pdo->prepare("
SELECT value FROM meter_readings
WHERE meter_id = ? AND user_id = ?
ORDER BY created_at DESC
LIMIT 1
");

$stmt->execute([$meter_id, $user_id]);
$last = $stmt->fetch();

if ($last && $new_value < $last['value']) {
    $_SESSION['meter_error'] = "Новое значение меньше предыдущего";
    header("Location: meter_form.php");
    exit;
}

/* загрузка фото */

$photoPath = null;

if (!empty($_FILES['photo']['name'])) {

    $uploadDir = 'uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);

    $fileName = time().'_'.uniqid().'.'.$ext;

    $photoPath = $uploadDir.$fileName;

    move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
}

/* сохранение */

$insert = $pdo->prepare("
INSERT INTO meter_readings (user_id, meter_id, value, photo)
VALUES (?, ?, ?, ?)
");

$insert->execute([$user_id, $meter_id, $new_value, $photoPath]);

$_SESSION['meter_success'] = "Показания успешно сохранены";

header("Location: meter_form.php");
exit;