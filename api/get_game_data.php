<?php
require_once '../config/functions.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
}

$user = getCurrentUser();
$currentRound = getCurrentRound();
$lastResults = getLastResults('color', 5);
$timeRemaining = getTimeRemaining();

if (!$currentRound) {
    $roundId = createNewRound();
    $currentRound = getCurrentRound();
}

sendJsonResponse([
    'success' => true,
    'current_round' => $currentRound,
    'last_results' => $lastResults,
    'user_balance' => $user['balance'],
    'time_remaining' => $timeRemaining
]);
?>