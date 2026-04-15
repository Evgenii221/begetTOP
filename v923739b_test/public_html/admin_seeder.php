<?php
// admin_seeder.php
session_start();
require 'config/db.php';
require 'check_admin.php'; // Доступ только админам!

$message = "";

// Получаем список всех таблиц в базе
$tables = [];
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tableName = $_POST['table_name'];
    $count = (int)$_POST['count'];
    
    // Защита: проверяем, есть ли такая таблица в нашем белом списке
    if (!in_array($tableName, $tables)) {
        die("Ошибка: Таблица не найдена.");
    }

    // --- ЭТАП 1: ЭКСПОРТ В CSV (БЭКАП) ---
    $exportDir = 'exports/';
    if (!is_dir($exportDir)) mkdir($exportDir);
    
    $filename = $exportDir . $tableName . '_' . date('Y-m-d_H-i-s') . '.csv';
    $fp = fopen($filename, 'w');
    
    // Получаем все данные
    $stmt = $pdo->query("SELECT * FROM `$tableName`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        $message = "Таблица пуста! Сначала создайте хотя бы одну запись вручную.";
    } else {
        // Записываем заголовки (названия колонок)
        fputcsv($fp, array_keys($rows[0]));
        
        // Записываем данные
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
        $message .= "Бэкап сохранен: $filename<br>";

        // --- ЭТАП 2: ГЕНЕРАЦИЯ (КЛОНИРОВАНИЕ) ---
        // Берем одну случайную строку как шаблон
        $template = $rows[array_rand($rows)];
        
        $inserted = 0;
        for ($i = 0; $i < $count; $i++) {
            $newRow = [];
            $cols = [];
            $vals = [];
            
            foreach ($template as $key => $value) {
                // Пропускаем ID (он автоинкрементный)
                if ($key === 'id') continue;
                
                // ЛОГИКА РАНДОМИЗАЦИИ
                if (is_numeric($value)) {
                    // Число: меняем на +/- 10-15%
                    // mt_rand(-15, 15) дает число от -15 до 15
                    $percent = mt_rand(-15, 15) / 100; 
                    $newValue = $value * (1 + $percent);
                    
                    // Если это int (например, user_id), округляем, но лучше не трогать внешние ключи
                    // Для простоты: если поле похоже на цену (price), округляем до 2 знаков
                    if (strpos($key, 'id') !== false) {
                         // ID внешних ключей лучше не менять, иначе нарушим связи!
                         $newValue = $value; 
                    } else {
                         $newValue = round($newValue, 2);
                    }
                } else {
                    // Строка: добавляем случайный хвост, чтобы обойти UNIQUE (например, email)
                    $newValue = $value . "_" . mt_rand(1000, 9999);
                    
                    // Если это дата - можно не менять или сдвигать, но оставим как есть для простоты
                }
                
                $cols[] = "`$key`";
                $vals[] = $pdo->quote($newValue); // Экранируем данные
            }
            
            // Собираем SQL INSERT
            $sql = "INSERT INTO `$tableName` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
            
            try {
                $pdo->exec($sql);
                $inserted++;
            } catch (Exception $e) {
                // Игнорируем ошибки (например, дубликаты уникальных полей), идем дальше
                continue;
            }
        }
        $message .= "Успешно сгенерировано строк: $inserted из $count.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Генератор данных</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5 bg-light">
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3>⚙️ Генератор контента (Seeder)</h3>
            </div>
            <div class="card-body">
                
                <?php if ($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label>Выберите таблицу для наполнения:</label>
                        <select name="table_name" class="form-select">
                            <?php foreach ($tables as $t): ?>
                                <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Рекомендуется выбирать products, posts или tickets.</small>
                    </div>

                    <div class="mb-3">
                        <label>Сколько записей добавить?</label>
                        <input type="number" name="count" class="form-control" value="10" min="1" max="100">
                    </div>

                    <div class="alert alert-warning">
                        <small>
                            ⚠️ <strong>Внимание:</strong> Скрипт создаст CSV-бэкап в папке /exports, а затем скопирует случайную запись указанное количество раз, изменяя числовые значения на ±15%.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-success w-100">🚀 Наполнить и Бэкапить</button>
                </form>
                
                <a href="index.php" class="btn btn-secondary mt-3">← Вернуться на сайт</a>
            </div>
        </div>
    </div>
</body>
</html>