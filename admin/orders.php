<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$orders = [];

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $data = json_encode([
        'order_id' => $_POST['order_id'],
        'status' => $_POST['status']
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '../api/admin.php?action=update_order_status');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $message = 'Order status updated successfully';
    } else {
        $message = 'Error updating order status';
    }
}

// Fetch orders
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '../api/admin.php?action=orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $orders = $data['orders'] ?? [];
    }
} catch (Exception $e) {
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Premium Bistro Admin</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glassmorphism-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .nav-item {
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-purple-900 to-purple-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 fixed h-full z-30">
            <div class="p-6">
                <div class="text-center mb-8">
                    <div class="bg-gradient-to-br from-purple-500 to-blue-500 rounded-full w-16 h-16 mx-auto flex items-center justify-center mb-3">
                        <i class="fas fa-crown text-2xl text-white"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white">Admin Panel</h2>
                    <p class="text-gray-300 text-sm">Premium Bistro</p>
                </div>
                
                <nav class="space-y-2">
                    <a href="dashboard.php" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:text-white rounded-lg">
                        <i class="fas fa-chart-bar mr-3"></i>
                        Dashboard
                    </a>
                    <a href="dishes.php" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:text-white rounded-lg">
                        <i class="fas fa-utensils mr-3"></i>
                        Manage Dishes
                    </a>
                    <a href="orders.php" class="nav-item flex items-center px-4 py-3 text-white rounded-lg bg-gradient-to-r from-purple-600 to-blue-600">
                        <i class="fas fa-shopping-bag mr-3"></i>
                        Orders
                    </a>
                    <a href="users.php" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:text-white rounded-lg">
                        <i class="fas fa-users mr-3"></i>
                        Users
                    </a>
                    <a href="settings.php" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:text-white rounded-lg">
                        <i class="fas fa-cog mr-3"></i>
                        Settings
                    </a>
                    <a href="../api/admin.php?action=logout" class="nav-item flex items-center px-4 py-3 text-red-300 hover:text-red-200 rounded-lg">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <!-- Header -->
            <div class="glassmorphism p-6 m-6 rounded-2xl">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-white font-montserrat">Orders Management</h1>
                        <p class="text-gray-300 mt-2">View and manage customer orders</p>
                    </div>
                    <div class="text-white">
                        <span class="text-2xl font-bold"><?php echo count($orders); ?></span>
                        <span class="text-gray-300 ml-2">Total Orders</span>
                    </div>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="mx-6 mb-6">
                    <div class="bg-green-500 bg-opacity-20 border border-green-500 rounded-lg p-4">
                        <p class="text-green-200"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Orders List -->
            <div class="mx-6 mb-6">
                <?php if (empty($orders)): ?>
                    <div class="glassmorphism-card rounded-2xl p-12 text-center text-white">
                        <i class="fas fa-shopping-bag text-6xl mb-6 opacity-50"></i>
                        <h2 class="text-2xl font-semibold mb-4">No orders yet</h2>
                        <p class="text-gray-300">Customer orders will appear here when they start placing orders</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($orders as $order): ?>
                            <div class="glassmorphism-card rounded-2xl p-6 text-white">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-semibold mb-2">Order #<?php echo $order['id']; ?></h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                                <p><strong>Date:</strong> <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                            </div>
                                            <div>
                                                <p><strong>Total:</strong> <span class="text-yellow-400 font-bold">$<?php echo number_format($order['total'], 2); ?></span></p>
                                                <p><strong>Status:</strong> 
                                                    <span class="px-2 py-1 rounded text-xs <?php 
                                                        echo $order['status'] === 'pending' ? 'bg-yellow-600' :
                                                            ($order['status'] === 'accepted' ? 'bg-green-600' :
                                                            ($order['status'] === 'delivered' ? 'bg-blue-600' : 'bg-red-600'));
                                                    ?>">
                                                        <?php echo strtoupper($order['status']); ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="update_status" value="1">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="status" value="accepted">
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Accept
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="update_status" value="1">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors" onclick="return confirm('Are you sure you want to reject this order?')">
                                                    <i class="fas fa-times mr-1"></i>
                                                    Reject
                                                </button>
                                            </form>
                                        <?php elseif ($order['status'] === 'accepted'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="update_status" value="1">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="status" value="delivered">
                                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                                    <i class="fas fa-truck mr-1"></i>
                                                    Mark Delivered
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="border-t border-gray-600 pt-4">
                                    <h4 class="font-semibold mb-2">Items Ordered:</h4>
                                    <p class="text-gray-300"><?php echo htmlspecialchars($order['items']); ?></p>
                                </div>
                                
                                <div class="border-t border-gray-600 pt-4 mt-4">
                                    <h4 class="font-semibold mb-2">Delivery Address:</h4>
                                    <p class="text-gray-300"><?php echo htmlspecialchars($order['address']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Stats -->
            <div class="mx-6 mb-6">
                <div class="glassmorphism rounded-2xl p-6">
                    <h2 class="text-2xl font-bold text-white mb-6">Order Statistics</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <?php
                        $pending = array_filter($orders, fn($o) => $o['status'] === 'pending');
                        $accepted = array_filter($orders, fn($o) => $o['status'] === 'accepted');
                        $delivered = array_filter($orders, fn($o) => $o['status'] === 'delivered');
                        $rejected = array_filter($orders, fn($o) => $o['status'] === 'rejected');
                        ?>
                        
                        <div class="bg-yellow-500 bg-opacity-20 border border-yellow-500 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-yellow-400"><?php echo count($pending); ?></div>
                            <div class="text-white text-sm">Pending</div>
                        </div>
                        
                        <div class="bg-green-500 bg-opacity-20 border border-green-500 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-400"><?php echo count($accepted); ?></div>
                            <div class="text-white text-sm">Accepted</div>
                        </div>
                        
                        <div class="bg-blue-500 bg-opacity-20 border border-blue-500 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-400"><?php echo count($delivered); ?></div>
                            <div class="text-white text-sm">Delivered</div>
                        </div>
                        
                        <div class="bg-red-500 bg-opacity-20 border border-red-500 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-red-400"><?php echo count($rejected); ?></div>
                            <div class="text-white text-sm">Rejected</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>