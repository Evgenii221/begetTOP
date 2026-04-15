<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'includes/layout.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$is_admin = ($_SESSION['user_role'] ?? 'user') === 'admin';

// Получаем показания
if ($is_admin) {
    $stmt = $pdo->query("
        SELECT mr.*, a.address, u.name
        FROM meter_readings mr
        LEFT JOIN apartments a ON a.id = mr.apartment_id
        LEFT JOIN users u ON u.id = mr.user_id
        ORDER BY mr.created_at DESC, mr.id DESC
    ");
    $readings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("
        SELECT mr.*, a.address
        FROM meter_readings mr
        LEFT JOIN apartments a ON a.id = mr.apartment_id
        WHERE mr.user_id = ?
        ORDER BY mr.created_at DESC, mr.id DESC
    ");
    $stmt->execute([$user_id]);
    $readings = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Статистика
$total_readings = count($readings);
$water_count = 0;
$electricity_count = 0;
$gas_count = 0;

foreach ($readings as $reading) {
    if ($reading['service_type'] === 'water') {
        $water_count++;
    } elseif ($reading['service_type'] === 'electricity') {
        $electricity_count++;
    } elseif ($reading['service_type'] === 'gas') {
        $gas_count++;
    }
}

renderLayoutStart([
    'metaTitle' => 'История показаний',
    'pageTitle' => 'История показаний',
    'pageSubtitle' => $is_admin
        ? 'Просмотр всех переданных показаний в системе.'
        : 'Здесь отображаются все ваши ранее переданные показания.',
    'activePage' => 'meter_history'
]);

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
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Всего записей</div>
                <div class="stat-value"><?= $total_readings ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">По воде</div>
                <div class="stat-value"><?= $water_count ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-droplet-half"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">По электричеству / газу</div>
                <div class="stat-value"><?= $electricity_count + $gas_count ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-lightning-charge"></i>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <h3 class="mb-4">Список переданных показаний</h3>

    <?php if (empty($readings)): ?>
        <div class="empty-note">
            Пока нет показаний. Сначала передайте данные счетчиков в разделе
            <a href="meter_form.php">«Передать показания»</a>.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <?php if ($is_admin): ?>
                            <th>Жилец</th>
                        <?php endif; ?>
                        <th>Квартира</th>
                        <th>Услуга</th>
                        <th>Показание</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($readings as $reading): ?>
                        <tr>
                            <?php if ($is_admin): ?>
                                <td><?= app_escape($reading['name'] ?? '—') ?></td>
                            <?php endif; ?>

                            <td><?= app_escape($reading['address'] ?? '—') ?></td>

                            <td><?= app_escape(serviceLabel($reading['service_type'])) ?></td>

                            <td>
                                <strong><?= number_format((float)$reading['value'], 2, '.', ' ') ?></strong>
                            </td>

                            <td><?= date('d.m.Y H:i', strtotime($reading['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php renderLayoutEnd(); ?>