<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'register':
                    register($db, $input);
                    break;
                case 'login':
                    login($db, $input);
                    break;
                default:
                    sendResponse(['error' => 'Invalid action'], 400);
            }
        } else {
            sendResponse(['error' => 'Action required'], 400);
        }
        break;
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'profile') {
            getProfile($db);
        } else {
            sendResponse(['error' => 'Invalid action'], 400);
        }
        break;
    case 'PUT':
        if (isset($_GET['action']) && $_GET['action'] === 'profile') {
            updateProfile($db, $input);
        } else {
            sendResponse(['error' => 'Invalid action'], 400);
        }
        break;
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}

function register($db, $input) {
    if (!isset($input['name']) || !isset($input['email']) || !isset($input['password'])) {
        sendResponse(['error' => 'Name, email, and password are required'], 400);
    }

    // Check if email already exists
    $query = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $input['email']);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        sendResponse(['error' => 'Email already exists'], 400);
    }

    // Insert new user
    $query = "INSERT INTO users (name, email, password, phone, address) VALUES (:name, :email, :password, :phone, :address)";
    $stmt = $db->prepare($query);
    
    $hashed_password = hashPassword($input['password']);
    $phone = isset($input['phone']) ? $input['phone'] : null;
    $address = isset($input['address']) ? $input['address'] : null;
    
    $stmt->bindParam(':name', $input['name']);
    $stmt->bindParam(':email', $input['email']);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    
    if ($stmt->execute()) {
        $user_id = $db->lastInsertId();
        $token = generateToken($user_id);
        
        sendResponse([
            'success' => true,
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => [
                'id' => $user_id,
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $phone,
                'address' => $address
            ]
        ]);
    } else {
        sendResponse(['error' => 'Registration failed'], 500);
    }
}

function login($db, $input) {
    if (!isset($input['email']) || !isset($input['password'])) {
        sendResponse(['error' => 'Email and password are required'], 400);
    }

    $query = "SELECT id, name, email, password, phone, address FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $input['email']);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        sendResponse(['error' => 'Invalid credentials'], 401);
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (verifyPassword($input['password'], $user['password'])) {
        $token = generateToken($user['id']);
        
        sendResponse([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'address' => $user['address']
            ]
        ]);
    } else {
        sendResponse(['error' => 'Invalid credentials'], 401);
    }
}

function getProfile($db) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token) {
        sendResponse(['error' => 'Token required'], 401);
    }
    
    $user_id = verifyToken($token);
    if (!$user_id) {
        sendResponse(['error' => 'Invalid token'], 401);
    }
    
    $query = "SELECT id, name, email, phone, address FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        sendResponse(['error' => 'User not found'], 404);
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get user's orders
    $order_query = "SELECT o.*, GROUP_CONCAT(CONCAT(d.name, ' x', oi.quantity) SEPARATOR ', ') as items 
                    FROM orders o 
                    LEFT JOIN order_items oi ON o.id = oi.order_id 
                    LEFT JOIN dishes d ON oi.dish_id = d.id 
                    WHERE o.user_id = :user_id 
                    GROUP BY o.id 
                    ORDER BY o.created_at DESC";
    $order_stmt = $db->prepare($order_query);
    $order_stmt->bindParam(':user_id', $user_id);
    $order_stmt->execute();
    $orders = $order_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse([
        'user' => $user,
        'orders' => $orders
    ]);
}

function updateProfile($db, $input) {
    $headers = getallheaders();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token) {
        sendResponse(['error' => 'Token required'], 401);
    }
    
    $user_id = verifyToken($token);
    if (!$user_id) {
        sendResponse(['error' => 'Invalid token'], 401);
    }
    
    $query = "UPDATE users SET name = :name, phone = :phone, address = :address WHERE id = :user_id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':name', $input['name']);
    $stmt->bindParam(':phone', $input['phone']);
    $stmt->bindParam(':address', $input['address']);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        sendResponse(['error' => 'Update failed'], 500);
    }
}
?>