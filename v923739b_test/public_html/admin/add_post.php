<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    if ($title == "" || $content == "") {
        $error = "Заполните все поля";
    } else {

        $stmt = $pdo->prepare("
        INSERT INTO posts (title, content, user_id, status, created_at)
        VALUES (?, ?, ?, 'pending', NOW())
        ");

        $stmt->execute([$title, $content, $user_id]);

        $success = "Пост отправлен на модерацию";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Новый пост</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5" style="max-width:600px;">

<h2>Создать пост</h2>

<?php if(!empty($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if(!empty($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">

<input type="text" name="title" class="form-control mb-3" placeholder="Название">

<textarea name="content" class="form-control mb-3" rows="6" placeholder="Текст поста"></textarea>

<button class="btn btn-dark">Отправить</button>

</form>

</div>

</body>
</html>