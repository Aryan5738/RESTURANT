<?php
require_once __DIR__ . '/../config/functions.php';
require_post(['phone', 'password']);

$phone    = $_POST['phone'];
$password = $_POST['password'];

$user = get_user_by_phone($phone);
if (!$user || !password_verify($password, $user['password'])) {
    json_output(['error' => 'Invalid credentials'], 401);
}

$_SESSION['user_id'] = $user['id'];
json_output([
    'success'  => true,
    'uid'      => $user['uid'],
    'balance'  => (float) $user['balance'],
    'name'     => $user['name'],
]);
?>