<?php
require_once __DIR__ . '/../config/functions.php';
if (!isset($_SESSION['user_id'])) {
    json_output(['error' => 'Not logged in'], 401);
}
require_post(['upi_id','account_no','ifsc','bank_name','password','amount']);
$amount = (float)$_POST['amount'];
if($amount<=0){ json_output(['error'=>'Invalid amount'],400); }
// Check balance
$stmt = $pdo->prepare('SELECT balance,password FROM users WHERE id=?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if(!$user){ json_output(['error'=>'User not found'],404); }
if(!password_verify($_POST['password'],$user['password'])){
    json_output(['error'=>'Password incorrect'],403);
}
if($user['balance'] < $amount){
    json_output(['error'=>'Insufficient balance'],400);
}
// Create withdraw request (balance deducted after approval)
$stmt = $pdo->prepare('INSERT INTO withdraws (user_id, upi_id, account_no, ifsc, bank_name, password, amount, status) VALUES (?,?,?,?,?,?,?,?)');
$stmt->execute([
    $_SESSION['user_id'],
    $_POST['upi_id'],
    $_POST['account_no'],
    $_POST['ifsc'],
    $_POST['bank_name'],
    password_hash($_POST['password'], PASSWORD_DEFAULT),
    $amount,
    'pending'
]);
json_output(['success'=>true]);
?>