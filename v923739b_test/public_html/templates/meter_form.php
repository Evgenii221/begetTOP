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
$message = '';
$error = '';

// Получаем квартиру пользователя
$stmt = $pdo->prepare("
    SELECT a.id, a.address
    FROM users u
    LEFT JOIN apartments a ON a.id = u.apartment_id
    WHERE u.id = ?
    LIMIT 1
");
$stmt->execute([$user_id]);
$apartment = $stmt->fetch(PDO::FETCH_ASSOC);

$apartment_id = $apartment['id'] ?? null;
$apartment_address = $apartment['address'] ?? '';

// Для подсказки на форме
$last_values = [
    'water' => null,
    'electricity' => null,
    'gas' => null
];

$tariffs = [
    'water' => null,
    'electricity' => null,
    'gas' => null
];

if (!empty($apartment_id)) {
    foreach (array_keys($last_values) as $serviceKey) {
        $stmt = $pdo->prepare("
            SELECT value
            FROM meter_readings
            WHERE apartment_id = ? AND service_type = ?
            ORDER BY created_at DESC, id DESC
            LIMIT 1
        ");
        $stmt->execute([$apartment_id, $serviceKey]);
        $value = $stmt->fetchColumn();
        $last_values[$serviceKey] = ($value !== false) ? (float)$value : null;
    }
}

foreach (array_keys($tariffs) as $serviceKey) {
    $stmt = $pdo->prepare("
        SELECT price_per_unit
        FROM tariffs
        WHERE service_type = ?
        LIMIT 1
    ");
    $stmt->execute([$serviceKey]);
    $value = $stmt->fetchColumn();
    $tariffs[$serviceKey] = ($value !== false) ? (float)$value : null;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    if (empty($apartment_id)) {
        $error = 'Сначала укажите квартиру в разделе «Моя квартира».';
    } else {
        $service_type = trim($_POST['service_type'] ?? '');
        $new_value_raw = trim($_POST['value'] ?? '');

        $allowed_services = ['water', 'electricity', 'gas'];

        if ($service_type === '' || $new_value_raw === '') {
            $error = 'Заполните все поля.';
        } elseif (!in_array($service_type, $allowed_services, true)) {
            $error = 'Выберите корректный тип услуги.';
        } elseif (!is_numeric($new_value_raw)) {
            $error = 'Показание должно быть числом.';
        } else {
            $new_value = (float)$new_value_raw;

            if ($new_value < 0) {
                $error = 'Показание не может быть отрицательным.';
            } else {
                // Предыдущее показание
                $stmt = $pdo->prepare("
                    SELECT value
                    FROM meter_readings
                    WHERE apartment_id = ? AND service_type = ?
                    ORDER BY created_at DESC, id DESC
                    LIMIT 1
                ");
                $stmt->execute([$apartment_id, $service_type]);
                $last = $stmt->fetch(PDO::FETCH_ASSOC);

                $last_value = $last ? (float)$last['value'] : 0;

                if ($new_value < $last_value) {
                    $error = 'Новое показание не может быть меньше предыдущего (' . number_format($last_value, 2, '.', ' ') . ').';
                } else {
                    $consumption = $new_value - $last_value;

                    // Получаем тариф
                    $stmt = $pdo->prepare("
                        SELECT price_per_unit
                        FROM tariffs
                        WHERE service_type = ?
                        LIMIT 1
                    ");
                    $stmt->execute([$service_type]);
                    $tariff = $stmt->fetchColumn();

                    if ($tariff === false) {
                        $error = 'Для выбранной услуги не найден тариф.';
                    } else {
                        $tariff = (float)$tariff;
                        $amount = $consumption * $tariff;

                        try {
                            $pdo->beginTransaction();

                            // Сохраняем показание
                            $stmt = $pdo->prepare("
                                INSERT INTO meter_readings (user_id, apartment_id, service_type, value)
                                VALUES (?, ?, ?, ?)
                            ");
                            $stmt->execute([$user_id, $apartment_id, $service_type, $new_value]);

                            // Создаем счет
                            $stmt = $pdo->prepare("
                                INSERT INTO bills (user_id, apartment_id, service_type, amount, status)
                                VALUES (?, ?, ?, ?, 'unpaid')
                            ");
                            $stmt->execute([$user_id, $apartment_id, $service_type, $amount]);

                            $pdo->commit();

                            $last_values[$service_type] = $new_value;
                            $message = 'Показания успешно переданы. Начислено: ' . number_format($amount, 2, '.', ' ') . ' ₽';
                        } catch (Exception $e) {
                            if ($pdo->inTransaction()) {
                                $pdo->rollBack();
                            }
                            $error = 'Ошибка при сохранении показаний: ' . $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}

renderLayoutStart([
    'metaTitle' => 'Передать показания',
    'pageTitle' => 'Передать показания',
    'pageSubtitle' => 'Введите актуальные показания счетчиков для автоматического расчета начислений.',
    'activePage' => 'meter_form'
]);
?>

<div class="panel" style="max-width: 760px;">
    <h3 class="mb-4">Форма передачи показаний</h3>

    <?php if (!empty($apartment_id)): ?>
        <div class="mb-4 p-3 rounded-4 border bg-light">
            <strong><i class="bi bi-geo-alt-fill me-2"></i>Адрес квартиры:</strong><br>
            <?= app_escape($apartment_address) ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            Квартира не указана. Сначала заполните раздел
            <a href="index.php?page=apartments">«Моя квартира»</a>
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= app_escape($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= app_escape($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="meterForm">
        <?= csrf_input() ?>

        <div class="mb-3">
            <label class="form-label">Тип услуги</label>
            <select name="service_type" id="service_type" class="form-select" required <?= empty($apartment_id) ? 'disabled' : '' ?>>
                <option value="">Выберите услугу</option>
                <option value="water">Вода</option>
                <option value="electricity">Электричество</option>
                <option value="gas">Газ</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Текущее показание</label>
            <input
                type="number"
                step="0.01"
                min="0"
                name="value"
                id="value"
                class="form-control"
                placeholder="Введите значение"
                required
                <?= empty($apartment_id) ? 'disabled' : '' ?>
            >
        </div>

        <div class="mb-4 p-3 rounded-4 border bg-light">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Предыдущее показание</div>
                    <div class="fw-semibold" id="lastValueText">—</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Расход</div>
                    <div class="fw-semibold" id="consumptionText">—</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Примерная сумма</div>
                    <div class="fw-semibold" id="amountText">—</div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-soft w-100" <?= empty($apartment_id) ? 'disabled' : '' ?>>
            <i class="bi bi-check-circle me-2"></i>Отправить показания
        </button>
    </form>
</div>

<script>
    const lastValues = {
        water: <?= $last_values['water'] !== null ? json_encode($last_values['water']) : 'null' ?>,
        electricity: <?= $last_values['electricity'] !== null ? json_encode($last_values['electricity']) : 'null' ?>,
        gas: <?= $last_values['gas'] !== null ? json_encode($last_values['gas']) : 'null' ?>
    };

    const tariffs = {
        water: <?= $tariffs['water'] !== null ? json_encode($tariffs['water']) : 'null' ?>,
        electricity: <?= $tariffs['electricity'] !== null ? json_encode($tariffs['electricity']) : 'null' ?>,
        gas: <?= $tariffs['gas'] !== null ? json_encode($tariffs['gas']) : 'null' ?>
    };

    const serviceSelect = document.getElementById('service_type');
    const valueInput = document.getElementById('value');
    const lastValueText = document.getElementById('lastValueText');
    const consumptionText = document.getElementById('consumptionText');
    const amountText = document.getElementById('amountText');

    function formatNumber(value) {
        return new Intl.NumberFormat('ru-RU', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(value);
    }

    function updateCalculation() {
        const service = serviceSelect.value;
        const currentValue = parseFloat(valueInput.value);

        if (!service) {
            lastValueText.textContent = '—';
            consumptionText.textContent = '—';
            amountText.textContent = '—';
            return;
        }

        const lastValue = lastValues[service] ?? 0;
        const tariff = tariffs[service];

        lastValueText.textContent = formatNumber(lastValue);

        if (isNaN(currentValue)) {
            consumptionText.textContent = '—';
            amountText.textContent = '—';
            return;
        }

        const consumption = currentValue - lastValue;

        if (consumption < 0) {
            consumptionText.textContent = 'Ошибка';
            amountText.textContent = 'Показание меньше предыдущего';
            return;
        }

        consumptionText.textContent = formatNumber(consumption);

        if (tariff === null) {
            amountText.textContent = 'Тариф не найден';
            return;
        }

        const amount = consumption * tariff;
        amountText.textContent = formatNumber(amount) + ' ₽';
    }

    if (serviceSelect && valueInput) {
        serviceSelect.addEventListener('change', updateCalculation);
        valueInput.addEventListener('input', updateCalculation);
    }
</script>

<?php renderLayoutEnd(); ?>