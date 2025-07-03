<?php
require_once '../../config/functions.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Not authorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$result = $input['result'] ?? null;

if (!$result || !in_array($result, ['red', 'green', 'violet'])) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid result color'], 400);
}

try {
    // Get current active round
    $round = getCurrentRound();
    if (!$round) {
        sendJsonResponse(['success' => false, 'message' => 'No active round found'], 400);
    }
    
    $pdo->beginTransaction();
    
    // Update round with result
    $stmt = $pdo->prepare("UPDATE rounds SET result_color = ?, status = 'completed', end_time = NOW() WHERE id = ?");
    $stmt->execute([$result, $round['id']]);
    
    // Get all predictions for this round
    $stmt = $pdo->prepare("SELECT * FROM predictions WHERE round_id = ? AND status = 'pending'");
    $stmt->execute([$round['id']]);
    $predictions = $stmt->fetchAll();
    
    foreach ($predictions as $prediction) {
        $winAmount = calculatePayout($prediction['color'], $result, $prediction['amount']);
        $status = $winAmount > 0 ? 'win' : 'loss';
        
        // Update prediction status
        $stmt = $pdo->prepare("UPDATE predictions SET status = ?, win_amount = ? WHERE id = ?");
        $stmt->execute([$status, $winAmount, $prediction['id']]);
        
        // If won, add amount to user balance
        if ($winAmount > 0) {
            updateUserBalance($prediction['user_id'], $winAmount);
        }
    }
    
    // Create new round
    $newRoundId = createNewRound($round['game_type']);
    
    $pdo->commit();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Round completed successfully',
        'result' => $result,
        'new_round_id' => $newRoundId
    ]);
    
} catch (Exception $e) {
    $pdo->rollback();
    sendJsonResponse(['success' => false, 'message' => 'Failed to set result: ' . $e->getMessage()], 500);
}
?>