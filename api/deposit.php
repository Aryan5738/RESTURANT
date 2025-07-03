<?php
require_once __DIR__ . '/../config/functions.php';
if (!isset($_SESSION['user_id'])) {
    json_output(['error' => 'Not logged in'], 401);
}
require_post(['amount','txn_id']);
$amount = (float)$_POST['amount'];
$txn_id = trim($_POST['txn_id']);
if($amount<=0){ json_output(['error'=>'Invalid amount'],400); }
$stmt = $pdo->prepare('INSERT INTO deposits (user_id, txn_id, amount, status) VALUES (?,?,?,?)');
$stmt->execute([$_SESSION['user_id'],$txn_id,$amount,'pending']);
json_output(['success'=>true]);
?>