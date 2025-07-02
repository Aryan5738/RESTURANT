<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        getOrders($db);
        break;
    case 'POST':
        createOrder($db, $input);
        break;
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}

function getOrders($db) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token) {
        sendResponse(['error' => 'Token required'], 401);
    }
    
    $user_id = verifyToken($token);
    if (!$user_id) {
        sendResponse(['error' => 'Invalid token'], 401);
    }
    
    $query = "SELECT o.*, GROUP_CONCAT(CONCAT(d.name, ' x', oi.quantity) SEPARATOR ', ') as items 
              FROM orders o 
              LEFT JOIN order_items oi ON o.id = oi.order_id 
              LEFT JOIN dishes d ON oi.dish_id = d.id 
              WHERE o.user_id = :user_id 
              GROUP BY o.id 
              ORDER BY o.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse([
        'success' => true,
        'orders' => $orders
    ]);
}

function createOrder($db, $input) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token) {
        sendResponse(['error' => 'Token required'], 401);
    }
    
    $user_id = verifyToken($token);
    if (!$user_id) {
        sendResponse(['error' => 'Invalid token'], 401);
    }
    
    if (!isset($input['address']) || empty($input['address'])) {
        sendResponse(['error' => 'Delivery address is required'], 400);
    }
    
    try {
        $db->beginTransaction();
        
        // Get cart items for this user
        $cart_query = "SELECT c.dish_id, c.quantity, d.price 
                       FROM cart c 
                       JOIN dishes d ON c.dish_id = d.id 
                       WHERE c.user_id = :user_id";
        $cart_stmt = $db->prepare($cart_query);
        $cart_stmt->bindParam(':user_id', $user_id);
        $cart_stmt->execute();
        
        $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cart_items)) {
            $db->rollBack();
            sendResponse(['error' => 'Cart is empty'], 400);
        }
        
        // Calculate total
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Create order
        $order_query = "INSERT INTO orders (user_id, total, address, status) VALUES (:user_id, :total, :address, 'pending')";
        $order_stmt = $db->prepare($order_query);
        $order_stmt->bindParam(':user_id', $user_id);
        $order_stmt->bindParam(':total', $total);
        $order_stmt->bindParam(':address', $input['address']);
        
        if (!$order_stmt->execute()) {
            $db->rollBack();
            sendResponse(['error' => 'Failed to create order'], 500);
        }
        
        $order_id = $db->lastInsertId();
        
        // Insert order items
        $item_query = "INSERT INTO order_items (order_id, dish_id, quantity, price) VALUES (:order_id, :dish_id, :quantity, :price)";
        $item_stmt = $db->prepare($item_query);
        
        foreach ($cart_items as $item) {
            $item_stmt->bindParam(':order_id', $order_id);
            $item_stmt->bindParam(':dish_id', $item['dish_id']);
            $item_stmt->bindParam(':quantity', $item['quantity']);
            $item_stmt->bindParam(':price', $item['price']);
            
            if (!$item_stmt->execute()) {
                $db->rollBack();
                sendResponse(['error' => 'Failed to create order items'], 500);
            }
        }
        
        // Clear cart
        $clear_cart_query = "DELETE FROM cart WHERE user_id = :user_id";
        $clear_cart_stmt = $db->prepare($clear_cart_query);
        $clear_cart_stmt->bindParam(':user_id', $user_id);
        
        if (!$clear_cart_stmt->execute()) {
            $db->rollBack();
            sendResponse(['error' => 'Failed to clear cart'], 500);
        }
        
        $db->commit();
        
        sendResponse([
            'success' => true,
            'message' => 'Order placed successfully',
            'order_id' => $order_id,
            'total' => $total
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        sendResponse(['error' => 'Failed to create order: ' . $e->getMessage()], 500);
    }
}
?>