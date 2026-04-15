<?php
session_start();
require 'config/db.php';
require 'includes/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['user_role'] ?? 'user') !== 'admin') {
    die('Доступ запрещен.');
}

require 'includes/layout.php';

$message = '';
$error = '';

// Обработка привязки квартиры к пользователю
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $action = $_POST['action'] ?? '';

    if ($action === 'bind_apartment') {
        $target_user_id = (int)($_POST['user_id'] ?? 0);
        $apartment_id = (int)($_POST['apartment_id'] ?? 0);

        if ($target_user_id <= 0 || $apartment_id <= 0) {
            $error = 'Выберите пользователя и квартиру.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET apartment_id = ? WHERE id = ?");
                $stmt->execute([$apartment_id, $target_user_id]);

                if ($stmt->rowCount() > 0) {
                    $message = 'Квартира успешно привязана к пользователю.';
                } else {
                    $message = 'Данные сохранены. Возможно, квартира уже была привязана к этому пользователю.';
                }
            } catch (Exception $e) {
                $error = 'Ошибка при привязке квартиры: ' . $e->getMessage();
            }
        }
    }
}

// Получаем всех пользователей
$stmt = $pdo->query("
    SELECT u.id, u.name, u.email, u.role, u.apartment_id, a.address
    FROM users u
    LEFT JOIN apartments a ON a.id = u.apartment_id
    ORDER BY u.id DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем квартиры для select
$stmt = $pdo->query("
    SELECT id, address
    FROM apartments
    ORDER BY id DESC
");
$apartments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Статистика
$total_users = count($users);
$admin_count = 0;
$resident_count = 0;
$with_apartment_count = 0;

foreach ($users as $user) {
    if (($user['role'] ?? 'user') === 'admin') {
        $admin_count++;
    } else {
        $resident_count++;
    }

    if (!empty($user['apartment_id'])) {
        $with_apartment_count++;
    }
}

renderLayoutStart([
    'metaTitle' => 'Жильцы',
    'pageTitle' => 'Жильцы',
    'pageSubtitle' => 'Управление пользователями и привязкой квартир.',
    'activePage' => 'users'
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
                <div class="stat-title">Всего пользователей</div>
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
                <div class="stat-title">Администраторы</div>
                <div class="stat-value"><?= $admin_count ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-shield-fill"></i>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-title">С привязанной квартирой</div>
                <div class="stat-value"><?= $with_apartment_count ?></div>
            </div>
            <div class="stat-icon">
                <i class="bi bi-house-door-fill"></i>
            </div>
        </div>
    </div>
</div>

<div class="panel mb-4">
    <h3 class="mb-4">Привязать квартиру к пользователю</h3>

    <form method="POST" class="row g-3">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="bind_apartment">

        <div class="col-md-5">
            <label class="form-label">Пользователь</label>
            <select name="user_id" class="form-select" required>
                <option value="">Выберите пользователя</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= (int)$user['id'] ?>">
                        #<?= (int)$user['id'] ?> — <?= app_escape($user['name'] ?? 'Без имени') ?>
                        <?php if (!empty($user['email'])): ?>
                            (<?= app_escape($user['email']) ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-5">
            <label class="form-label">Квартира</label>
            <select name="apartment_id" class="form-select" required>
                <option value="">Выберите квартиру</option>
                <?php foreach ($apartments as $apartment): ?>
                    <option value="<?= (int)$apartment['id'] ?>">
                        #<?= (int)$apartment['id'] ?> — <?= app_escape($apartment['address']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-soft w-100">
                <i class="bi bi-link-45deg me-2"></i>Привязать
            </button>
        </div>
    </form>
</div>

<div class="panel">
    <h3 class="mb-4">Список пользователей</h3>

    <?php if (empty($users)): ?>
        <div class="empty-note">
            Пользователей пока нет.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Квартира</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= (int)$user['id'] ?></td>
                            <td><?= app_escape($user['name'] ?? '—') ?></td>
                            <td><?= app_escape($user['email'] ?? '—') ?></td>
                            <td>
                                <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                                    <span class="badge bg-danger">Администратор</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Жилец</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($user['address'])): ?>
                                    <?= app_escape($user['address']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Не привязана</span>
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