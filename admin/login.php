<?php
require_once '../config/functions.php';

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill all fields';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
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
    <title>Admin Login - 91CLUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 100%);
        }
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div id="app" class="w-full max-w-md">
        <div class="bg-white rounded-3xl card-shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-center">
                <i class="fas fa-shield-alt text-4xl text-white mb-3"></i>
                <h1 class="text-2xl font-bold text-white mb-2">Admin Panel</h1>
                <p class="text-blue-100">91CLUB Management</p>
            </div>
            
            <!-- Form -->
            <div class="p-6">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger mb-4 rounded-2xl border-0" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" @submit="handleSubmit">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">
                            <i class="fas fa-user mr-2"></i>Username
                        </label>
                        <input 
                            type="text" 
                            name="username" 
                            v-model="username"
                            class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Enter admin username"
                            required
                        >
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-medium mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            v-model="password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Enter admin password"
                            required
                        >
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold py-3 rounded-2xl hover:shadow-lg transition duration-300 transform hover:scale-105"
                        :disabled="loading"
                    >
                        <span v-if="!loading">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login to Admin Panel
                        </span>
                        <span v-else>
                            <i class="fas fa-spinner fa-spin mr-2"></i>Authenticating...
                        </span>
                    </button>
                </form>
                
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Authorized access only
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-white text-sm opacity-75">
                <i class="fas fa-copyright mr-1"></i>
                2024 91CLUB Admin Panel. Secure & Protected.
            </p>
        </div>
    </div>

    <script>
        const { createApp } = Vue;
        
        createApp({
            data() {
                return {
                    username: '',
                    password: '',
                    loading: false
                }
            },
            methods: {
                handleSubmit(event) {
                    this.loading = true;
                    
                    // Basic validation
                    if (!this.username || !this.password) {
                        event.preventDefault();
                        this.loading = false;
                        Swal.fire('Error', 'Please fill all fields', 'error');
                        return;
                    }
                    
                    // Form will submit normally
                    setTimeout(() => {
                        this.loading = false;
                    }, 2000);
                }
            },
            mounted() {
                // Auto-focus on username field
                document.querySelector('input[name="username"]').focus();
            }
        }).mount('#app');
    </script>
</body>
</html>