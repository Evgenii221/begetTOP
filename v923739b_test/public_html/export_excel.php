<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ===== Получаем данные =====
$stmt = $pdo->prepare(" 
    SELECT 
        id,
        meter_id,
        value,
        status,
        created_at
    FROM meter_readings
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== Заголовки для Excel =====
$filename = "meter_readings_" . date('Y-m-d_H-i-s') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

// ===== Вывод таблицы =====
echo "<table border='1'>";

echo "<tr>";
echo "<th>ID</th>";
echo "<th>Счетчик</th>";
echo "<th>Значение</th>";
echo "<th>Статус</th>";
echo "<th>Дата</th>";
echo "</tr>";

foreach ($rows as $row) {

    echo "<tr>";

    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['meter_id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['value']) . "</td>";
    echo "<td>" . htmlspecialchars($row['status'] ?? 'На проверке') . "</td>";
    echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";

    echo "</tr>";
}

echo "</table>";
