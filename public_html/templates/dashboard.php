<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/layout.php';

$user_name = $_SESSION['user_name'] ?? 'Пользователь';
$is_admin = ($_SESSION['user_role'] ?? 'user') === 'admin';

renderLayoutStart([
    'metaTitle' => 'Главная — Личный кабинет ЖКХ',
    'pageTitle' => 'Личный кабинет',
    'pageSubtitle' => 'Добро пожаловать, ' . $user_name . '. Здесь собраны все основные действия.',
    'activePage' => 'dashboard'
]);
?>

<section class="hero">
    <div class="hero-content">
        <h2>Все важное — на одном экране</h2>
        <p>
            Передавайте показания, проверяйте начисления и просматривайте историю без лишних переходов.
        </p>

        <div class="hero-actions">
            <a href="index.php?page=meter_form" class="btn btn-hero-primary">
                <i class="bi bi-speedometer2 me-2"></i>Передать показания
            </a>
            <a href="index.php?page=bills" class="btn btn-hero-secondary">
                <i class="bi bi-receipt me-2"></i>Открыть счета
            </a>
        </div>
    </div>
</section>

<section class="stats-grid">
    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Главное действие</div>
                <div class="stat-value">Показания</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Раздел оплаты</div>
                <div class="stat-value">Счета ЖКХ</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-wallet2"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Тип доступа</div>
                <div class="stat-value"><?= $is_admin ? 'Администратор' : 'Жилец' ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-person-badge-fill"></i>
            </div>
        </div>
    </div>
</section>

<h2 class="section-title">Быстрые действия</h2>

<section class="cards-grid">
    <div class="feature-card">
        <div class="feature-header">
            <div class="feature-icon icon-primary">
                <i class="bi bi-speedometer2"></i>
            </div>
            <div class="feature-badge">Основное</div>
        </div>

        <h3>Передать показания</h3>
        <p>Ввод новых значений счетчиков.</p>

        <a href="index.php?page=meter_form" class="btn btn-primary btn-soft">Открыть форму</a>
    </div>

    <div class="feature-card">
        <div class="feature-header">
            <div class="feature-icon icon-success">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div class="feature-badge">Финансы</div>
        </div>

        <h3>Счета ЖКХ</h3>
        <p>Проверка начислений и оплаты.</p>

        <a href="index.php?page=bills" class="btn btn-success btn-soft">Перейти</a>
    </div>

    <div class="feature-card">
        <div class="feature-header">
            <div class="feature-icon icon-warning">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="feature-badge">Архив</div>
        </div>

        <h3>История показаний</h3>
        <p>Просмотр прошлых данных.</p>

        <a href="index.php?page=meter_history" class="btn btn-warning btn-soft">История</a>
    </div>

    <div class="feature-card">
        <div class="feature-header">
            <div class="feature-icon icon-primary">
                <i class="bi bi-info-circle-fill"></i>
            </div>
            <div class="feature-badge">Подсказка</div>
        </div>

        <h3>Как пользоваться</h3>
        <ul class="info-list">
            <li class="info-item">
                <i class="bi bi-1-circle-fill"></i>
                <div>Передайте показания</div>
            </li>
            <li class="info-item">
                <i class="bi bi-2-circle-fill"></i>
                <div>Проверьте счета</div>
            </li>
            <li class="info-item">
                <i class="bi bi-3-circle-fill"></i>
                <div>Контролируйте историю</div>
            </li>
        </ul>
    </div>
</section>

<?php if ($is_admin): ?>
    <h2 class="section-title mt-4">Инструменты администратора</h2>

    <section class="cards-grid">
        <div class="feature-card">
            <div class="feature-header">
                <div class="feature-icon icon-danger">
                    <i class="bi bi-sliders"></i>
                </div>
                <div class="feature-badge">Управление</div>
            </div>

            <h3>Панель управления</h3>
            <p>Контроль системы.</p>

            <a href="index.php?page=admin" class="btn btn-danger btn-soft">Открыть</a>
        </div>

        <div class="feature-card">
            <div class="feature-header">
                <div class="feature-icon icon-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="feature-badge">Справочники</div>
            </div>

            <h3>Жильцы и квартиры</h3>
            <p>Управление данными.</p>

            <div class="d-flex gap-2 flex-wrap">
                <a href="index.php?page=users" class="btn btn-outline-primary btn-soft">Жильцы</a>
                <a href="index.php?page=apartments" class="btn btn-outline-primary btn-soft">Квартиры</a>
                <a href="index.php?page=tariffs" class="btn btn-outline-primary btn-soft">Тарифы</a>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php renderLayoutEnd(); ?>