<?php
require_once '../../config/functions.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Not authorized'], 401);
}

try {
    $stmt = $pdo->prepare("SELECT * FROM rounds ORDER BY id DESC LIMIT 20");
    $stmt->execute();
    $rounds = $stmt->fetchAll();
    
    sendJsonResponse([
        'success' => true,
        'rounds' => $rounds
    ]);
    
} catch (Exception $e) {
    sendJsonResponse(['success' => false, 'message' => 'Failed to load rounds'], 500);
}
?>