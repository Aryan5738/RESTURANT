<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Make API call to login
        $data = json_encode(['username' => $username, 'password' => $password]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, '../api/admin.php?action=login');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Premium Bistro</title>
    
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
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md px-4">
        <div class="glassmorphism rounded-3xl p-8 text-white animate-float">
            <div class="text-center mb-8">
                <div class="bg-gradient-to-br from-purple-500 to-blue-500 rounded-full w-20 h-20 mx-auto flex items-center justify-center mb-4">
                    <i class="fas fa-crown text-3xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold font-montserrat">Admin Panel</h1>
                <p class="text-gray-300 mt-2">Premium Bistro Management</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-500 bg-opacity-20 border border-red-500 rounded-lg p-4 mb-6">
                    <p class="text-red-200 text-center"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input 
                            type="text" 
                            name="username" 
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            required
                            placeholder="Enter your username"
                            class="w-full pl-12 pr-4 py-3 rounded-lg bg-gray-700 bg-opacity-50 border border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none transition-colors"
                        />
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input 
                            type="password" 
                            name="password" 
                            required
                            placeholder="Enter your password"
                            class="w-full pl-12 pr-4 py-3 rounded-lg bg-gray-700 bg-opacity-50 border border-gray-600 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none transition-colors"
                        />
                    </div>
                </div>
                
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white py-3 rounded-lg font-semibold transition-all duration-300 transform hover:scale-105"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Login to Admin Panel
                </button>
            </form>
            
            <div class="text-center mt-6">
                <p class="text-sm text-gray-400">
                    Default login: <span class="text-blue-400">admin</span> / <span class="text-blue-400">admin123</span>
                </p>
            </div>
            
            <div class="text-center mt-6">
                <a href="../index.html" class="text-blue-400 hover:text-blue-300 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Website
                </a>
            </div>
        </div>
    </div>
</body>
</html>