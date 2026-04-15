<?php
session_start();
if(!isset($_SESSION['user_id'])) die("Login required");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Создать пост</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

<h3>Новый пост</h3>

<form action="blog_save.php" method="POST" enctype="multipart/form-data">

<input name="title" class="form-control mb-3" placeholder="Заголовок" required>

<textarea name="content" class="form-control mb-3" rows="6" placeholder="Текст"></textarea>

<input type="file" name="image" class="form-control mb-3">

<button class="btn btn-success">Отправить на модерацию</button>

</form>

</div>

</body>
</html>