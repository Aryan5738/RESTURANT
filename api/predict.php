<?php
require_once __DIR__ . '/../config/functions.php';

if (!isset($_SESSION['user_id'])) {
    json_output(['error' => 'Not logged in'], 401);
}

require_post(['game_type', 'color', 'amount']);

$user_id  = $_SESSION['user_id'];
$gameType = $_POST['game_type'];
$color    = strtoupper($_POST['color']);
$amount   = (float) $_POST['amount'];

if ($amount <= 0) {
    json_output(['error' => 'Invalid amount'], 400);
}

// Fetch balance
$stmt    = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$balance = (float) $stmt->fetchColumn();

if ($balance < $amount) {
    json_output(['error' => 'Insufficient balance'], 400);
}

// Current round
$stmt  = $pdo->query('SELECT id FROM rounds ORDER BY id DESC LIMIT 1');
$round = $stmt->fetch();
if (!$round) {
    json_output(['error' => 'No active round'], 400);
}
$round_id = $round['id'];

// Record prediction
$stmt = $pdo->prepare('INSERT INTO predictions (user_id, round_id, game_type, color, amount, status) VALUES (?,?,?,?,?,?)');
$stmt->execute([$user_id, $round_id, $gameType, $color, $amount, 'pending']);

// Deduct balance
$stmt = $pdo->prepare('UPDATE users SET balance = balance - ? WHERE id = ?');
$stmt->execute([$amount, $user_id]);

json_output(['success' => true, 'new_balance' => $balance - $amount]);
?>