<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ===== Фильтры =====
$year   = $_GET['year'] ?? '';
$search = $_GET['search'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 9;
$offset = ($page - 1) * $limit;

$sql = "
SELECT SQL_CALC_FOUND_ROWS
    id,
    meter_id,
    value,
    photo,
    status,
    created_at
FROM meter_readings
WHERE user_id = ?
";

$params = [$user_id];

if (!empty($year)) {
    $sql .= " AND YEAR(created_at) = ?";
    $params[] = $year;
}

if (!empty($search)) {
    $sql .= " AND meter_id LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$readings = $stmt->fetchAll();

$totalStmt = $pdo->query("SELECT FOUND_ROWS()");
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>История показаний</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body {
    background: #f4f6f9;
    font-family: 'Segoe UI', sans-serif;
}

.page-header {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    margin-top: 25px;
}

.card-reading {
    background: white;
    border-radius: 18px;
    padding: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    transition: 0.25s;
    height: 100%;
}

.card-reading:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px rgba(0,0,0,0.08);
}

.value {
    font-size: 26px;
    font-weight: 700;
    color: #0d6efd;
}

.meta {
    color: #6c757d;
    font-size: 14px;
}

.status {
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status-ok {
    background: #d4edda;
    color: #155724;
}

.status-wait {
    background: #fff3cd;
    color: #856404;
}

.status-bad {
    background: #f8d7da;
    color: #721c24;
}

.img-preview {
    width: 100%;
    border-radius: 12px;
    cursor: pointer;
    margin-top: 10px;
}

.filters {
    background: white;
    border-radius: 16px;
    padding: 15px;
    margin-top: 15px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}

.chart-box {
    background: white;
    border-radius: 18px;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
}

.empty {
    text-align: center;
    padding: 50px;
    color: #6c757d;
}

</style>

</head>

<body>

<div class="container">

<div class="page-header d-flex justify-content-between align-items-center flex-wrap">

<h3 class="mb-2">📊 История показаний</h3>

<div>

<a href="export_excel.php" class="btn btn-success">
📥 Excel
</a>

<a href="index.php" class="btn btn-primary">
🏠 Главная
</a>

</div>

</div>

<div class="filters">

<form method="GET" class="row g-2">

<div class="col-md-4">

<input
class="form-control"
name="search"
placeholder="Поиск по счётчику"
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-md-3">

<select name="year" class="form-select">

<option value="">Все годы</option>

<?php for ($y = date('Y'); $y >= 2020; $y--): ?>

<option value="<?= $y ?>" <?= ($year == $y ? 'selected' : '') ?>>
<?= $y ?>
</option>

<?php endfor; ?>

</select>

</div>

<div class="col-md-2">

<button class="btn btn-dark w-100">
🔍 Найти
</button>

</div>

</form>

</div>

<div class="row mt-3">

<?php if (!$readings): ?>

<div class="empty">
📭 Нет показаний
</div>

<?php endif; ?>

<?php foreach ($readings as $row): ?>

<?php
$status = $row['status'] ?? 'На проверке';
$class = 'status-wait';

if ($status === 'Принято') {
    $class = 'status-ok';
}

if ($status === 'Отклонено') {
    $class = 'status-bad';
}
?>

<div class="col-lg-4 col-md-6 mb-3">

<div class="card-reading">

<div class="value">
<?= htmlspecialchars($row['value']) ?>
</div>

<div class="meta">
Счётчик: <?= htmlspecialchars($row['meter_id']) ?>
</div>

<div class="meta">
<?= date('d.m.Y H:i', strtotime($row['created_at'])) ?>
</div>

<div class="mt-2">

<span class="status <?= $class ?>">
<?= htmlspecialchars($status) ?>
</span>

</div>

<?php if ($row['photo']): ?>

<img
src="<?= htmlspecialchars($row['photo']) ?>"
class="img-preview"
data-bs-toggle="modal"
data-bs-target="#photoModal"
onclick="showPhoto('<?= htmlspecialchars($row['photo']) ?>')">

<?php endif; ?>

</div>

</div>

<?php endforeach; ?>

</div>

<!-- Pagination -->

<nav class="mt-3">

<ul class="pagination justify-content-center">

<?php for ($i = 1; $i <= $totalPages; $i++): ?>

<li class="page-item <?= ($i == $page ? 'active' : '') ?>">

<a
class="page-link"
href="?page=<?= $i ?>&year=<?= $year ?>&search=<?= urlencode($search) ?>">

<?= $i ?>

</a>

</li>

<?php endfor; ?>

</ul>

</nav>

<div class="chart-box">

<h5 class="mb-3">📈 Динамика показаний</h5>

<canvas id="chart"></canvas>

</div>

</div>

<!-- Modal -->

<div class="modal fade" id="photoModal">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content">

<div class="modal-body text-center">

<img id="modalImage" style="max-width:100%">

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

function showPhoto(src) {
    document.getElementById("modalImage").src = src;
}

const ctx = document.getElementById('chart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            <?php foreach ($readings as $r): ?>
                "<?= date('d.m', strtotime($r['created_at'])) ?>",
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Показания',
            data: [
                <?php foreach ($readings as $r): ?>
                    <?= $r['value'] ?>,
                <?php endforeach; ?>
            ],
            tension: 0.3
        }]
    }
});

</script>

</body>
</html>
