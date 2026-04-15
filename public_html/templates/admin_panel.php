<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

if (($_SESSION['user_role'] ?? 'resident') !== 'admin') {
    die('Доступ запрещен.');
}

require 'includes/layout.php';

// Пользователи
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$total_admins = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'resident'");
$total_residents = (int)$stmt->fetchColumn();

// Квартиры
$stmt = $pdo->query("SELECT COUNT(*) FROM apartments");
$total_apartments = (int)$stmt->fetchColumn();

// Показания
$stmt = $pdo->query("SELECT COUNT(*) FROM meter_readings");
$total_readings = (int)$stmt->fetchColumn();

// Счета
$stmt = $pdo->query("SELECT COUNT(*) FROM bills");
$total_bills = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM bills WHERE status = 'unpaid'");
$total_unpaid_bills = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM bills WHERE status = 'unpaid'");
$total_debt = (float)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM bills WHERE status = 'paid'");
$total_paid = (float)$stmt->fetchColumn();

// Последние счета
$stmt = $pdo->query("
    SELECT b.id, b.amount, b.status, b.service_type, b.created_at,
           u.name,
           a.address
    FROM bills b
    LEFT JOIN users u ON u.id = b.user_id
    LEFT JOIN apartments a ON a.id = b.apartment_id
    ORDER BY b.created_at DESC, b.id DESC
    LIMIT 5
");
$recent_bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Последние показания
$stmt = $pdo->query("
    SELECT mr.id, mr.value, mr.service_type, mr.created_at,
           u.name,
           a.address
    FROM meter_readings mr
    LEFT JOIN users u ON u.id = mr.user_id
    LEFT JOIN apartments a ON a.id = mr.apartment_id
    ORDER BY mr.created_at DESC, mr.id DESC
    LIMIT 5
");
$recent_readings = $stmt->fetchAll(PDO::FETCH_ASSOC);

function serviceLabel($serviceType) {
    switch ($serviceType) {
        case 'water':
            return 'Вода';
        case 'electricity':
            return 'Электричество';
        case 'gas':
            return 'Газ';
        default:
            return $serviceType;
    }
}

renderLayoutStart([
    'metaTitle' => 'Панель управления',
    'pageTitle' => 'Панель управления',
    'pageSubtitle' => 'Общая статистика и последние действия в системе.',
    'activePage' => 'admin'
]);
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Пользователи</div>
                <div class="stat-value"><?= $total_users ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Квартиры</div>
                <div class="stat-value"><?= $total_apartments ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-house-door-fill"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Показания</div>
                <div class="stat-value"><?= $total_readings ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-speedometer2"></i>
            </div>
        </div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Всего счетов</div>
                <div class="stat-value"><?= $total_bills ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Неоплаченных счетов</div>
                <div class="stat-value"><?= $total_unpaid_bills ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-exclamation-circle-fill"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Общий долг</div>
                <div class="stat-value"><?= number_format($total_debt, 2, '.', ' ') ?> ₽</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="panel h-100">
            <h3 class="mb-4">Структура пользователей</h3>

            <div class="info-list">
                <div class="info-item">
                    <i class="bi bi-shield-fill"></i>
                    <div>
                        <strong>Администраторы</strong><br>
                        <?= $total_admins ?>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-person-fill"></i>
                    <div>
                        <strong>Жильцы</strong><br>
                        <?= $total_residents ?>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-wallet2"></i>
                    <div>
                        <strong>Оплачено всего</strong><br>
                        <?= number_format($total_paid, 2, '.', ' ') ?> ₽
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel h-100">
            <h3 class="mb-4">Быстрые действия</h3>

            <div class="d-flex flex-wrap gap-2">
                <a href="index.php?page=users" class="btn btn-outline-primary btn-soft">
                    <i class="bi bi-people-fill me-2"></i>Жильцы
                </a>

                <a href="index.php?page=apartments" class="btn btn-outline-primary btn-soft">
                    <i class="bi bi-house-door-fill me-2"></i>Квартиры
                </a>

                <a href="index.php?page=meter_history" class="btn btn-outline-primary btn-soft">
                    <i class="bi bi-clock-history me-2"></i>Показания
                </a>

                <a href="index.php?page=bills" class="btn btn-outline-primary btn-soft">
                    <i class="bi bi-receipt-cutoff me-2"></i>Счета
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="panel h-100">
            <h3 class="mb-4">Последние счета</h3>

            <?php if (empty($recent_bills)): ?>
                <div class="empty-note">Пока нет счетов.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Жилец</th>
                                <th>Услуга</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bills as $bill): ?>
                                <tr>
                                    <td>
                                        <?= app_escape($bill['name'] ?? '—') ?><br>
                                        <small class="text-muted"><?= app_escape($bill['address'] ?? '—') ?></small>
                                    </td>
                                    <td><?= app_escape(serviceLabel($bill['service_type'])) ?></td>
                                    <td><strong><?= number_format((float)$bill['amount'], 2, '.', ' ') ?> ₽</strong></td>
                                    <td>
                                        <?php if (($bill['status'] ?? 'unpaid') === 'paid'): ?>
                                            <span class="badge bg-success">Оплачено</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Не оплачено</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel h-100">
            <h3 class="mb-4">Последние показания</h3>

            <?php if (empty($recent_readings)): ?>
                <div class="empty-note">Пока нет показаний.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Жилец</th>
                                <th>Услуга</th>
                                <th>Показание</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_readings as $reading): ?>
                                <tr>
                                    <td>
                                        <?= app_escape($reading['name'] ?? '—') ?><br>
                                        <small class="text-muted"><?= app_escape($reading['address'] ?? '—') ?></small>
                                    </td>
                                    <td><?= app_escape(serviceLabel($reading['service_type'])) ?></td>
                                    <td><strong><?= number_format((float)$reading['value'], 2, '.', ' ') ?></strong></td>
                                    <td><?= date('d.m.Y H:i', strtotime($reading['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php renderLayoutEnd(); ?>