<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

// Approve
if(isset($_GET['approve'])){
    $id = (int)$_GET['approve'];
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT * FROM deposits WHERE id=? AND status="pending"');
    $stmt->execute([$id]);
    if($dep = $stmt->fetch()){
        $pdo->prepare('UPDATE deposits SET status="approved" WHERE id=?')->execute([$id]);
        $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id=?')->execute([$dep['amount'],$dep['user_id']]);
    }
    $pdo->commit();
    header('Location: deposits.php');
    exit;
}

$rows = $pdo->query('SELECT d.*, u.uid FROM deposits d JOIN users u ON u.id = d.user_id ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Deposits</title>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="p-3">
<h3>Deposits</h3>
<table class="table table-bordered">
<thead><tr><th>ID</th><th>UID</th><th>TXN ID</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td><?=$r['id']?></td>
<td><?=$r['uid']?></td>
<td><?=$r['txn_id']?></td>
<td>₹<?=number_format($r['amount'],2)?></td>
<td><?=$r['status']?></td>
<td>
<?php if($r['status']=='pending'): ?>
<a href="?approve=<?=$r['id']?>" class="btn btn-sm btn-success">Approve</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<a href="dashboard.php" class="btn btn-secondary">Back</a>
</body>
</html>