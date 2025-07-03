<?php
require_once '../config/functions.php';
requireUserLogin();

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = sanitize($_POST['amount']);
    $txnId = sanitize($_POST['txn_id']);
    
    if (empty($amount) || empty($txnId)) {
        $error = 'Please fill all fields';
    } elseif (!isValidAmount($amount)) {
        $error = 'Please enter a valid amount';
    } elseif ($amount < 10) {
        $error = 'Minimum deposit amount is ₹10';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO deposits (user_id, txn_id, amount, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$user['id'], $txnId, $amount]);
            $success = 'Deposit request submitted successfully. It will be processed within 24 hours.';
        } catch (Exception $e) {
            $error = 'Failed to submit deposit request. Please try again.';
        }
    }
}

// Get user's deposit history
$stmt = $pdo->prepare("SELECT * FROM deposits WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user['id']]);
$deposits = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit - 91CLUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .upi-logo {
            width: 60px;
            height: 40px;
            background: linear-gradient(45deg, #FF6B35, #F7931E);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div id="app">
        <!-- Header -->
        <div class="gradient-bg text-white p-4">
            <div class="flex items-center">
                <a href="dashboard.php" class="mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div class="flex-1">
                    <h1 class="text-xl font-bold">Deposit</h1>
                    <p class="text-sm opacity-75">Add money to your wallet</p>
                </div>
                <div class="text-right">
                    <p class="text-sm opacity-75">Balance</p>
                    <p class="text-lg font-bold">₹<?php echo number_format($user['balance'], 2); ?></p>
                </div>
            </div>
        </div>

        <div class="p-4">
            <!-- Deposit Form -->
            <div class="bg-white rounded-3xl card-shadow p-6 mb-6">
                <h2 class="text-lg font-bold mb-4">Add Money</h2>
                
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
                        <label class="block text-gray-700 text-sm font-medium mb-2">Amount</label>
                        <input 
                            type="number" 
                            name="amount" 
                            v-model="amount"
                            class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter amount (Min: ₹10)"
                            min="10"
                            required
                        >
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <button type="button" v-for="quickAmount in quickAmounts" :key="quickAmount"
                                @click="amount = quickAmount"
                                class="px-3 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-purple-100">
                            ₹{{ quickAmount }}
                        </button>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-medium mb-2">UPI Transaction ID</label>
                        <input 
                            type="text" 
                            name="txn_id" 
                            v-model="txnId"
                            class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter UPI transaction ID"
                            required
                        >
                        <small class="text-gray-500">Enter the 12-digit UPI transaction ID after payment</small>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold py-3 rounded-2xl hover:shadow-lg transition duration-300"
                        :disabled="loading"
                    >
                        <span v-if="!loading">
                            <i class="fas fa-plus mr-2"></i>Submit Deposit Request
                        </span>
                        <span v-else>Processing...</span>
                    </button>
                </form>
            </div>

            <!-- UPI Payment Instructions -->
            <div class="bg-white rounded-3xl card-shadow p-6 mb-6">
                <h3 class="font-bold mb-4 flex items-center">
                    <div class="upi-logo mr-3">UPI</div>
                    Payment Instructions
                </h3>
                
                <div class="space-y-3">
                    <div class="flex items-start">
                        <div class="bg-purple-100 text-purple-600 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3 mt-1">1</div>
                        <div>
                            <p class="font-medium">Make UPI Payment</p>
                            <p class="text-sm text-gray-600">Use any UPI app (PhonePe, GPay, Paytm) to pay</p>
                            <div class="bg-gray-100 p-2 rounded-lg mt-2">
                                <p class="text-sm font-mono">UPI ID: merchant@paytm</p>
                                <p class="text-sm font-mono">Name: 91CLUB PAYMENTS</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-purple-100 text-purple-600 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3 mt-1">2</div>
                        <div>
                            <p class="font-medium">Copy Transaction ID</p>
                            <p class="text-sm text-gray-600">After successful payment, copy the 12-digit transaction ID</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-purple-100 text-purple-600 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3 mt-1">3</div>
                        <div>
                            <p class="font-medium">Submit Request</p>
                            <p class="text-sm text-gray-600">Enter amount and transaction ID above, then submit</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deposit History -->
            <div class="bg-white rounded-3xl card-shadow p-6">
                <h3 class="font-bold mb-4">Recent Deposits</h3>
                
                <?php if (empty($deposits)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-history text-4xl mb-3"></i>
                        <p>No deposits yet</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($deposits as $deposit): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <div>
                                    <p class="font-medium">₹<?php echo number_format($deposit['amount'], 2); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo date('d M Y, h:i A', strtotime($deposit['created_at'])); ?></p>
                                    <p class="text-xs text-gray-500">TXN: <?php echo $deposit['txn_id']; ?></p>
                                </div>
                                <div class="text-right">
                                    <?php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800'
                                    ];
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $statusColors[$deposit['status']]; ?>">
                                        <?php echo ucfirst($deposit['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;
        
        createApp({
            data() {
                return {
                    amount: '',
                    txnId: '',
                    loading: false,
                    quickAmounts: [100, 500, 1000, 2000]
                }
            },
            methods: {
                handleSubmit(event) {
                    this.loading = true;
                    
                    if (this.amount < 10) {
                        event.preventDefault();
                        this.loading = false;
                        Swal.fire('Error', 'Minimum deposit amount is ₹10', 'error');
                        return;
                    }
                    
                    if (!this.txnId || this.txnId.length < 10) {
                        event.preventDefault();
                        this.loading = false;
                        Swal.fire('Error', 'Please enter a valid transaction ID', 'error');
                        return;
                    }
                    
                    // Form will submit normally
                    setTimeout(() => {
                        this.loading = false;
                    }, 2000);
                }
            }
        }).mount('#app');
    </script>
</body>
</html>