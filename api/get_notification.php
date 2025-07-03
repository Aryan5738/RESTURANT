<?php
require_once '../config/functions.php';

header('Content-Type: application/json');

$notification = getLatestNotification();

sendJsonResponse([
    'notification' => $notification
]);
?>