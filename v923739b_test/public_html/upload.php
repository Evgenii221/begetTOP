<?php
session_start();
require 'db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) die('Доступ запрещен');

// Настройки
$uploadDir = 'uploads/'; // Папка, куда сохраняем (создайте её руками в Beget!)
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // 1. Проверка на ошибки загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Ошибка загрузки кода: " . $file['error']);
    }

    // 2. Проверка типа файла (MIME-type)
    if (!in_array($file['type'], $allowedTypes)) {
        die("Ошибка: Можно загружать только картинки (JPG, PNG, GIF).");
    }

    // 3. Генерация безопасного имени (чтобы не перезатереть чужой файл)
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = uniqid('img_') . '.' . $extension;
    $destination = $uploadDir . $newName;

    // 4. Перемещение из временной папки в постоянную
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        
        // 5. Сохраняем ПУТЬ в базу данных
        // ПРИМЕР ДЛЯ АВАТАРКИ:
        $stmt = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
        $stmt->execute([$destination, $_SESSION['user_id']]);

        echo "Файл успешно загружен! <a href='profile.php'>Вернуться</a>";
    } else {
        echo "Не удалось сохранить файл.";
    }
}
?>