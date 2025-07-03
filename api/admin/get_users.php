<?php
require_once '../../config/functions.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    sendJsonResponse(['success' => false, 'message' => 'Not authorized'], 401);
}

try {
    $stmt = $pdo->prepare("SELECT id, uid, name, phone, balance, created_at FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    sendJsonResponse([
        'success' => true,
        'users' => $users
    ]);
    
} catch (Exception $e) {
    sendJsonResponse(['success' => false, 'message' => 'Failed to load users'], 500);
}
?>