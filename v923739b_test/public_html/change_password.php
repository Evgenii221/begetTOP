<?php
session_start();
require 'db.php';

// Только для авторизованных
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Смена пароля</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h4 class="mb-0">Смена пароля</h4>
                </div>

                <div class="card-body">

                    <form method="POST" action="change_password_handler.php">

                        <!-- CSRF-токен -->
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <div class="mb-3">
                            <label class="form-label">Старый пароль</label>
                            <input type="password" name="old_password"
                                   class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Новый пароль</label>
                            <input type="password" name="new_password"
                                   class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Повтор нового пароля</label>
                            <input type="password" name="new_password_confirm"
                                   class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            Сменить пароль
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
