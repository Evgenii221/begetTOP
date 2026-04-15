<?php
session_start();

// Удаляем все данные сессии
$_SESSION = [];

// Уничтожаем сессию
session_destroy();

// Перенаправляем на страницу входа
header("Location: index.php?page=login");
exit;