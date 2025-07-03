<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/../config/db.php';

// actions
if(isset($_GET['approve']) || isset($_GET['reject'])){
    $id = isset($_GET['approve']) ? (int)$_GET['approve'] : (int)$_GET['reject'];
    $newStatus = isset($_GET['approve']) ? 'approved' : 'rejected';
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT * FROM withdraws WHERE id=? AND status="pending"');
    $stmt->execute([$id]);
    if($row = $stmt->fetch()){
        if($newStatus=='approved'){
            // deduct balance
            $pdo->prepare('UPDATE users SET balance = balance - ? WHERE id=?')->execute([$row['amount'],$row['user_id']]);
        }
        $pdo->prepare('UPDATE withdraws SET status=? WHERE id=?')->execute([$newStatus,$id]);
    }
    $pdo->commit();
    header('Location: withdraws.php');
    exit;
}

$rows = $pdo->query('SELECT w.*, u.uid FROM withdraws w JOIN users u ON u.id = w.user_id ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"><title>Withdraws</title>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="p-3">
<h3>Withdraw Requests</h3>
<table class="table table-bordered">
<thead><tr><th>ID</th><th>UID</th><th>Amount</th><th>UPI</th><th>Account</th><th>IFSC</th><th>Bank</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td><?=$r['id']?></td>
<td><?=$r['uid']?></td>
<td>₹<?=number_format($r['amount'],2)?></td>
<td><?=$r['upi_id']?></td>
<td><?=$r['account_no']?></td>
<td><?=$r['ifsc']?></td>
<td><?=$r['bank_name']?></td>
<td><?=$r['status']?></td>
<td>
<?php if($r['status']=='pending'): ?>
<a href="?approve=<?=$r['id']?>" class="btn btn-sm btn-success">Approve</a>
<a href="?reject=<?=$r['id']?>" class="btn btn-sm btn-danger">Reject</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<a href="dashboard.php" class="btn btn-secondary">Back</a>
</body>
</html>