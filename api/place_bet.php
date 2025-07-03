<?php
require_once '../config/functions.php';

header('Content-Type: application/json');

if (!isUserLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
}

$roundId = $input['round_id'] ?? null;
$color = $input['color'] ?? null;
$amount = $input['amount'] ?? null;
$gameType = $input['game_type'] ?? 'color';

// Validate inputs
if (!$roundId || !$color || !$amount) {
    sendJsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
}

if (!isValidAmount($amount)) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid amount'], 400);
}

$userId = $_SESSION['user_id'];
$userBalance = getUserBalance($userId);

if ($amount > $userBalance) {
    sendJsonResponse(['success' => false, 'message' => 'Insufficient balance'], 400);
}

// Check if round is still active
$round = getCurrentRound($gameType);
if (!$round || $round['id'] != $roundId) {
    sendJsonResponse(['success' => false, 'message' => 'Round not active'], 400);
}

// Check time remaining
$timeRemaining = getTimeRemaining();
if ($timeRemaining <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'Betting time expired'], 400);
}

try {
    $pdo->beginTransaction();
    
    // Deduct amount from user balance
    $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmt->execute([$amount, $userId]);
    
    // Insert prediction
    $stmt = $pdo->prepare("INSERT INTO predictions (user_id, round_id, game_type, color, amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $roundId, $gameType, $color, $amount]);
    
    $pdo->commit();
    
    $newBalance = getUserBalance($userId);
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Bet placed successfully',
        'new_balance' => $newBalance
    ]);
    
} catch (Exception $e) {
    $pdo->rollback();
    sendJsonResponse(['success' => false, 'message' => 'Failed to place bet'], 500);
}
?>