<?php
if (!function_exists('app_escape')) {
    function app_escape($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('renderLayoutStart')) {
    function renderLayoutStart($config = []) {
        $metaTitle = $config['metaTitle'] ?? 'Личный кабинет ЖКХ';
        $pageTitle = $config['pageTitle'] ?? 'Личный кабинет';
        $pageSubtitle = $config['pageSubtitle'] ?? 'Добро пожаловать в систему.';
        $activePage = $config['activePage'] ?? 'dashboard';

        $user_role = $_SESSION['user_role'] ?? 'user';
        $user_name = $_SESSION['user_name'] ?? 'Пользователь';
        $is_admin = $user_role === 'admin';
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= app_escape($metaTitle) ?></title>

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

            <style>
                :root{
                    --bg:#f4f7fb;
                    --surface:#ffffff;
                    --surface-2:#f8fafc;
                    --line:#e7edf5;
                    --text:#172033;
                    --muted:#6b7280;
                    --primary:#2563eb;
                    --primary-dark:#1d4ed8;
                    --success:#16a34a;
                    --warning:#f59e0b;
                    --danger:#dc2626;
                    --sidebar-start:#0f172a;
                    --sidebar-end:#182338;
                    --shadow:0 12px 32px rgba(15,23,42,.08);
                    --radius:22px;
                }

                *{ box-sizing:border-box; }

                body{
                    margin:0;
                    font-family:'Inter',sans-serif;
                    background:linear-gradient(180deg,#f7f9fc 0%,#eef3f9 100%);
                    color:var(--text);
                }

                .app-layout{
                    display:flex;
                    min-height:100vh;
                }

                .app-sidebar{
                    width:290px;
                    background:linear-gradient(180deg,var(--sidebar-start),var(--sidebar-end));
                    color:#fff;
                    padding:28px 20px;
                    display:flex;
                    flex-direction:column;
                    position:sticky;
                    top:0;
                    height:100vh;
                }

                .app-brand{
                    display:flex;
                    align-items:center;
                    gap:14px;
                    margin-bottom:28px;
                    padding:10px 12px;
                    border-radius:18px;
                    background:rgba(255,255,255,.05);
                    border:1px solid rgba(255,255,255,.08);
                }

                .app-brand-icon{
                    width:46px;
                    height:46px;
                    border-radius:14px;
                    display:grid;
                    place-items:center;
                    background:linear-gradient(135deg,#3b82f6,#60a5fa);
                    font-size:22px;
                    flex-shrink:0;
                }

                .app-brand-title{
                    font-size:18px;
                    font-weight:800;
                    line-height:1.15;
                }

                .app-brand-subtitle{
                    font-size:12px;
                    color:rgba(255,255,255,.7);
                    margin-top:3px;
                }

                .app-nav-label{
                    font-size:12px;
                    text-transform:uppercase;
                    letter-spacing:.08em;
                    color:rgba(255,255,255,.45);
                    margin:20px 12px 10px;
                }

                .app-nav{
                    display:flex;
                    flex-direction:column;
                    gap:8px;
                }

                .app-nav-link{
                    display:flex;
                    align-items:center;
                    gap:12px;
                    text-decoration:none;
                    color:rgba(255,255,255,.88);
                    padding:13px 14px;
                    border-radius:16px;
                    transition:.25s ease;
                    font-weight:500;
                }

                .app-nav-link i{
                    font-size:18px;
                    width:20px;
                    text-align:center;
                }

                .app-nav-link:hover,
                .app-nav-link.active{
                    color:#fff;
                    background:rgba(255,255,255,.1);
                    transform:translateX(4px);
                }

                .app-sidebar-footer{
                    margin-top:auto;
                    padding:16px;
                    border-radius:18px;
                    background:rgba(255,255,255,.05);
                    border:1px solid rgba(255,255,255,.08);
                }

                .app-sidebar-footer .muted{
                    color:rgba(255,255,255,.7);
                    font-size:14px;
                }

                .app-main{
                    flex:1;
                    padding:28px;
                }

                .app-topbar{
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    gap:16px;
                    margin-bottom:24px;
                    flex-wrap:wrap;
                }

                .app-page-title{
                    font-size:28px;
                    font-weight:800;
                    margin:0;
                }

                .app-page-subtitle{
                    margin:6px 0 0;
                    color:var(--muted);
                }

                .app-role-badge{
                    display:inline-flex;
                    align-items:center;
                    gap:8px;
                    padding:10px 14px;
                    border-radius:999px;
                    background:#fff;
                    border:1px solid var(--line);
                    box-shadow:var(--shadow);
                    font-size:14px;
                    font-weight:600;
                }

                .hero{
                    background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 45%,#60a5fa 100%);
                    color:#fff;
                    border-radius:28px;
                    padding:32px;
                    box-shadow:0 18px 40px rgba(37,99,235,.22);
                    margin-bottom:24px;
                    position:relative;
                    overflow:hidden;
                }

                .hero::after{
                    content:"";
                    position:absolute;
                    right:-60px;
                    top:-40px;
                    width:220px;
                    height:220px;
                    border-radius:50%;
                    background:rgba(255,255,255,.10);
                }

                .hero::before{
                    content:"";
                    position:absolute;
                    right:80px;
                    bottom:-70px;
                    width:180px;
                    height:180px;
                    border-radius:50%;
                    background:rgba(255,255,255,.08);
                }

                .hero-content{
                    position:relative;
                    z-index:2;
                }

                .hero h2{
                    font-size:30px;
                    font-weight:800;
                    margin-bottom:12px;
                }

                .hero p{
                    max-width:700px;
                    margin-bottom:22px;
                    color:rgba(255,255,255,.88);
                }

                .hero-actions{
                    display:flex;
                    gap:12px;
                    flex-wrap:wrap;
                }

                .btn-hero-primary{
                    background:#fff;
                    color:var(--primary-dark);
                    border:none;
                    border-radius:14px;
                    padding:13px 18px;
                    font-weight:700;
                }

                .btn-hero-primary:hover{
                    background:#f8fafc;
                    color:var(--primary-dark);
                }

                .btn-hero-secondary{
                    background:rgba(255,255,255,.12);
                    color:#fff;
                    border:1px solid rgba(255,255,255,.25);
                    border-radius:14px;
                    padding:13px 18px;
                    font-weight:700;
                }

                .btn-hero-secondary:hover{
                    background:rgba(255,255,255,.18);
                    color:#fff;
                }

                .section-title{
                    font-size:20px;
                    font-weight:800;
                    margin:8px 0 16px;
                }

                .cards-grid{
                    display:grid;
                    grid-template-columns:repeat(2, minmax(0,1fr));
                    gap:18px;
                }

                .feature-card{
                    background:var(--surface);
                    border:1px solid var(--line);
                    border-radius:24px;
                    padding:22px;
                    box-shadow:var(--shadow);
                    transition:.25s ease;
                    height:100%;
                }

                .feature-card:hover{
                    transform:translateY(-4px);
                    box-shadow:0 16px 34px rgba(15,23,42,.10);
                }

                .feature-header{
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                    margin-bottom:14px;
                    gap:10px;
                }

                .feature-icon{
                    width:52px;
                    height:52px;
                    border-radius:16px;
                    display:grid;
                    place-items:center;
                    font-size:22px;
                    flex-shrink:0;
                }

                .icon-primary{ background:#eff6ff; color:#2563eb; }
                .icon-success{ background:#ecfdf3; color:#16a34a; }
                .icon-warning{ background:#fffbeb; color:#d97706; }
                .icon-danger{ background:#fef2f2; color:#dc2626; }

                .feature-badge{
                    font-size:12px;
                    font-weight:700;
                    padding:8px 10px;
                    border-radius:999px;
                    background:var(--surface-2);
                    color:var(--muted);
                    border:1px solid var(--line);
                }

                .feature-card h3{
                    font-size:20px;
                    font-weight:800;
                    margin-bottom:8px;
                }

                .feature-card p{
                    color:var(--muted);
                    margin-bottom:16px;
                }

                .stats-grid{
                    display:grid;
                    grid-template-columns:repeat(3,minmax(0,1fr));
                    gap:18px;
                    margin-bottom:24px;
                }

                .stat-card{
                    background:var(--surface);
                    border:1px solid var(--line);
                    border-radius:22px;
                    padding:20px;
                    box-shadow:var(--shadow);
                }

                .stat-top{
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    margin-bottom:14px;
                }

                .stat-title{
                    color:var(--muted);
                    font-size:14px;
                    margin-bottom:8px;
                }

                .stat-value{
                    font-size:22px;
                    font-weight:800;
                    line-height:1.1;
                }

                .stat-icon{
                    width:44px;
                    height:44px;
                    border-radius:14px;
                    display:grid;
                    place-items:center;
                    font-size:20px;
                    background:#eff6ff;
                    color:var(--primary);
                }

                .panel{
                    background:#fff;
                    border:1px solid var(--line);
                    border-radius:24px;
                    box-shadow:var(--shadow);
                    padding:24px;
                }

                .form-label{
                    font-weight:600;
                    color:#334155;
                    margin-bottom:8px;
                }

                .form-control,
                .form-select{
                    border-radius:14px;
                    border:1px solid #d9e3f0;
                    min-height:50px;
                    padding:12px 14px;
                }

                .form-control:focus,
                .form-select:focus{
                    border-color:#93c5fd;
                    box-shadow:0 0 0 .2rem rgba(37,99,235,.12);
                }

                .btn-soft{
                    border-radius:14px;
                    padding:12px 18px;
                    font-weight:700;
                }

                .info-list{
                    list-style:none;
                    padding:0;
                    margin:0;
                    display:grid;
                    gap:14px;
                }

                .info-item{
                    display:flex;
                    gap:12px;
                    align-items:flex-start;
                }

                .info-item i{
                    width:38px;
                    height:38px;
                    display:grid;
                    place-items:center;
                    border-radius:12px;
                    background:#eff6ff;
                    color:var(--primary);
                    flex-shrink:0;
                }

                .empty-note{
                    color:var(--muted);
                    padding:16px;
                    border-radius:16px;
                    background:#f8fafc;
                    border:1px dashed var(--line);
                }

                @media (max-width: 1100px){
                    .stats-grid,
                    .cards-grid{
                        grid-template-columns:1fr;
                    }
                }

                @media (max-width: 900px){
                    .app-layout{
                        flex-direction:column;
                    }

                    .app-sidebar{
                        width:100%;
                        height:auto;
                        position:relative;
                    }

                    .app-main{
                        padding:18px;
                    }

                    .hero{
                        padding:24px;
                    }

                    .hero h2{
                        font-size:24px;
                    }

                    .app-page-title{
                        font-size:24px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="app-layout">
                <aside class="app-sidebar">
                    <div class="app-brand">
                        <div class="app-brand-icon">
                            <i class="bi bi-buildings-fill"></i>
                        </div>
                        <div>
                            <div class="app-brand-title">ЖКХ Кабинет</div>
                            <div class="app-brand-subtitle">ТСЖ / Управляющая компания</div>
                        </div>
                    </div>

                    <div class="app-nav-label">Основное</div>
                    <nav class="app-nav">
                        <a href="index.php?page=dashboard" class="app-nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                            <i class="bi bi-grid-1x2-fill"></i>
                            <span>Главная</span>
                        </a>

                        <a href="index.php?page=apartments" class="app-nav-link <?= $activePage === 'apartments' ? 'active' : '' ?>">
                            <i class="bi bi-door-open-fill"></i>
                            <span><?= $is_admin ? 'Квартиры' : 'Моя квартира' ?></span>
                        </a>

                        <a href="index.php?page=meter_form" class="app-nav-link <?= $activePage === 'meter_form' ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Передать показания</span>
                        </a>

                        <a href="index.php?page=meter_history" class="app-nav-link <?= $activePage === 'meter_history' ? 'active' : '' ?>">
                            <i class="bi bi-clock-history"></i>
                            <span>История показаний</span>
                        </a>

                        <a href="index.php?page=bills" class="app-nav-link <?= $activePage === 'bills' ? 'active' : '' ?>">
                            <i class="bi bi-receipt-cutoff"></i>
                            <span>Счета и начисления</span>
                        </a>
                    </nav>

                    <?php if ($is_admin): ?>
                        <div class="app-nav-label">Администрирование</div>
                        <nav class="app-nav">
                            <a href="index.php?page=admin" class="app-nav-link <?= $activePage === 'admin' ? 'active' : '' ?>">
                                <i class="bi bi-sliders"></i>
                                <span>Панель управления</span>
                            </a>

                            <a href="index.php?page=users" class="app-nav-link <?= $activePage === 'users' ? 'active' : '' ?>">
                                <i class="bi bi-people-fill"></i>
                                <span>Жильцы</span>
                            </a>

                            <a href="index.php?page=tariffs" class="app-nav-link <?= $activePage === 'tariffs' ? 'active' : '' ?>">
                                <i class="bi bi-cash-coin"></i>
                                <span>Тарифы</span>
                            </a>
                        </nav>
                    <?php endif; ?>

                    <div class="app-sidebar-footer">
                        <div class="fw-semibold mb-1">Вы вошли в систему</div>
                        <div class="muted mb-3">
                            <?= app_escape($user_name) ?><br>
                            Роль: <strong><?= $is_admin ? 'Администратор' : 'Жилец' ?></strong>
                        </div>
                        <a href="logout.php" class="btn btn-outline-light w-100 rounded-4">
                            <i class="bi bi-box-arrow-right me-2"></i>Выйти
                        </a>
                    </div>
                </aside>

                <main class="app-main">
                    <div class="app-topbar">
                        <div>
                            <h1 class="app-page-title"><?= app_escape($pageTitle) ?></h1>
                            <p class="app-page-subtitle"><?= app_escape($pageSubtitle) ?></p>
                        </div>

                        <div class="app-role-badge">
                            <i class="bi bi-shield-check"></i>
                            <?= $is_admin ? 'Режим администратора' : 'Кабинет жильца' ?>
                        </div>
                    </div>
        <?php
    }
}

if (!function_exists('renderLayoutEnd')) {
    function renderLayoutEnd() {
        ?>
                </main>
            </div>
        </body>
        </html>
        <?php
    }
}