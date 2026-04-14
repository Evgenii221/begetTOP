<?php
session_start();
require '../config/db.php';

$posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='pending'")->fetchColumn();
$comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
?>

<?php include "sidebar.php"; ?>

<div class="content">

<h1>Dashboard</h1>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px">

<div class="card">
<h3>📝 Посты</h3>
<h2><?= $posts ?></h2>
</div>

<div class="card">
<h3>⏳ На модерации</h3>
<h2><?= $pending ?></h2>
</div>

<div class="card">
<h3>💬 Комментарии</h3>
<h2><?= $comments ?></h2>
</div>

</div>

</div>