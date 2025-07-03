<?php
session_start(); if(!isset($_SESSION['admin'])){header('Location: login.php');exit;}
require_once __DIR__ . '/../config/db.php';
if($_SERVER['REQUEST_METHOD']=='POST'){
    $msg=trim($_POST['message']);
    if($msg){
        $stmt=$pdo->prepare('INSERT INTO notifications (message) VALUES (?)');
        $stmt->execute([$msg]);
        $success=true;
    }
}
$rows=$pdo->query('SELECT * FROM notifications ORDER BY id DESC LIMIT 20')->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Notifications</title><link rel="stylesheet" href="../assets/css/bootstrap.min.css"></head><body class="p-3">
<h3>Send Notification</h3>
<?php if(isset($success)): ?><div class="alert alert-success">Sent</div><?php endif; ?>
<form method="post" class="mb-3"><div class="mb-3"><textarea name="message" class="form-control" required></textarea></div><button class="btn btn-primary">Send</button></form>
<h5>Recent</h5>
<ul class="list-group">
<?php foreach($rows as $n): ?><li class="list-group-item">[<?=$n['created_at']?>] <?=$n['message']?></li><?php endforeach; ?>
</ul>
<a href="dashboard.php" class="btn btn-secondary mt-3">Back</a>
</body></html>