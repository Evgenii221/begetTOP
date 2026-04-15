<?php
session_start();
require 'config/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
SELECT * FROM posts
WHERE user_id=?
ORDER BY created_at DESC
");

$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Мои посты</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

<h2>Мои посты</h2>

<a href="blog_add.php" class="btn btn-success mb-3">
Создать пост
</a>

<?php foreach($posts as $post): ?>

<div class="card mb-3">

<div class="card-body">

<h4><?= htmlspecialchars($post['title']) ?></h4>

<p>
Статус:
<b><?= $post['status'] ?></b>
</p>

<p>
<?= mb_substr(strip_tags($post['content']),0,200) ?>...
</p>

<a href="posts.php?id=<?= $post['id'] ?>" class="btn btn-primary btn-sm">
Открыть
</a>

</div>

</div>

<?php endforeach; ?>

</div>

</body>
</html>