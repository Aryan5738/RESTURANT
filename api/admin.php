<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Admin authentication check
function checkAdminAuth() {
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        sendResponse(['error' => 'Admin authentication required'], 401);
    }
}

switch ($method) {
    case 'POST':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'login':
                    adminLogin($db, $input);
                    break;
                case 'logout':
                    adminLogout();
                    break;
                case 'add_dish':
                    checkAdminAuth();
                    addDish($db, $input);
                    break;
                case 'update_order_status':
                    checkAdminAuth();
                    updateOrderStatus($db, $input);
                    break;
                case 'update_settings':
                    checkAdminAuth();
                    updateSettings($db, $input);
                    break;
                default:
                    sendResponse(['error' => 'Invalid action'], 400);
            }
        } else {
            sendResponse(['error' => 'Action required'], 400);
        }
        break;
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'dashboard':
                    checkAdminAuth();
                    getDashboardStats($db);
                    break;
                case 'dishes':
                    checkAdminAuth();
                    getAllDishes($db);
                    break;
                case 'orders':
                    checkAdminAuth();
                    getAllOrders($db);
                    break;
                case 'users':
                    checkAdminAuth();
                    getAllUsers($db);
                    break;
                case 'settings':
                    checkAdminAuth();
                    getSettings($db);
                    break;
                default:
                    sendResponse(['error' => 'Invalid action'], 400);
            }
        } else {
            sendResponse(['error' => 'Action required'], 400);
        }
        break;
    case 'PUT':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'update_dish':
                    checkAdminAuth();
                    updateDish($db, $input);
                    break;
                case 'toggle_dish_visibility':
                    checkAdminAuth();
                    toggleDishVisibility($db, $input);
                    break;
                default:
                    sendResponse(['error' => 'Invalid action'], 400);
            }
        } else {
            sendResponse(['error' => 'Action required'], 400);
        }
        break;
    case 'DELETE':
        if (isset($_GET['action']) && $_GET['action'] === 'delete_dish') {
            checkAdminAuth();
            deleteDish($db, $_GET['dish_id']);
        } else {
            sendResponse(['error' => 'Invalid action'], 400);
        }
        break;
    default:
        sendResponse(['error' => 'Method not allowed'], 405);
}

function adminLogin($db, $input) {
    if (!isset($input['username']) || !isset($input['password'])) {
        sendResponse(['error' => 'Username and password are required'], 400);
    }

    $query = "SELECT id, username, password FROM admin WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $input['username']);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        sendResponse(['error' => 'Invalid credentials'], 401);
    }
    
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (verifyPassword($input['password'], $admin['password'])) {
        session_start();
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        
        sendResponse([
            'success' => true,
            'message' => 'Admin login successful'
        ]);
    } else {
        sendResponse(['error' => 'Invalid credentials'], 401);
    }
}

function adminLogout() {
    session_start();
    session_destroy();
    sendResponse(['success' => true, 'message' => 'Logged out successfully']);
}

function getDashboardStats($db) {
    // Get total users
    $user_query = "SELECT COUNT(*) as total_users FROM users";
    $user_stmt = $db->prepare($user_query);
    $user_stmt->execute();
    $total_users = $user_stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    
    // Get total orders
    $order_query = "SELECT COUNT(*) as total_orders FROM orders";
    $order_stmt = $db->prepare($order_query);
    $order_stmt->execute();
    $total_orders = $order_stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
    
    // Get total income
    $income_query = "SELECT SUM(total) as total_income FROM orders WHERE status != 'rejected'";
    $income_stmt = $db->prepare($income_query);
    $income_stmt->execute();
    $total_income = $income_stmt->fetch(PDO::FETCH_ASSOC)['total_income'] ?? 0;
    
    // Get pending orders
    $pending_query = "SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'";
    $pending_stmt = $db->prepare($pending_query);
    $pending_stmt->execute();
    $pending_orders = $pending_stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];
    
    sendResponse([
        'success' => true,
        'stats' => [
            'total_users' => $total_users,
            'total_orders' => $total_orders,
            'total_income' => $total_income,
            'pending_orders' => $pending_orders
        ]
    ]);
}

function getAllDishes($db) {
    $query = "SELECT * FROM dishes ORDER BY id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse([
        'success' => true,
        'dishes' => $dishes
    ]);
}

function getAllOrders($db) {
    $query = "SELECT o.*, u.name as customer_name, u.email as customer_email,
                     GROUP_CONCAT(CONCAT(d.name, ' x', oi.quantity) SEPARATOR ', ') as items 
              FROM orders o 
              JOIN users u ON o.user_id = u.id
              LEFT JOIN order_items oi ON o.id = oi.order_id 
              LEFT JOIN dishes d ON oi.dish_id = d.id 
              GROUP BY o.id 
              ORDER BY o.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse([
        'success' => true,
        'orders' => $orders
    ]);
}

function getAllUsers($db) {
    $query = "SELECT id, name, email, phone, created_at FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    sendResponse([
        'success' => true,
        'users' => $users
    ]);
}

function getSettings($db) {
    $query = "SELECT * FROM site_settings WHERE id = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    sendResponse([
        'success' => true,
        'settings' => $settings
    ]);
}

function addDish($db, $input) {
    if (!isset($input['name']) || !isset($input['price'])) {
        sendResponse(['error' => 'Name and price are required'], 400);
    }
    
    $query = "INSERT INTO dishes (name, description, price, image, visible) VALUES (:name, :description, :price, :image, :visible)";
    $stmt = $db->prepare($query);
    
    $description = isset($input['description']) ? $input['description'] : '';
    $image = isset($input['image']) ? $input['image'] : '';
    $visible = isset($input['visible']) ? $input['visible'] : 1;
    
    $stmt->bindParam(':name', $input['name']);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $input['price']);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':visible', $visible);
    
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => 'Dish added successfully']);
    } else {
        sendResponse(['error' => 'Failed to add dish'], 500);
    }
}

function updateDish($db, $input) {
    if (!isset($input['id'])) {
        sendResponse(['error' => 'Dish ID is required'], 400);
    }
    
    $query = "UPDATE dishes SET name = :name, description = :description, price = :price, image = :image WHERE id = :id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':name', $input['name']);
    $stmt->bindParam(':description', $input['description']);
    $stmt->bindParam(':price', $input['price']);
    $stmt->bindParam(':image', $input['image']);
    $stmt->bindParam(':id', $input['id']);
    
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => 'Dish updated successfully']);
    } else {
        sendResponse(['error' => 'Failed to update dish'], 500);
    }
}

function toggleDishVisibility($db, $input) {
    if (!isset($input['id'])) {
        sendResponse(['error' => 'Dish ID is required'], 400);
    }
    
    $query = "UPDATE dishes SET visible = NOT visible WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $input['id']);
    
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => 'Dish visibility toggled successfully']);
    } else {
        sendResponse(['error' => 'Failed to toggle dish visibility'], 500);
    }
}

function deleteDish($db, $dish_id) {
    if (!$dish_id) {
        sendResponse(['error' => 'Dish ID is required'], 400);
    }
    
    $query = "DELETE FROM dishes WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $dish_id);
    
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => 'Dish deleted successfully']);
    } else {
        sendResponse(['error' => 'Failed to delete dish'], 500);
    }
}

function updateOrderStatus($db, $input) {
    if (!isset($input['order_id']) || !isset($input['status'])) {
        sendResponse(['error' => 'Order ID and status are required'], 400);
    }
    
    $query = "UPDATE orders SET status = :status WHERE id = :order_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $input['status']);
    $stmt->bindParam(':order_id', $input['order_id']);
    
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => 'Order status updated successfully']);
    } else {
        sendResponse(['error' => 'Failed to update order status'], 500);
    }
}

function updateSettings($db, $input) {
    $query = "UPDATE site_settings SET restaurant_name = :restaurant_name, logo_url = :logo_url, theme_color = :theme_color WHERE id = 1";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':restaurant_name', $input['restaurant_name']);
    $stmt->bindParam(':logo_url', $input['logo_url']);
    $stmt->bindParam(':theme_color', $input['theme_color']);
    
    if ($stmt->execute()) {
        sendResponse(['success' => true, 'message' => 'Settings updated successfully']);
    } else {
        sendResponse(['error' => 'Failed to update settings'], 500);
    }
}
?>