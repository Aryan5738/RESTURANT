<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$settings = [];

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $data = json_encode([
        'restaurant_name' => $_POST['restaurant_name'],
        'logo_url' => $_POST['logo_url'],
        'theme_color' => $_POST['theme_color']
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '../api/admin.php?action=update_settings');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $message = 'Settings updated successfully';
    } else {
        $message = 'Error updating settings';
    }
}

// Fetch settings
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '../api/admin.php?action=settings');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $settings = $data['settings'] ?? [];
    }
} catch (Exception $e) {
    $settings = [
        'restaurant_name' => 'Premium Bistro',
        'logo_url' => 'assets/logo.png',
        'theme_color' => '#6366f1'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Premium Bistro Admin</title>
    
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
                    <a href="users.php" class="nav-item flex items-center px-4 py-3 text-gray-300 hover:text-white rounded-lg">
                        <i class="fas fa-users mr-3"></i>
                        Users
                    </a>
                    <a href="settings.php" class="nav-item flex items-center px-4 py-3 text-white rounded-lg bg-gradient-to-r from-purple-600 to-blue-600">
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
                        <h1 class="text-3xl font-bold text-white font-montserrat">Restaurant Settings</h1>
                        <p class="text-gray-300 mt-2">Configure your restaurant information and appearance</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="../index.html" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            View Website
                        </a>
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
            
            <div class="mx-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Restaurant Information -->
                    <div class="glassmorphism-card rounded-2xl p-6">
                        <h2 class="text-2xl font-bold text-white mb-6">Restaurant Information</h2>
                        
                        <form method="POST">
                            <input type="hidden" name="update_settings" value="1">
                            
                            <div class="mb-6">
                                <label class="block text-white text-sm font-medium mb-2">Restaurant Name</label>
                                <input 
                                    type="text" 
                                    name="restaurant_name" 
                                    value="<?php echo htmlspecialchars($settings['restaurant_name'] ?? ''); ?>"
                                    required
                                    class="w-full px-4 py-3 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none"
                                    placeholder="Enter restaurant name"
                                >
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-white text-sm font-medium mb-2">Logo URL</label>
                                <input 
                                    type="url" 
                                    name="logo_url" 
                                    value="<?php echo htmlspecialchars($settings['logo_url'] ?? ''); ?>"
                                    class="w-full px-4 py-3 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none"
                                    placeholder="https://example.com/logo.png"
                                >
                                <p class="text-gray-400 text-sm mt-2">URL to your restaurant logo image</p>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-white text-sm font-medium mb-2">Theme Color</label>
                                <div class="flex items-center gap-4">
                                    <input 
                                        type="color" 
                                        name="theme_color" 
                                        value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#6366f1'); ?>"
                                        class="w-16 h-12 rounded-lg border border-gray-600 bg-gray-700"
                                    >
                                    <input 
                                        type="text" 
                                        value="<?php echo htmlspecialchars($settings['theme_color'] ?? '#6366f1'); ?>"
                                        readonly
                                        class="flex-1 px-4 py-3 rounded-lg bg-gray-700 border border-gray-600 text-white"
                                    >
                                </div>
                                <p class="text-gray-400 text-sm mt-2">Primary color for your restaurant branding</p>
                            </div>
                            
                            <button 
                                type="submit"
                                class="w-full bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white py-3 rounded-lg font-semibold transition-all duration-300 transform hover:scale-105"
                            >
                                <i class="fas fa-save mr-2"></i>
                                Save Settings
                            </button>
                        </form>
                    </div>
                    
                    <!-- Preview -->
                    <div class="glassmorphism-card rounded-2xl p-6">
                        <h2 class="text-2xl font-bold text-white mb-6">Preview</h2>
                        
                        <div class="bg-gray-800 rounded-xl p-6 mb-6">
                            <div class="text-center">
                                <div class="bg-gradient-to-br from-purple-500 to-blue-500 rounded-full w-16 h-16 mx-auto flex items-center justify-center mb-4">
                                    <i class="fas fa-utensils text-2xl text-white"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white mb-2"><?php echo htmlspecialchars($settings['restaurant_name'] ?? 'Premium Bistro'); ?></h3>
                                <p class="text-gray-300 text-sm">Fine Dining Experience</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-gray-800 rounded-lg p-4">
                                <h4 class="text-white font-semibold mb-2">Current Settings:</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-300">Name:</span>
                                        <span class="text-white"><?php echo htmlspecialchars($settings['restaurant_name'] ?? 'Premium Bistro'); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-300">Theme:</span>
                                        <div class="flex items-center gap-2">
                                            <div 
                                                class="w-4 h-4 rounded border border-gray-600" 
                                                style="background-color: <?php echo htmlspecialchars($settings['theme_color'] ?? '#6366f1'); ?>"
                                            ></div>
                                            <span class="text-white"><?php echo htmlspecialchars($settings['theme_color'] ?? '#6366f1'); ?></span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-300">Logo:</span>
                                        <span class="text-white"><?php echo !empty($settings['logo_url']) ? 'Set' : 'Default'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- System Information -->
                <div class="glassmorphism-card rounded-2xl p-6 mt-8">
                    <h2 class="text-2xl font-bold text-white mb-6">System Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-300">PHP Version</span>
                                <i class="fas fa-code text-blue-400"></i>
                            </div>
                            <div class="text-white font-semibold"><?php echo phpversion(); ?></div>
                        </div>
                        
                        <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-300">Server Time</span>
                                <i class="fas fa-clock text-green-400"></i>
                            </div>
                            <div class="text-white font-semibold"><?php echo date('M j, Y g:i A'); ?></div>
                        </div>
                        
                        <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-300">Admin Session</span>
                                <i class="fas fa-shield-alt text-purple-400"></i>
                            </div>
                            <div class="text-white font-semibold">Active</div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="glassmorphism-card rounded-2xl p-6 mt-8">
                    <h2 class="text-2xl font-bold text-white mb-6">Quick Actions</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="dishes.php" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white p-4 rounded-xl transition-all duration-300 transform hover:scale-105 text-center">
                            <i class="fas fa-utensils text-2xl mb-2"></i>
                            <div class="font-semibold">Manage Menu</div>
                        </a>
                        
                        <a href="orders.php" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white p-4 rounded-xl transition-all duration-300 transform hover:scale-105 text-center">
                            <i class="fas fa-shopping-bag text-2xl mb-2"></i>
                            <div class="font-semibold">View Orders</div>
                        </a>
                        
                        <a href="users.php" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white p-4 rounded-xl transition-all duration-300 transform hover:scale-105 text-center">
                            <i class="fas fa-users text-2xl mb-2"></i>
                            <div class="font-semibold">View Customers</div>
                        </a>
                        
                        <a href="dashboard.php" class="bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 text-white p-4 rounded-xl transition-all duration-300 transform hover:scale-105 text-center">
                            <i class="fas fa-chart-bar text-2xl mb-2"></i>
                            <div class="font-semibold">Dashboard</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Update the text input when color picker changes
        document.querySelector('input[type="color"]').addEventListener('change', function(e) {
            document.querySelector('input[type="text"][readonly]').value = e.target.value;
        });
    </script>
</body>
</html>