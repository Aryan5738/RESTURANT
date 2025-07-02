<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getDishes($db);
        break;
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}

function getDishes($db) {
    $query = "SELECT id, name, description, price, image FROM dishes WHERE visible = 1 ORDER BY id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add full image URL
    foreach ($dishes as &$dish) {
        if ($dish['image']) {
            $dish['image'] = 'uploads/' . basename($dish['image']);
        } else {
            $dish['image'] = 'assets/dishes/default.jpg';
        }
    }
    
    sendResponse([
        'success' => true,
        'dishes' => $dishes
    ]);
}
?>