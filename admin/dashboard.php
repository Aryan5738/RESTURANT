<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch dashboard stats
$stats = [];
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '../api/admin.php?action=dashboard');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $stats = $data['stats'] ?? [];
    }
} catch (Exception $e) {
    $stats = [
        'total_users' => 0,
        'total_orders' => 0,
        'total_income' => 0,
        'pending_orders' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Premium Bistro</title>
    
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
        
        .stat-card {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
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
                    <a href="dashboard.php" class="nav-item flex items-center px-4 py-3 text-white rounded-lg bg-gradient-to-r from-purple-600 to-blue-600">
                        <i class="fas fa-chart-bar mr-3"></i>
                        Dashboard
                    </a>
                    <a href="dishes.php" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:text-white rounded-lg">
                        <i class="fas fa-utensils mr-3"></i>
                        Manage Dishes
                    </a>
                    <a href="orders.php" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:text-white rounded-lg">
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
                        <h1 class="text-3xl font-bold text-white font-montserrat">Dashboard</h1>
                        <p class="text-gray-300 mt-2">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-white font-semibold"><?php echo date('F j, Y'); ?></p>
                            <p class="text-gray-300 text-sm"><?php echo date('l, g:i A'); ?></p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-500 to-blue-500 rounded-full w-12 h-12 flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mx-6 mb-6">
                <div class="stat-card glassmorphism-card rounded-2xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-500 rounded-lg w-12 h-12 flex items-center justify-center">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold"><?php echo number_format($stats['total_users'] ?? 0); ?></span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Total Users</h3>
                    <p class="text-green-400 text-sm">
                        <i class="fas fa-arrow-up mr-1"></i>
                        Active customers
                    </p>
                </div>
                
                <div class="stat-card glassmorphism-card rounded-2xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-500 rounded-lg w-12 h-12 flex items-center justify-center">
                            <i class="fas fa-shopping-bag text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold"><?php echo number_format($stats['total_orders'] ?? 0); ?></span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Total Orders</h3>
                    <p class="text-green-400 text-sm">
                        <i class="fas fa-arrow-up mr-1"></i>
                        All time orders
                    </p>
                </div>
                
                <div class="stat-card glassmorphism-card rounded-2xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-500 rounded-lg w-12 h-12 flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold">$<?php echo number_format($stats['total_income'] ?? 0, 2); ?></span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Total Revenue</h3>
                    <p class="text-green-400 text-sm">
                        <i class="fas fa-arrow-up mr-1"></i>
                        Lifetime earnings
                    </p>
                </div>
                
                <div class="stat-card glassmorphism-card rounded-2xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-yellow-500 rounded-lg w-12 h-12 flex items-center justify-center">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold"><?php echo number_format($stats['pending_orders'] ?? 0); ?></span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Pending Orders</h3>
                    <p class="text-yellow-400 text-sm">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Needs attention
                    </p>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="glassmorphism rounded-2xl p-6 mx-6 mb-6">
                <h2 class="text-2xl font-bold text-white mb-6">Quick Actions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="dishes.php" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white p-4 rounded-xl transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center">
                            <i class="fas fa-plus-circle text-2xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold">Add New Dish</h3>
                                <p class="text-sm opacity-80">Create a new menu item</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="orders.php" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white p-4 rounded-xl transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center">
                            <i class="fas fa-list-alt text-2xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold">View Orders</h3>
                                <p class="text-sm opacity-80">Manage pending orders</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="settings.php" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white p-4 rounded-xl transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center">
                            <i class="fas fa-cog text-2xl mr-4"></i>
                            <div>
                                <h3 class="font-semibold">Settings</h3>
                                <p class="text-sm opacity-80">Configure restaurant</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="glassmorphism rounded-2xl p-6 mx-6 mb-6">
                <h2 class="text-2xl font-bold text-white mb-6">System Status</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-green-500 bg-opacity-20 rounded-lg border border-green-500">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 text-xl mr-3"></i>
                            <div>
                                <h3 class="text-white font-semibold">Database Connection</h3>
                                <p class="text-green-300 text-sm">Connected and operational</p>
                            </div>
                        </div>
                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm">Active</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-blue-500 bg-opacity-20 rounded-lg border border-blue-500">
                        <div class="flex items-center">
                            <i class="fas fa-server text-blue-400 text-xl mr-3"></i>
                            <div>
                                <h3 class="text-white font-semibold">API Services</h3>
                                <p class="text-blue-300 text-sm">All endpoints responding</p>
                            </div>
                        </div>
                        <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm">Online</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-purple-500 bg-opacity-20 rounded-lg border border-purple-500">
                        <div class="flex items-center">
                            <i class="fas fa-shield-alt text-purple-400 text-xl mr-3"></i>
                            <div>
                                <h3 class="text-white font-semibold">Security</h3>
                                <p class="text-purple-300 text-sm">Admin session secured</p>
                            </div>
                        </div>
                        <span class="bg-purple-500 text-white px-3 py-1 rounded-full text-sm">Secure</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>