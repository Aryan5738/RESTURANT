<?php
session_start(); if(!isset($_SESSION['user_id'])){header('Location: login.php');exit;} require_once __DIR__ . '/../config/db.php';
$stmt=$pdo->prepare('SELECT balance FROM users WHERE id=?');$stmt->execute([$_SESSION['user_id']]);$bal=$stmt->fetchColumn();
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Wallet</title><link rel="stylesheet" href="../assets/css/bootstrap.min.css"></head><body class="p-3 text-center">
<h4>Wallet Balance</h4>
<h2 class="mb-4">₹<?=number_format($bal,2)?></h2>
<div class="d-grid gap-2 mb-3" style="max-width:300px;margin:auto;">
<a href="deposit.php" class="btn btn-success">Deposit</a>
<a href="withdraw.php" class="btn btn-danger">Withdraw</a>
<a href="history.php" class="btn btn-secondary">Game History</a>
</div>
<a href="dashboard.php" class="btn btn-outline-secondary">Back</a>
</body></html>