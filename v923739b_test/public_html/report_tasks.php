<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'config/db.php'; // подключение к БД

function printTable($data) {
    if (empty($data)) {
        echo "<p>Нет данных</p>";
        return;
    }

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr>
            <th>ID</th>
            <th>ФИО</th>
            <th>Билет</th>
            <th>Группа</th>
            <th>Стипендия</th>
            <th>Город</th>
          </tr>";

    foreach ($data as $row) {
        echo "<tr>
                <td>".htmlspecialchars($row['id'])."</td>
                <td>".htmlspecialchars($row['fio'])."</td>
                <td>".htmlspecialchars($row['student_ticket'])."</td>
                <td>".htmlspecialchars($row['group_number'])."</td>
                <td>".htmlspecialchars($row['scholarship'])."</td>
                <td>".htmlspecialchars($row['city'])."</td>
              </tr>";
    }

    echo "</table><br>";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Отчет по заданиям</title>
</head>
<body>

<h1>15 задание</h1>

<!-- 1 -->
<h2>1. Сортировка по алфавиту (А-Я)</h2>
<?php
$sorted = $pdo->query("SELECT * FROM students ORDER BY fio ASC")->fetchAll();
printTable($sorted);
?>

<!-- 2 -->
<h2>2. Стипендия 1000р, Новосибирск, группа 204</h2>
<?php
$filtered = $pdo->query("
    SELECT * FROM students
    WHERE scholarship = 1000
    AND city = 'Новосибирск'
    AND group_number = '204'
")->fetchAll();
printTable($filtered);
?>

<!-- 3 -->
<h2>3. Студенты с билетами 101009 и 101010</h2>
<?php
$two = $pdo->query("
    SELECT * FROM students
    WHERE student_ticket IN ('101009', '101010')
")->fetchAll();
printTable($two);
?>

<!-- 4 -->
<h2>4. Студенты, чье имя начинается на А</h2>
<?php
$nameA = $pdo->query("
    SELECT fio FROM students
    WHERE fio LIKE 'А%'
")->fetchAll();

if (!empty($nameA)) {
    echo "<ul>";
    foreach ($nameA as $row) {
        echo "<li>".htmlspecialchars($row['fio'])."</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Нет данных</p>";
}
?>

<!-- 5 -->
<h2>5. Удаление одной записи</h2>

<h3>До удаления:</h3>
<?php
$before = $pdo->query("SELECT * FROM students")->fetchAll();
printTable($before);
?>

<?php
// удалим запись с максимальным ID
$pdo->query("DELETE FROM students ORDER BY id DESC LIMIT 1");
?>

<h3>После удаления:</h3>
<?php
$after = $pdo->query("SELECT * FROM students")->fetchAll();
printTable($after);
?>

<!-- 6 -->
<h2>6. Добавление двух Котовых (Новосибирск)</h2>

<?php
$pdo->exec("
    INSERT INTO students (fio, student_ticket, group_number, scholarship, city)
    VALUES
    ('Котов Иван Сергеевич', '700001', '205', 1200, 'Новосибирск'),
    ('Котов Алексей Сергеевич', '700002', '205', 1100, 'Новосибирск')
");

$kotovs = $pdo->query("
    SELECT * FROM students
    WHERE fio LIKE 'Котов%'
    AND city = 'Новосибирск'
")->fetchAll();

printTable($kotovs);
?>

</body>
</html>
