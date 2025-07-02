<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$users = [];

// Fetch users
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '../api/admin.php?action=users');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $users = $data['users'] ?? [];
    }
} catch (Exception $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Premium Bistro Admin</title>
    
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
                    <a href="orders.php" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:text-white rounded-lg">
                        <i class="fas fa-shopping-bag mr-3"></i>
                        Orders
                    </a>
                    <a href="users.php" class="nav-item flex items-center px-4 py-3 text-white rounded-lg bg-gradient-to-r from-purple-600 to-blue-600">
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
                        <h1 class="text-3xl font-bold text-white font-montserrat">Users Management</h1>
                        <p class="text-gray-300 mt-2">View and manage customer accounts</p>
                    </div>
                    <div class="text-white">
                        <span class="text-2xl font-bold"><?php echo count($users); ?></span>
                        <span class="text-gray-300 ml-2">Total Users</span>
                    </div>
                </div>
            </div>
            
            <!-- Users List -->
            <div class="mx-6 mb-6">
                <?php if (empty($users)): ?>
                    <div class="glassmorphism-card rounded-2xl p-12 text-center text-white">
                        <i class="fas fa-users text-6xl mb-6 opacity-50"></i>
                        <h2 class="text-2xl font-semibold mb-4">No users yet</h2>
                        <p class="text-gray-300">Customer registrations will appear here</p>
                    </div>
                <?php else: ?>
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="glassmorphism-card rounded-2xl p-6 text-white text-center">
                            <div class="bg-blue-500 rounded-lg w-12 h-12 mx-auto flex items-center justify-center mb-4">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div class="text-2xl font-bold mb-2"><?php echo count($users); ?></div>
                            <div class="text-gray-300">Total Customers</div>
                        </div>
                        
                        <div class="glassmorphism-card rounded-2xl p-6 text-white text-center">
                            <div class="bg-green-500 rounded-lg w-12 h-12 mx-auto flex items-center justify-center mb-4">
                                <i class="fas fa-user-check text-xl"></i>
                            </div>
                            <div class="text-2xl font-bold mb-2"><?php echo count(array_filter($users, fn($u) => !empty($u['phone']))); ?></div>
                            <div class="text-gray-300">With Phone Numbers</div>
                        </div>
                        
                        <div class="glassmorphism-card rounded-2xl p-6 text-white text-center">
                            <div class="bg-purple-500 rounded-lg w-12 h-12 mx-auto flex items-center justify-center mb-4">
                                <i class="fas fa-calendar text-xl"></i>
                            </div>
                            <div class="text-2xl font-bold mb-2"><?php echo count(array_filter($users, fn($u) => strtotime($u['created_at']) > strtotime('-30 days'))); ?></div>
                            <div class="text-gray-300">New This Month</div>
                        </div>
                    </div>
                    
                    <!-- Users Table -->
                    <div class="glassmorphism rounded-2xl overflow-hidden">
                        <div class="p-6 border-b border-gray-600">
                            <h2 class="text-2xl font-bold text-white">Customer List</h2>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-white">
                                <thead class="bg-gray-700 bg-opacity-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-300 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-300 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-300 uppercase tracking-wider">Registered</th>
                                        <th class="px-6 py-4 text-left text-sm font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-600">
                                    <?php foreach ($users as $user): ?>
                                        <tr class="hover:bg-gray-700 hover:bg-opacity-30 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="bg-gradient-to-br from-purple-500 to-blue-500 rounded-full w-10 h-10 flex items-center justify-center mr-4">
                                                        <i class="fas fa-user text-white text-sm"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-white"><?php echo htmlspecialchars($user['name']); ?></div>
                                                        <div class="text-sm text-gray-400"><?php echo htmlspecialchars($user['email']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-white">
                                                    <?php if (!empty($user['phone'])): ?>
                                                        <div><i class="fas fa-phone mr-2 text-green-400"></i><?php echo htmlspecialchars($user['phone']); ?></div>
                                                    <?php else: ?>
                                                        <span class="text-gray-500">No phone provided</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-white"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                                                <div class="text-sm text-gray-400"><?php echo date('g:i A', strtotime($user['created_at'])); ?></div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php if (strtotime($user['created_at']) > strtotime('-7 days')): ?>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-green-500 text-white">New</span>
                                                <?php elseif (strtotime($user['created_at']) > strtotime('-30 days')): ?>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-500 text-white">Recent</span>
                                                <?php else: ?>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-500 text-white">Regular</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>