<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'includes/layout.php';
require 'includes/csrf.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$is_admin = (($_SESSION['user_role'] ?? 'resident') === 'admin');
$message = '';
$error = '';

// Оплата счета
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $action = $_POST['action'] ?? '';

    if ($action === 'pay_bill') {
        $bill_id = (int)($_POST['bill_id'] ?? 0);

        if ($bill_id <= 0) {
            $error = 'Некорректный счет.';
        } else {
            try {
                if ($is_admin) {
                    $stmt = $pdo->prepare("
                        UPDATE bills
                        SET status = 'paid'
                        WHERE id = ? AND status != 'paid'
                    ");
                    $stmt->execute([$bill_id]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE bills
                        SET status = 'paid'
                        WHERE id = ? AND user_id = ? AND status != 'paid'
                    ");
                    $stmt->execute([$bill_id, $user_id]);
                }

                if ($stmt->rowCount() > 0) {
                    $message = 'Счет успешно оплачен.';
                } else {
                    $error = 'Не удалось оплатить счет. Возможно, он уже оплачен или недоступен.';
                }
            } catch (Exception $e) {
                $error = 'Ошибка при оплате счета: ' . $e->getMessage();
            }
        }
    }
}

// Фильтр
$filter = $_GET['status'] ?? 'all';
$allowed_filters = ['all', 'paid', 'unpaid'];
if (!in_array($filter, $allowed_filters, true)) {
    $filter = 'all';
}

// Получаем счета
$params = [];
$where = [];

if (!$is_admin) {
    $where[] = 'b.user_id = ?';
    $params[] = $user_id;
}

if ($filter === 'paid') {
    $where[] = "b.status = 'paid'";
} elseif ($filter === 'unpaid') {
    $where[] = "b.status = 'unpaid'";
}

$where_sql = '';
if (!empty($where)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
    SELECT b.*, a.address, u.name
    FROM bills b
    LEFT JOIN apartments a ON a.id = b.apartment_id
    LEFT JOIN users u ON u.id = b.user_id
    $where_sql
    ORDER BY b.created_at DESC, b.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Считаем статистику без фильтра
$statsWhere = [];
$statsParams = [];

if (!$is_admin) {
    $statsWhere[] = 'user_id = ?';
    $statsParams[] = $user_id;
}

$statsWhereSql = '';
if (!empty($statsWhere)) {
    $statsWhereSql = 'WHERE ' . implode(' AND ', $statsWhere);
}

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM bills $statsWhereSql");
$stmt->execute($statsParams);
$total_amount = (float)$stmt->fetchColumn();

$paidWhereSql = $statsWhereSql === ''
    ? "WHERE status = 'paid'"
    : $statsWhereSql . " AND status = 'paid'";
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM bills $paidWhereSql");
$stmt->execute($statsParams);
$paid_amount = (float)$stmt->fetchColumn();

$unpaidWhereSql = $statsWhereSql === ''
    ? "WHERE status = 'unpaid'"
    : $statsWhereSql . " AND status = 'unpaid'";
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM bills $unpaidWhereSql");
$stmt->execute($statsParams);
$unpaid_amount = (float)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM bills $statsWhereSql");
$stmt->execute($statsParams);
$total_bills_count = (int)$stmt->fetchColumn();

renderLayoutStart([
    'metaTitle' => 'Счета и начисления',
    'pageTitle' => 'Счета и начисления',
    'pageSubtitle' => $is_admin
        ? 'Просмотр и управление всеми счетами в системе.'
        : 'Здесь отображаются ваши начисления и доступна оплата счетов.',
    'activePage' => 'bills'
]);
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= app_escape($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= app_escape($error) ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Всего начислено</div>
                <div class="stat-value"><?= number_format($total_amount, 2, '.', ' ') ?> ₽</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Оплачено</div>
                <div class="stat-value text-success"><?= number_format($paid_amount, 2, '.', ' ') ?> ₽</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">Долг</div>
                <div class="stat-value text-danger"><?= number_format($unpaid_amount, 2, '.', ' ') ?> ₽</div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-exclamation-circle-fill"></i>
            </div>
        </div>
    </div>
</div>

<div class="panel mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="mb-1">Фильтр счетов</h3>
            <div class="text-muted">Всего счетов: <?= $total_bills_count ?></div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="index.php?page=bills&status=all" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?> btn-soft">
                Все
            </a>
            <a href="index.php?page=bills&status=paid" class="btn <?= $filter === 'paid' ? 'btn-success' : 'btn-outline-success' ?> btn-soft">
                Оплачено
            </a>
            <a href="index.php?page=bills&status=unpaid" class="btn <?= $filter === 'unpaid' ? 'btn-danger' : 'btn-outline-danger' ?> btn-soft">
                Не оплачено
            </a>
        </div>
    </div>
</div>

<div class="panel">
    <h3 class="mb-4">Список счетов</h3>

    <?php if (empty($bills)): ?>
        <div class="empty-note">
            Счета не найдены.
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
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bills as $bill): ?>
                        <tr>
                            <?php if ($is_admin): ?>
                                <td><?= app_escape($bill['name'] ?? '—') ?></td>
                            <?php endif; ?>

                            <td><?= app_escape($bill['address'] ?? '—') ?></td>

                            <td>
                                <?php
                                switch ($bill['service_type']) {
                                    case 'water':
                                        echo 'Вода';
                                        break;
                                    case 'electricity':
                                        echo 'Электричество';
                                        break;
                                    case 'gas':
                                        echo 'Газ';
                                        break;
                                    default:
                                        echo app_escape($bill['service_type']);
                                }
                                ?>
                            </td>

                            <td>
                                <strong><?= number_format((float)$bill['amount'], 2, '.', ' ') ?> ₽</strong>
                            </td>

                            <td>
                                <?php if (($bill['status'] ?? 'unpaid') === 'paid'): ?>
                                    <span class="badge bg-success">Оплачено</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Не оплачено</span>
                                <?php endif; ?>
                            </td>

                            <td><?= date('d.m.Y H:i', strtotime($bill['created_at'])) ?></td>

                            <td>
                                <?php if (($bill['status'] ?? 'unpaid') !== 'paid'): ?>
                                    <form method="POST" class="m-0">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="action" value="pay_bill">
                                        <input type="hidden" name="bill_id" value="<?= (int)$bill['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="bi bi-credit-card me-1"></i>Оплатить
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php renderLayoutEnd(); ?>