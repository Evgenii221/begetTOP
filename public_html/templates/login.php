<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config/db.php';

if (isset($_SESSION['user_id'])) {
    if (($_SESSION['user_role'] ?? 'resident') === 'admin') {
        header("Location: index.php?page=admin");
    } else {
        header("Location: index.php?page=dashboard");
    }
    exit;
}

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $errorMsg = 'Заполните все поля.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Некорректный формат Email.';
    } else {
        $sql = "SELECT id, name, email, password_hash, role
                FROM users
                WHERE email = :email
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $errorMsg = 'Пользователь не найден.';
        } elseif (!password_verify($pass, $user['password_hash'])) {
            $errorMsg = 'Неверный пароль.';
        } else {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'] ?? 'Пользователь';
            $_SESSION['email']     = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: index.php?page=admin");
            } else {
                header("Location: index.php?page=dashboard");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Личный кабинет ЖКХ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root{
            --bg:#eef4fb;
            --surface:#ffffff;
            --line:#e5edf7;
            --text:#172033;
            --muted:#6b7280;
            --primary:#2563eb;
            --primary-dark:#1d4ed8;
            --shadow:0 18px 40px rgba(15,23,42,.10);
        }

        *{ box-sizing:border-box; }

        body{
            margin:0;
            font-family:'Inter',sans-serif;
            background:
                radial-gradient(circle at top left, rgba(96,165,250,.22), transparent 30%),
                radial-gradient(circle at bottom right, rgba(37,99,235,.18), transparent 28%),
                linear-gradient(180deg,#f7faff 0%,#edf3fb 100%);
            color:var(--text);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px;
        }

        .auth-shell{
            width:100%;
            max-width:1100px;
            display:grid;
            grid-template-columns:1.05fr .95fr;
            background:var(--surface);
            border:1px solid var(--line);
            border-radius:28px;
            overflow:hidden;
            box-shadow:var(--shadow);
        }

        .auth-side{
            background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 45%,#60a5fa 100%);
            color:#fff;
            padding:42px;
            position:relative;
            overflow:hidden;
        }

        .auth-side::before{
            content:"";
            position:absolute;
            width:240px;
            height:240px;
            right:-70px;
            top:-70px;
            border-radius:50%;
            background:rgba(255,255,255,.10);
        }

        .auth-side::after{
            content:"";
            position:absolute;
            width:220px;
            height:220px;
            left:-80px;
            bottom:-90px;
            border-radius:50%;
            background:rgba(255,255,255,.08);
        }

        .auth-brand{
            position:relative;
            z-index:1;
            display:flex;
            align-items:center;
            gap:14px;
            margin-bottom:36px;
        }

        .auth-brand-icon{
            width:54px;
            height:54px;
            border-radius:16px;
            display:grid;
            place-items:center;
            background:rgba(255,255,255,.16);
            font-size:26px;
        }

        .auth-brand-title{
            font-size:20px;
            font-weight:800;
            line-height:1.1;
        }

        .auth-brand-subtitle{
            font-size:13px;
            color:rgba(255,255,255,.82);
            margin-top:4px;
        }

        .auth-side-content{
            position:relative;
            z-index:1;
            max-width:440px;
        }

        .auth-side h1{
            font-size:38px;
            font-weight:800;
            line-height:1.08;
            margin-bottom:16px;
        }

        .auth-side p{
            color:rgba(255,255,255,.88);
            font-size:16px;
            margin-bottom:28px;
        }

        .auth-points{
            display:grid;
            gap:14px;
            padding:0;
            margin:0;
            list-style:none;
        }

        .auth-points li{
            display:flex;
            gap:12px;
            align-items:flex-start;
            background:rgba(255,255,255,.10);
            border:1px solid rgba(255,255,255,.14);
            border-radius:16px;
            padding:14px 16px;
        }

        .auth-points i{
            font-size:18px;
            margin-top:2px;
        }

        .auth-form-wrap{
            padding:42px;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .auth-card{
            width:100%;
            max-width:420px;
        }

        .auth-badge{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:10px 14px;
            border-radius:999px;
            background:#eff6ff;
            color:var(--primary-dark);
            font-weight:700;
            margin-bottom:18px;
        }

        .auth-title{
            font-size:30px;
            font-weight:800;
            margin-bottom:8px;
        }

        .auth-subtitle{
            color:var(--muted);
            margin-bottom:24px;
        }

        .form-label{
            font-weight:600;
            color:#334155;
            margin-bottom:8px;
        }

        .form-control{
            min-height:52px;
            border-radius:14px;
            border:1px solid #d7e3f1;
            padding:12px 14px;
        }

        .form-control:focus{
            border-color:#93c5fd;
            box-shadow:0 0 0 .2rem rgba(37,99,235,.12);
        }

        .btn-auth{
            min-height:52px;
            border-radius:14px;
            font-weight:700;
        }

        .auth-footer{
            margin-top:20px;
            text-align:center;
            color:var(--muted);
        }

        .auth-footer a{
            text-decoration:none;
            font-weight:700;
        }

        @media (max-width: 920px){
            .auth-shell{
                grid-template-columns:1fr;
            }

            .auth-side{
                padding:30px 24px;
            }

            .auth-side h1{
                font-size:30px;
            }

            .auth-form-wrap{
                padding:30px 24px;
            }
        }
    </style>
</head>
<body>

<div class="auth-shell">
    <section class="auth-side">
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <i class="bi bi-buildings-fill"></i>
            </div>
            <div>
                <div class="auth-brand-title">ЖКХ Кабинет</div>
                <div class="auth-brand-subtitle">ТСЖ / Управляющая компания</div>
            </div>
        </div>

        <div class="auth-side-content">
            <h1>Добро пожаловать в личный кабинет</h1>
            <p>
                Передавайте показания, следите за начислениями и оплачивайте счета
                в удобном и понятном интерфейсе.
            </p>

            <ul class="auth-points">
                <li>
                    <i class="bi bi-speedometer2"></i>
                    <div>Передача показаний счётчиков в несколько кликов</div>
                </li>
                <li>
                    <i class="bi bi-receipt-cutoff"></i>
                    <div>Просмотр начислений и истории оплат</div>
                </li>
                <li>
                    <i class="bi bi-shield-check"></i>
                    <div>Безопасная работа с учётной записью</div>
                </li>
            </ul>
        </div>
    </section>

    <section class="auth-form-wrap">
        <div class="auth-card">
            <div class="auth-badge">
                <i class="bi bi-box-arrow-in-right"></i>
                Вход в систему
            </div>

            <div class="auth-title">Войти</div>
            <div class="auth-subtitle">Введите данные своей учётной записи</div>

            <?php if ($errorMsg): ?>
                <div class="alert alert-danger rounded-4">
                    <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        required
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Введите email"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Пароль</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                        placeholder="Введите пароль"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-auth w-100">
                    <i class="bi bi-door-open me-2"></i>Войти
                </button>
            </form>

            <div class="auth-footer">
                Нет аккаунта?
                <a href="index.php?page=register">Регистрация</a>
            </div>
        </div>
    </section>
</div>

</body>
</html>