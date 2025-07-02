<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$dishes = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $data = json_encode($_POST);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, '../api/admin.php?action=' . $_POST['action']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $message = $result['message'] ?? 'Action completed successfully';
        } else {
            $message = 'Error: Action failed';
        }
    }
}

// Fetch dishes
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '../api/admin.php?action=dishes');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $dishes = $data['dishes'] ?? [];
    }
} catch (Exception $e) {
    $dishes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Dishes - Premium Bistro Admin</title>
    
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
                    <a href="dishes.php" class="nav-item flex items-center px-4 py-3 text-white rounded-lg bg-gradient-to-r from-purple-600 to-blue-600">
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
                        <h1 class="text-3xl font-bold text-white font-montserrat">Manage Dishes</h1>
                        <p class="text-gray-300 mt-2">Add, edit, and manage your menu items</p>
                    </div>
                    <button onclick="openModal()" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg transition-all duration-300">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Dish
                    </button>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="mx-6 mb-6">
                    <div class="bg-green-500 bg-opacity-20 border border-green-500 rounded-lg p-4">
                        <p class="text-green-200"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Dishes Grid -->
            <div class="mx-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($dishes as $dish): ?>
                        <div class="glassmorphism-card rounded-2xl p-6 text-white">
                            <div class="bg-gradient-to-br from-purple-500 to-blue-500 h-48 rounded-xl mb-4 flex items-center justify-center">
                                <i class="fas fa-utensils text-4xl text-white opacity-50"></i>
                            </div>
                            
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($dish['name']); ?></h3>
                            <p class="text-gray-300 text-sm mb-4"><?php echo htmlspecialchars($dish['description']); ?></p>
                            
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-2xl font-bold text-yellow-400">$<?php echo number_format($dish['price'], 2); ?></span>
                                <span class="px-3 py-1 rounded-full text-xs <?php echo $dish['visible'] ? 'bg-green-500 text-white' : 'bg-red-500 text-white'; ?>">
                                    <?php echo $dish['visible'] ? 'Visible' : 'Hidden'; ?>
                                </span>
                            </div>
                            
                            <div class="flex gap-2">
                                <button onclick="editDish(<?php echo htmlspecialchars(json_encode($dish)); ?>)" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit
                                </button>
                                
                                <form method="POST" class="flex-1">
                                    <input type="hidden" name="action" value="toggle_dish_visibility">
                                    <input type="hidden" name="id" value="<?php echo $dish['id']; ?>">
                                    <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-lg transition-colors">
                                        <i class="fas fa-eye<?php echo $dish['visible'] ? '-slash' : ''; ?> mr-1"></i>
                                        <?php echo $dish['visible'] ? 'Hide' : 'Show'; ?>
                                    </button>
                                </form>
                                
                                <form method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this dish?')">
                                    <input type="hidden" name="action" value="delete_dish">
                                    <input type="hidden" name="dish_id" value="<?php echo $dish['id']; ?>">
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition-colors">
                                        <i class="fas fa-trash mr-1"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($dishes)): ?>
                    <div class="glassmorphism-card rounded-2xl p-12 text-center text-white">
                        <i class="fas fa-utensils text-6xl mb-6 opacity-50"></i>
                        <h2 class="text-2xl font-semibold mb-4">No dishes yet</h2>
                        <p class="text-gray-300 mb-6">Start by adding your first dish to the menu</p>
                        <button onclick="openModal()" class="bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg transition-all duration-300">
                            <i class="fas fa-plus mr-2"></i>
                            Add First Dish
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add/Edit Dish Modal -->
    <div id="dishModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="glassmorphism rounded-2xl p-8 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-6">
                <h2 id="modalTitle" class="text-2xl font-bold text-white">Add New Dish</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="dishForm" method="POST">
                <input type="hidden" id="dishAction" name="action" value="add_dish">
                <input type="hidden" id="dishId" name="id">
                
                <div class="mb-4">
                    <label class="block text-white text-sm font-medium mb-2">Dish Name</label>
                    <input type="text" id="dishName" name="name" required class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none">
                </div>
                
                <div class="mb-4">
                    <label class="block text-white text-sm font-medium mb-2">Description</label>
                    <textarea id="dishDescription" name="description" rows="3" class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-white text-sm font-medium mb-2">Price ($)</label>
                    <input type="number" id="dishPrice" name="price" step="0.01" min="0" required class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none">
                </div>
                
                <div class="mb-6">
                    <label class="block text-white text-sm font-medium mb-2">Image URL</label>
                    <input type="url" id="dishImage" name="image" class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none">
                </div>
                
                <div class="flex gap-4">
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 text-white py-3 rounded-lg transition-all duration-300">
                        <span id="submitText">Add Dish</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('dishModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Add New Dish';
            document.getElementById('dishAction').value = 'add_dish';
            document.getElementById('submitText').textContent = 'Add Dish';
            document.getElementById('dishForm').reset();
            document.getElementById('dishId').value = '';
        }
        
        function closeModal() {
            document.getElementById('dishModal').classList.add('hidden');
        }
        
        function editDish(dish) {
            document.getElementById('dishModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Edit Dish';
            document.getElementById('dishAction').value = 'update_dish';
            document.getElementById('submitText').textContent = 'Update Dish';
            
            document.getElementById('dishId').value = dish.id;
            document.getElementById('dishName').value = dish.name;
            document.getElementById('dishDescription').value = dish.description;
            document.getElementById('dishPrice').value = dish.price;
            document.getElementById('dishImage').value = dish.image || '';
        }
        
        // Close modal when clicking outside
        document.getElementById('dishModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>