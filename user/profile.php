<?php
session_start(); if(!isset($_SESSION['user_id'])){header('Location: login.php');exit;} require_once __DIR__ . '/../config/db.php';
if($_SERVER['REQUEST_METHOD']=='POST'){
    if(isset($_POST['name'])){
        $stmt=$pdo->prepare('UPDATE users SET name=? WHERE id=?');
        $stmt->execute([$_POST['name'],$_SESSION['user_id']]);
    }
    if(!empty($_POST['newpass']) && $_POST['newpass']===$_POST['confpass']){
        $stmt=$pdo->prepare('UPDATE users SET password=? WHERE id=?');
        $stmt->execute([password_hash($_POST['newpass'],PASSWORD_DEFAULT),$_SESSION['user_id']]);
    }
}
$stmt=$pdo->prepare('SELECT uid,name,phone,balance FROM users WHERE id=?');$stmt->execute([$_SESSION['user_id']]);$u=$stmt->fetch();
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Profile</title><link rel="stylesheet" href="../assets/css/bootstrap.min.css"></head><body class="p-3">
<h4>Profile</h4>
<p><strong>UID:</strong> <?=$u['uid']?><br><strong>Phone:</strong> <?=$u['phone']?><br><strong>Balance:</strong> ₹<?=number_format($u['balance'],2)?></p>
<form method="post" class="mb-4"><h5>Update Name</h5><div class="mb-2"><input type="text" name="name" value="<?=$u['name']?>" class="form-control"></div><button class="btn btn-primary">Save Name</button></form>
<form method="post"><h5>Change Password</h5><div class="mb-2"><input type="password" name="newpass" placeholder="New Password" class="form-control"></div><div class="mb-2"><input type="password" name="confpass" placeholder="Confirm Password" class="form-control"></div><button class="btn btn-warning">Change Password</button></form>
<a href="dashboard.php" class="btn btn-secondary mt-3">Back</a>
</body></html>