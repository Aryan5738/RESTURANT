<?php
require_once 'db.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate unique UID
function generateUID() {
    return '91CLUB' . rand(10000, 99999);
}

// Check if user is logged in
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Get current user data
function getCurrentUser() {
    global $pdo;
    if (!isUserLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Get current admin data
function getCurrentAdmin() {
    global $pdo;
    if (!isAdminLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

// Redirect if not logged in (user)
function requireUserLogin() {
    if (!isUserLoggedIn()) {
        header('Location: /user/login.php');
        exit;
    }
}

// Redirect if not logged in (admin)
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Get current active round
function getCurrentRound($gameType = 'color') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM rounds WHERE status = 'active' AND game_type = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$gameType]);
    return $stmt->fetch();
}

// Create new round
function createNewRound($gameType = 'color') {
    global $pdo;
    $periodNumber = date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO rounds (period_number, game_type) VALUES (?, ?)");
    $stmt->execute([$periodNumber, $gameType]);
    
    return $pdo->lastInsertId();
}

// Get time remaining for current round (60 seconds)
function getTimeRemaining() {
    $round = getCurrentRound();
    if (!$round) return 0;
    
    $startTime = strtotime($round['start_time']);
    $currentTime = time();
    $elapsed = $currentTime - $startTime;
    $remaining = 60 - ($elapsed % 60);
    
    return max(0, $remaining);
}

// Update user balance
function updateUserBalance($userId, $amount) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    return $stmt->execute([$amount, $userId]);
}

// Get user balance
function getUserBalance($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result ? $result['balance'] : 0;
}

// Calculate payout
function calculatePayout($color, $resultColor, $amount) {
    if ($color === $resultColor) {
        if ($color === 'violet') {
            return $amount * 5; // 400% profit (5x)
        } else {
            return $amount * 1.5; // 50% profit (1.5x)
        }
    }
    return 0;
}

// Get last results
function getLastResults($gameType = 'color', $limit = 5) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM rounds WHERE status = 'completed' AND game_type = ? ORDER BY id DESC LIMIT ?");
    $stmt->execute([$gameType, $limit]);
    return $stmt->fetchAll();
}

// Get latest notification
function getLatestNotification() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE status = 'active' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch();
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Validate phone number
function isValidPhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

// Validate amount
function isValidAmount($amount) {
    return is_numeric($amount) && $amount > 0;
}

// Generate random color result
function generateRandomResult($gameType = 'color') {
    switch ($gameType) {
        case 'color':
            $colors = ['red', 'green', 'violet'];
            return $colors[array_rand($colors)];
        case 'big_small':
            return rand(1, 6) > 3 ? 'big' : 'small';
        case 'dice':
            return rand(1, 6);
        case 'head_tail':
            return rand(0, 1) ? 'head' : 'tail';
        default:
            return 'red';
    }
}

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Get game display name
function getGameDisplayName($gameType) {
    $games = [
        'color' => 'Color Prediction',
        'big_small' => 'Big Small',
        'dice' => 'Dice',
        'mines' => 'Mines',
        'head_tail' => 'Head or Tail'
    ];
    return $games[$gameType] ?? 'Unknown Game';
}

// Send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Log activity
function logActivity($userId, $action, $details = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $action, $details]);
    } catch (Exception $e) {
        // Log error if needed
    }
}
?>