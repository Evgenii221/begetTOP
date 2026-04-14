<?php
session_start();
require '../config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id=?");
$stmt->execute([$id]);

$post = $stmt->fetch();

if($_POST){

$stmt = $pdo->prepare("
UPDATE posts SET title=?, content=? WHERE id=?
");

$stmt->execute([
$_POST['title'],
$_POST['content'],
$id
]);

header("Location: posts.php");
}
?>

<?php include "sidebar.php"; ?>

<div class="content">

<h1>Редактировать пост</h1>

<div class="card">

<form method="POST">

<input class="form-control" name="title"
value="<?= htmlspecialchars($post['title']) ?>">

<br>

<textarea class="form-control" name="content" rows="10">
<?= htmlspecialchars($post['content']) ?>
</textarea>

<br>

<button class="btn btn-primary">
Сохранить
</button>

</form>

</div>

</div>