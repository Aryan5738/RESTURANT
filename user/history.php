<?php
session_start(); if(!isset($_SESSION['user_id'])){header('Location: login.php');exit;} require_once __DIR__ . '/../config/db.php';
$rows=$pdo->prepare('SELECT p.*, r.result_color,r.period_number FROM predictions p JOIN rounds r ON r.id = p.round_id WHERE p.user_id = ? ORDER BY p.id DESC LIMIT 100');
$rows->execute([$_SESSION['user_id']]);
$data=$rows->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>History</title><link rel="stylesheet" href="../assets/css/bootstrap.min.css"></head><body class="p-3">
<h4>Your Game History</h4>
<table class="table table-bordered table-sm"><thead><tr><th>#</th><th>Period</th><th>Game</th><th>Choice</th><th>Amount</th><th>Status</th><th>Result</th></tr></thead><tbody>
<?php foreach($data as $d): ?><tr><td><?=$d['id']?></td><td><?=$d['period_number']?></td><td><?=$d['game_type']?></td><td><?=$d['color']?></td><td><?=$d['amount']?></td><td><?=$d['status']?></td><td><?=$d['result_color']?></td></tr><?php endforeach; ?>
</tbody></table>
<a href="dashboard.php" class="btn btn-secondary">Back</a>
</body></html>