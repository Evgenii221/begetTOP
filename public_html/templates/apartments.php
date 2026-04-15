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
$is_admin = ($_SESSION['user_role'] ?? 'user') === 'admin';
$message = '';
$error = '';

// Узнаем текущую квартиру пользователя
$stmt = $pdo->prepare("
    SELECT u.apartment_id, a.address
    FROM users u
    LEFT JOIN apartments a ON a.id = u.apartment_id
    WHERE u.id = ?
    LIMIT 1
");
$stmt->execute([$user_id]);
$currentUserApartment = $stmt->fetch(PDO::FETCH_ASSOC);

$current_apartment_id = $currentUserApartment['apartment_id'] ?? null;
$current_apartment_address = $currentUserApartment['address'] ?? null;

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $action = $_POST['action'] ?? '';

    if ($action === 'add_apartment') {
        $address = trim($_POST['address'] ?? '');

        if ($address === '') {
            $error = 'Введите адрес квартиры.';
        } else {
            if ($is_admin) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO apartments (address, owner_id) VALUES (?, ?)");
                    $stmt->execute([$address, $user_id]);
                    $message = 'Квартира успешно добавлена.';
                } catch (Exception $e) {
                    $error = 'Ошибка при добавлении квартиры: ' . $e->getMessage();
                }
            } else {
                if (!empty($current_apartment_id)) {
                    $error = 'У вас уже привязана квартира. Ниже можно изменить ее адрес.';
                } else {
                    try {
                        $pdo->beginTransaction();

                        $stmt = $pdo->prepare("INSERT INTO apartments (address, owner_id) VALUES (?, ?)");
                        $stmt->execute([$address, $user_id]);

                        $new_apartment_id = (int)$pdo->lastInsertId();

                        $stmt = $pdo->prepare("UPDATE users SET apartment_id = ? WHERE id = ?");
                        $stmt->execute([$new_apartment_id, $user_id]);

                        $pdo->commit();

                        $current_apartment_id = $new_apartment_id;
                        $current_apartment_address = $address;
                        $message = 'Адрес квартиры сохранен и привязан к вашему аккаунту.';
                    } catch (Exception $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        $error = 'Не удалось сохранить адрес квартиры: ' . $e->getMessage();
                    }
                }
            }
        }
    }

    if ($action === 'update_my_apartment') {
        $address = trim($_POST['address'] ?? '');

        if ($address === '') {
            $error = 'Введите адрес квартиры.';
        } elseif (empty($current_apartment_id)) {
            $error = 'Сначала добавьте квартиру.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE apartments SET address = ? WHERE id = ?");
                $stmt->execute([$address, $current_apartment_id]);

                $current_apartment_address = $address;
                $message = 'Адрес квартиры обновлен.';
            } catch (Exception $e) {
                $error = 'Не удалось обновить адрес: ' . $e->getMessage();
            }
        }
    }
}

// Для админа получаем список всех квартир
$apartments = [];
if ($is_admin) {
    $stmt = $pdo->query("
        SELECT a.id, a.address, a.owner_id,
               COUNT(u.id) AS residents_count
        FROM apartments a
        LEFT JOIN users u ON u.apartment_id = a.id
        GROUP BY a.id, a.address, a.owner_id
        ORDER BY a.id DESC
    ");
    $apartments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

renderLayoutStart([
    'metaTitle' => $is_admin ? 'Квартиры' : 'Моя квартира',
    'pageTitle' => $is_admin ? 'Квартиры' : 'Моя квартира',
    'pageSubtitle' => $is_admin
        ? 'Управление адресами квартир и привязками пользователей.'
        : 'Здесь можно указать или изменить адрес своей квартиры.',
    'activePage' => 'apartments'
]);
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= app_escape($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= app_escape($error) ?></div>
<?php endif; ?>

<?php if ($is_admin): ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="panel h-100">
                <h3 class="mb-4">Добавить квартиру</h3>

                <form method="POST">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="add_apartment">

                    <div class="mb-3">
                        <label class="form-label">Адрес квартиры</label>
                        <input
                            type="text"
                            name="address"
                            class="form-control"
                            placeholder="Например: г. Москва, ул. Ленина, д. 10, кв. 25"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-soft">
                        <i class="bi bi-plus-circle me-2"></i>Добавить квартиру
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="panel h-100">
                <h3 class="mb-4">Список квартир</h3>

                <?php if (!$apartments): ?>
                    <div class="empty-note">
                        Квартир пока нет.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Адрес</th>
                                    <th>Owner ID</th>
                                    <th>Жильцов</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($apartments as $apartment): ?>
                                    <tr>
                                        <td><?= (int)$apartment['id'] ?></td>
                                        <td><?= app_escape($apartment['address']) ?></td>
                                        <td><?= (int)$apartment['owner_id'] ?></td>
                                        <td><?= (int)$apartment['residents_count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="panel h-100">
                <h3 class="mb-4">Текущая квартира</h3>

                <?php if (!empty($current_apartment_id)): ?>
                    <div class="mb-3 p-3 rounded-4 border bg-light">
                        <div class="text-muted mb-1">Адрес</div>
                        <div class="fw-semibold"><?= app_escape($current_apartment_address) ?></div>
                    </div>

                    <form method="POST">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="update_my_apartment">

                        <div class="mb-3">
                            <label class="form-label">Изменить адрес</label>
                            <input
                                type="text"
                                name="address"
                                class="form-control"
                                value="<?= app_escape($current_apartment_address) ?>"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary btn-soft">
                            <i class="bi bi-pencil-square me-2"></i>Сохранить адрес
                        </button>
                    </form>
                <?php else: ?>
                    <div class="empty-note mb-3">
                        У вашего аккаунта пока нет привязанной квартиры.
                    </div>

                    <form method="POST">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action" value="add_apartment">

                        <div class="mb-3">
                            <label class="form-label">Введите адрес квартиры</label>
                            <input
                                type="text"
                                name="address"
                                class="form-control"
                                placeholder="Например: г. Москва, ул. Ленина, д. 10, кв. 25"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary btn-soft">
                            <i class="bi bi-house-add me-2"></i>Сохранить адрес
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel h-100">
                <h3 class="mb-4">Как это работает</h3>

                <ul class="info-list">
                    <li class="info-item">
                        <i class="bi bi-1-circle-fill"></i>
                        <div>
                            <strong>Сначала укажите адрес</strong><br>
                            Квартира будет привязана к вашему аккаунту.
                        </div>
                    </li>
                    <li class="info-item">
                        <i class="bi bi-2-circle-fill"></i>
                        <div>
                            <strong>Потом передавайте показания</strong><br>
                            Система сама подставит вашу квартиру в форме.
                        </div>
                    </li>
                    <li class="info-item">
                        <i class="bi bi-3-circle-fill"></i>
                        <div>
                            <strong>После этого появятся счета</strong><br>
                            Начисления будут формироваться автоматически.
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php renderLayoutEnd(); ?>