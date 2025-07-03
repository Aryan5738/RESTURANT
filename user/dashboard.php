<?php
require_once '../config/functions.php';
requireUserLogin();

$user = getCurrentUser();
$currentRound = getCurrentRound();
$lastResults = getLastResults('color', 5);
$notification = getLatestNotification();

if (!$currentRound) {
    $currentRound = ['id' => createNewRound(), 'period_number' => date('Ymd') . '001'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - 91CLUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .game-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .color-btn {
            transition: all 0.3s ease;
            transform: scale(1);
        }
        .color-btn:hover {
            transform: scale(1.05);
        }
        .color-btn:active {
            transform: scale(0.95);
        }
        .red-btn {
            background: linear-gradient(145deg, #ef4444 0%, #dc2626 100%);
        }
        .green-btn {
            background: linear-gradient(145deg, #10b981 0%, #059669 100%);
        }
        .violet-btn {
            background: linear-gradient(145deg, #8b5cf6 0%, #7c3aed 100%);
        }
        .result-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-block;
            margin: 2px;
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 20px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .nav-item {
            flex: 1;
            text-align: center;
            padding: 10px;
            border: none;
            background: none;
            color: #6b7280;
            transition: all 0.3s ease;
        }
        .nav-item.active {
            color: #8b5cf6;
        }
        .notification-bar {
            background: linear-gradient(45deg, #fbbf24, #f59e0b);
        }
        .timer-circle {
            background: conic-gradient(#8b5cf6 var(--progress, 0%), #e5e7eb var(--progress, 0%));
        }
        .balance-card {
            background: linear-gradient(145deg, #1f2937 0%, #374151 100%);
        }
    </style>
</head>
<body class="bg-gray-50 pb-20">
    <div id="app">
        <!-- Notification Bar -->
        <div v-if="notification" class="notification-bar text-white px-4 py-2 text-center text-sm">
            <marquee>{{ notification.message }}</marquee>
        </div>

        <!-- Header -->
        <div class="gradient-bg text-white p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold">91CLUB</h1>
                    <p class="text-sm opacity-75">UID: <?php echo $user['uid']; ?></p>
                </div>
                <div class="text-right">
                    <p class="text-sm opacity-75">Balance</p>
                    <p class="text-lg font-bold">₹{{ userBalance }}</p>
                </div>
            </div>
        </div>

        <!-- Game Section -->
        <div class="p-4">
            <!-- Current Round -->
            <div class="game-card rounded-3xl p-6 mb-4">
                <div class="text-center mb-4">
                    <h2 class="text-lg font-bold text-gray-800 mb-2">Color Prediction</h2>
                    <div class="flex justify-center items-center mb-2">
                        <div class="timer-circle w-16 h-16 rounded-full flex items-center justify-center" 
                             :style="{'--progress': timerProgress + '%'}">
                            <span class="text-lg font-bold text-gray-800">{{ timeRemaining }}s</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">Period: {{ currentRound.period_number }}</p>
                </div>

                <!-- Bet Amount Selection -->
                <div class="mb-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Select Amount:</p>
                    <div class="grid grid-cols-4 gap-2">
                        <button v-for="amount in betAmounts" :key="amount"
                                @click="selectedAmount = amount"
                                :class="['px-3 py-2 rounded-xl text-sm font-medium transition-all', 
                                        selectedAmount === amount ? 'bg-purple-500 text-white' : 'bg-gray-200 text-gray-700']">
                            ₹{{ amount }}
                        </button>
                    </div>
                    <input v-model="selectedAmount" type="number" placeholder="Custom amount" 
                           class="w-full mt-2 px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <!-- Color Buttons -->
                <div class="grid grid-cols-3 gap-3">
                    <button @click="placeBet('red')" :disabled="timeRemaining <= 0 || loading"
                            class="color-btn red-btn text-white py-4 rounded-2xl font-bold text-lg">
                        <i class="fas fa-circle mr-2"></i>RED
                    </button>
                    <button @click="placeBet('violet')" :disabled="timeRemaining <= 0 || loading"
                            class="color-btn violet-btn text-white py-4 rounded-2xl font-bold text-lg">
                        <i class="fas fa-circle mr-2"></i>VIOLET
                    </button>
                    <button @click="placeBet('green')" :disabled="timeRemaining <= 0 || loading"
                            class="color-btn green-btn text-white py-4 rounded-2xl font-bold text-lg">
                        <i class="fas fa-circle mr-2"></i>GREEN
                    </button>
                </div>

                <!-- Payout Info -->
                <div class="mt-4 bg-gray-100 rounded-xl p-3">
                    <div class="flex justify-between text-xs text-gray-600">
                        <span>RED/GREEN: 1.5x</span>
                        <span>VIOLET: 5x</span>
                    </div>
                </div>
            </div>

            <!-- Last Results -->
            <div class="bg-white rounded-2xl p-4 mb-4">
                <h3 class="font-bold mb-3">Last Results</h3>
                <div class="flex justify-center space-x-1">
                    <div v-for="result in lastResults" :key="result.id"
                         :class="['result-circle', getResultColor(result.result_color)]">
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 gap-3">
                <a href="deposit.php" class="bg-green-500 text-white p-4 rounded-2xl text-center font-medium">
                    <i class="fas fa-plus mb-2 block"></i>
                    Deposit
                </a>
                <a href="withdraw.php" class="bg-blue-500 text-white p-4 rounded-2xl text-center font-medium">
                    <i class="fas fa-minus mb-2 block"></i>
                    Withdraw
                </a>
            </div>
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <div class="flex">
                <button class="nav-item active" @click="currentTab = 'home'">
                    <i class="fas fa-home text-xl mb-1 block"></i>
                    <span class="text-xs">Home</span>
                </button>
                <button class="nav-item" @click="window.location.href='games.php'">
                    <i class="fas fa-gamepad text-xl mb-1 block"></i>
                    <span class="text-xs">Games</span>
                </button>
                <button class="nav-item" @click="window.location.href='wallet.php'">
                    <i class="fas fa-wallet text-xl mb-1 block"></i>
                    <span class="text-xs">Wallet</span>
                </button>
                <button class="nav-item" @click="window.location.href='profile.php'">
                    <i class="fas fa-user text-xl mb-1 block"></i>
                    <span class="text-xs">Profile</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;
        
        createApp({
            data() {
                return {
                    currentTab: 'home',
                    userBalance: <?php echo $user['balance']; ?>,
                    timeRemaining: 60,
                    currentRound: <?php echo json_encode($currentRound); ?>,
                    lastResults: <?php echo json_encode($lastResults); ?>,
                    notification: <?php echo json_encode($notification); ?>,
                    selectedAmount: 10,
                    betAmounts: [10, 50, 100, 500],
                    loading: false,
                    timerInterval: null,
                    refreshInterval: null
                }
            },
            computed: {
                timerProgress() {
                    return ((60 - this.timeRemaining) / 60) * 100;
                }
            },
            methods: {
                async placeBet(color) {
                    if (this.selectedAmount <= 0) {
                        Swal.fire('Error', 'Please select a valid amount', 'error');
                        return;
                    }
                    
                    if (this.selectedAmount > this.userBalance) {
                        Swal.fire('Error', 'Insufficient balance', 'error');
                        return;
                    }
                    
                    this.loading = true;
                    
                    try {
                        const response = await fetch('../api/place_bet.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                round_id: this.currentRound.id,
                                color: color,
                                amount: this.selectedAmount,
                                game_type: 'color'
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.userBalance = data.new_balance;
                            Swal.fire('Success', `Bet placed on ${color.toUpperCase()} for ₹${this.selectedAmount}`, 'success');
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error', 'Failed to place bet', 'error');
                    }
                    
                    this.loading = false;
                },
                
                getResultColor(color) {
                    const colors = {
                        'red': 'bg-red-500',
                        'green': 'bg-green-500',
                        'violet': 'bg-purple-500'
                    };
                    return colors[color] || 'bg-gray-400';
                },
                
                async updateTimer() {
                    try {
                        const response = await fetch('../api/get_time_remaining.php');
                        const data = await response.json();
                        this.timeRemaining = data.time_remaining;
                        
                        if (this.timeRemaining <= 0) {
                            // Round ended, refresh data
                            await this.refreshGameData();
                        }
                    } catch (error) {
                        console.error('Failed to update timer:', error);
                    }
                },
                
                async refreshGameData() {
                    try {
                        const response = await fetch('../api/get_game_data.php');
                        const data = await response.json();
                        
                        this.currentRound = data.current_round;
                        this.lastResults = data.last_results;
                        this.userBalance = data.user_balance;
                        this.timeRemaining = data.time_remaining;
                    } catch (error) {
                        console.error('Failed to refresh game data:', error);
                    }
                },
                
                async loadNotification() {
                    try {
                        const response = await fetch('../api/get_notification.php');
                        const data = await response.json();
                        if (data.notification) {
                            this.notification = data.notification;
                        }
                    } catch (error) {
                        console.error('Failed to load notification:', error);
                    }
                }
            },
            
            mounted() {
                // Update timer every second
                this.timerInterval = setInterval(this.updateTimer, 1000);
                
                // Refresh game data every 10 seconds
                this.refreshInterval = setInterval(this.refreshGameData, 10000);
                
                // Load notification every 10 seconds
                setInterval(this.loadNotification, 10000);
            },
            
            beforeUnmount() {
                if (this.timerInterval) {
                    clearInterval(this.timerInterval);
                }
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                }
            }
        }).mount('#app');
    </script>
</body>
</html>