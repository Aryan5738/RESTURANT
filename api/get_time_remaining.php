<?php
require_once '../config/functions.php';

header('Content-Type: application/json');

$timeRemaining = getTimeRemaining();

sendJsonResponse([
    'time_remaining' => $timeRemaining
]);
?>