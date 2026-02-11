<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Сессия ВСЕГДА в самом начале
require 'db.php';

$errorMsg = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // Валидация
    if ($email === '' || $pass === '') {
        $errorMsg = "Заполните все поля!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Некорректный формат Email!";
    } else {

        // Получаем пользователя
        $sql = "SELECT id, email, password_hash, role 
                FROM users 
                WHERE email = :email 
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $errorMsg = "Пользователь не найден.";
        } elseif (!password_verify($pass, $user['password_hash'])) {
            $errorMsg = "Неверный пароль.";
        } else {
            // ✅ УСПЕШНЫЙ ВХОД

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['user_role'] = $user['role']; // 🔥 КЛЮЧЕВОЕ ИСПРАВЛЕНИЕ

            // Редирект
            if ($user['role'] === 'admin') {
                header('Location: admin_panel.php');
            } else {
                header('Location: index.php');
            }
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Вход</h4>
                </div>

                <div class="card-body">

                    <?php if ($errorMsg): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($errorMsg) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Войти
                        </button>
                    </form>

                    <div class="mt-3 text-center">
                        <a href="register.php">Нет аккаунта? Регистрация</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
