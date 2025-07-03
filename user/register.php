<?php
require_once '../config/functions.php';

if (isUserLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($name) || empty($phone) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill all fields';
    } elseif (!isValidPhone($phone)) {
        $error = 'Please enter a valid 10-digit phone number';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Check if phone already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        
        if ($stmt->fetch()) {
            $error = 'Phone number already registered';
        } else {
            // Generate unique UID
            do {
                $uid = generateUID();
                $stmt = $pdo->prepare("SELECT id FROM users WHERE uid = ?");
                $stmt->execute([$uid]);
            } while ($stmt->fetch());
            
            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (uid, name, phone, password, balance) VALUES (?, ?, ?, ?, 0.00)");
            
            if ($stmt->execute([$uid, $name, $phone, $hashedPassword])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $success = "Registration successful! Your UID is: $uid";
                header('refresh:2;url=dashboard.php');
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Color Prediction Game</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-6 text-center">
                <h1 class="text-2xl font-bold text-white mb-2">91CLUB</h1>
                <p class="text-purple-100">Create Your Account</p>
            </div>
            
            <!-- Form -->
            <div class="p-6">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger mb-4 rounded-2xl border-0" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success mb-4 rounded-2xl border-0" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" @submit="handleSubmit">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Full Name</label>
                        <input 
                            type="text" 
                            name="name" 
                            v-model="name"
                            class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter your full name"
                            required
                        >
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Phone Number</label>
                        <input 
                            type="tel" 
                            name="phone" 
                            v-model="phone"
                            class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter your phone number"
                            maxlength="10"
                            required
                        >
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                        <input 
                            type="password" 
                            name="password" 
                            v-model="password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter your password"
                            minlength="6"
                            required
                        >
                        <small class="text-gray-500">Minimum 6 characters</small>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-medium mb-2">Confirm Password</label>
                        <input 
                            type="password" 
                            name="confirm_password" 
                            v-model="confirmPassword"
                            class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Confirm your password"
                            required
                        >
                        <small v-if="password && confirmPassword && password !== confirmPassword" class="text-red-500">
                            Passwords do not match
                        </small>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold py-3 rounded-2xl hover:shadow-lg transition duration-300 transform hover:scale-105"
                        :disabled="loading || (password && confirmPassword && password !== confirmPassword)"
                    >
                        <span v-if="!loading">Create Account</span>
                        <span v-else>Creating Account...</span>
                    </button>
                </form>
                
                <div class="text-center mt-6">
                    <p class="text-gray-600">Already have an account? 
                        <a href="login.php" class="text-purple-500 font-medium hover:text-purple-600">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-white text-sm opacity-75">© 2024 91CLUB. All rights reserved.</p>
        </div>
    </div>

    <script>
        const { createApp } = Vue;
        
        createApp({
            data() {
                return {
                    name: '',
                    phone: '',
                    password: '',
                    confirmPassword: '',
                    loading: false
                }
            },
            methods: {
                handleSubmit(event) {
                    this.loading = true;
                    
                    // Validate phone number
                    if (this.phone.length !== 10) {
                        event.preventDefault();
                        this.loading = false;
                        Swal.fire('Error', 'Please enter a valid 10-digit phone number', 'error');
                        return;
                    }
                    
                    // Validate password
                    if (this.password.length < 6) {
                        event.preventDefault();
                        this.loading = false;
                        Swal.fire('Error', 'Password must be at least 6 characters long', 'error');
                        return;
                    }
                    
                    // Validate password confirmation
                    if (this.password !== this.confirmPassword) {
                        event.preventDefault();
                        this.loading = false;
                        Swal.fire('Error', 'Passwords do not match', 'error');
                        return;
                    }
                    
                    // Form will submit normally
                    setTimeout(() => {
                        this.loading = false;
                    }, 2000);
                }
            },
            mounted() {
                // Auto-focus on name field
                document.querySelector('input[name="name"]').focus();
            }
        }).mount('#app');
    </script>
</body>
</html>