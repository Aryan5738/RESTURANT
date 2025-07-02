<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        getCart($db);
        break;
    case 'POST':
        addToCart($db, $input);
        break;
    case 'PUT':
        updateCartItem($db, $input);
        break;
    case 'DELETE':
        if (isset($_GET['dish_id'])) {
            removeFromCart($db, $_GET['dish_id']);
        } else {
            clearCart($db);
        }
        break;
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}

function getCart($db) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token) {
        sendResponse(['error' => 'Token required'], 401);
    }
    
    $user_id = verifyToken($token);
    if (!$user_id) {
        sendResponse(['error' => 'Invalid token'], 401);
    }
    
    $query = "SELECT c.*, d.name, d.description, d.price, d.image 
              FROM cart c 
              JOIN dishes d ON c.dish_id = d.id 
              WHERE c.user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total
    $total = 0;
    foreach ($cart_items as &$item) {
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $total += $item['subtotal'];
        
        if ($item['image']) {
            $item['image'] = 'uploads/' . basename($item['image']);
        } else {
            $item['image'] = 'assets/dishes/default.jpg';
        }
    }
    
    sendResponse([
        'success' => true,
        'cart_items' => $cart_items,
        'total' => $total
    ]);
}

function addToCart($db, $input) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token) {
        sendResponse(['error' => 'Token required'], 401);
    }
    
    $user_id = verifyToken($token);
    if (!$user_id) {
        sendResponse(['error' => 'Invalid token'], 401);
    }
    
    if (!isset($input['dish_id'])) {
        sendResponse(['error' => 'Dish ID required'], 400);
    }
    
    $quantity = isset($input['quantity']) ? $input['quantity'] : 1;
    
    // Check if item already exists in cart
    $check_query = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND dish_id = :dish_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':user_id', $user_id);
    $check_stmt->bindParam(':dish_id', $input['dish_id']);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // Update existing item
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        $new_quantity = $existing['quantity'] + $quantity;
        
        $update_query = "UPDATE cart SET quantity = :quantity WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':quantity', $new_quantity);
        $update_stmt->bindParam(':id', $existing['id']);
        
        if ($update_stmt->execute()) {
            sendResponse(['success' => true, 'message' => 'Cart updated successfully']);
        } else {
            sendResponse(['error' => 'Failed to update cart'], 500);
        }
    } else {
        // Add new item
        $insert_query = "INSERT INTO cart (user_id, dish_id, quantity) VALUES (:user_id, :dish_id, :quantity)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bindParam(':user_id', $user_id);
        $insert_stmt->bindParam(':dish_id', $input['dish_id']);
        $insert_stmt->bindParam(':quantity', $quantity);
        
        if ($insert_stmt->execute()) {
            sendResponse(['success' => true, 'message' => 'Item added to cart successfully']);
        } else {
            sendResponse(['error' => 'Failed to add item to cart'], 500);
        }
    }
}

function updateCartItem($db, $input) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token) {
        sendResponse(['error' => 'Token required'], 401);
    }
    
    $user_id = verifyToken($token);
    if (!$user_id) {
        sendResponse(['error' => 'Invalid token'], 401);
    }
    
    if (!isset($input['dish_id']) || !isset($input['quantity'])) {
        sendResponse(['error' => 'Dish ID and quantity required'], 400);
    }
    
    if ($input['quantity'] <= 0) {
        // Remove item if quantity is 0 or negative
        removeFromCart($db, $input['dish_id'], $user_id);
        return;
    }
    
    $query = "UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND dish_id = :dish_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':quantity', $input['quantity']);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':dish_id', $input['dish_id']);
    
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => 'Cart updated successfully']);
    } else {
        sendResponse(['error' => 'Failed to update cart'], 500);
    }
}

function removeFromCart($db, $dish_id, $user_id = null) {
    if (!$user_id) {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
        
        if (!$token) {
            sendResponse(['error' => 'Token required'], 401);
        }
        
        $user_id = verifyToken($token);
        if (!$user_id) {
            sendResponse(['error' => 'Invalid token'], 401);
        }
    }
    
    $query = "DELETE FROM cart WHERE user_id = :user_id AND dish_id = :dish_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':dish_id', $dish_id);
    
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        sendResponse(['error' => 'Failed to remove item from cart'], 500);
    }
}

function clearCart($db) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token) {
        sendResponse(['error' => 'Token required'], 401);
    }
    
    $user_id = verifyToken($token);
    if (!$user_id) {
        sendResponse(['error' => 'Invalid token'], 401);
    }
    
    $query = "DELETE FROM cart WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => 'Cart cleared successfully']);
    } else {
        sendResponse(['error' => 'Failed to clear cart'], 500);
    }
}
?>