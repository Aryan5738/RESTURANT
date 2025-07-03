<?php
session_start(); if(!isset($_SESSION['admin'])){header('Location: login.php');exit;}
require_once __DIR__ . '/../config/db.php';
$rounds=$pdo->query('SELECT * FROM rounds ORDER BY id DESC LIMIT 100')->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Rounds</title><link rel="stylesheet" href="../assets/css/bootstrap.min.css"></head><body class="p-3"><h3>Rounds History</h3>
<table class="table table-bordered"><thead><tr><th>ID</th><th>Period</th><th>Result</th><th>Time</th></tr></thead><tbody>
<?php foreach($rounds as $r): ?><tr><td><?=$r['id']?></td><td><?=$r['period_number']?></td><td><?=$r['result_color']?></td><td><?=$r['timestamp']?></td></tr><?php endforeach; ?>
</tbody></table>
<a href="dashboard.php" class="btn btn-secondary">Back</a></body></html>