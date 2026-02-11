<?php
require 'check_admin.php'; // Вызов охраны
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <div class="alert alert-success">
        <h1>Панель Администратора</h1>
        <p>Добро пожаловать в систему управления.</p>
        <a href="add_item.php" class="btn btn-primary">Добавить запись</a>
        <a href="logout.php" class="btn btn-danger">Выйти</a>
        <a href="index.php" class="btn btn-danger">Перейти на сайт</a>
    </div>
</body>
</html>