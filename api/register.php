<?php
require_once __DIR__ . '/../config/functions.php';
require_post(['phone', 'name', 'password']);

$phone    = $_POST['phone'];
$name     = $_POST['name'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

if (get_user_by_phone($phone)) {
    json_output(['error' => 'Phone already registered'], 400);
}

$uid = generate_uid();

$stmt = $pdo->prepare('INSERT INTO users (uid, name, phone, password, balance) VALUES (?,?,?,?,0)');
$stmt->execute([$uid, $name, $phone, $password]);
$user_id = $pdo->lastInsertId();

$_SESSION['user_id'] = $user_id;
json_output(['success' => true, 'uid' => $uid]);
?>