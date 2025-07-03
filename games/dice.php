<?php
require_once '../config/functions.php';
requireUserLogin();

$user = getCurrentUser();
$currentRound = getCurrentRound('dice');
$lastResults = getLastResults('dice', 5);

if (!$currentRound) {
    $currentRound = ['id' => createNewRound('dice'), 'period_number' => date('Ymd') . '001'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dice Game - 91CLUB</title>
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
        .dice-btn {
            transition: all 0.3s ease;
            transform: scale(1);
        }
        .dice-btn:hover {
            transform: scale(1.05);
        }
        .big-btn {
            background: linear-gradient(145deg, #f59e0b 0%, #d97706 100%);
        }
        .small-btn {
            background: linear-gradient(145deg, #06b6d4 0%, #0891b2 100%);
        }
        .dice-face {
            width: 60px;
            height: 60px;
            background: white;
            border: 2px solid #333;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin: 2px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div id="app">
        <!-- Header -->
        <div class="gradient-bg text-white p-4">
            <div class="flex items-center">
                <a href="../user/dashboard.php" class="mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div class="flex-1">
                    <h1 class="text-xl font-bold">Dice Game</h1>
                    <p class="text-sm opacity-75">Big or Small</p>
                </div>
                <div class="text-right">
                    <p class="text-sm opacity-75">Balance</p>
                    <p class="text-lg font-bold">₹{{ userBalance }}</p>
                </div>
            </div>
        </div>

        <div class="p-4">
            <!-- Game Section -->
            <div class="game-card rounded-3xl p-6 mb-4">
                <div class="text-center mb-4">
                    <h2 class="text-lg font-bold text-gray-800 mb-2">Dice Prediction</h2>
                    <div class="flex justify-center items-center mb-2">
                        <div class="timer-circle w-16 h-16 rounded-full flex items-center justify-center border-4 border-purple-500" 
                             :style="{'border-color': timeRemaining > 10 ? '#8b5cf6' : '#ef4444'}">
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

                <!-- Dice Betting Options -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <button @click="placeBet('big')" :disabled="timeRemaining <= 0 || loading"
                            class="dice-btn big-btn text-white py-6 rounded-2xl font-bold text-lg">
                        <i class="fas fa-dice-six text-3xl mb-2 block"></i>
                        BIG (4-6)
                        <div class="text-sm opacity-75">Win Rate: 1.8x</div>
                    </button>
                    <button @click="placeBet('small')" :disabled="timeRemaining <= 0 || loading"
                            class="dice-btn small-btn text-white py-6 rounded-2xl font-bold text-lg">
                        <i class="fas fa-dice-one text-3xl mb-2 block"></i>
                        SMALL (1-3)
                        <div class="text-sm opacity-75">Win Rate: 1.8x</div>
                    </button>
                </div>

                <!-- Number Betting -->
                <div class="mb-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Bet on Exact Number (5x payout):</p>
                    <div class="grid grid-cols-6 gap-2">
                        <button v-for="num in [1,2,3,4,5,6]" :key="num"
                                @click="placeBet(num.toString())" :disabled="timeRemaining <= 0 || loading"
                                class="dice-btn bg-gradient-to-r from-purple-500 to-pink-500 text-white py-3 rounded-xl font-bold">
                            {{ num }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Last Results -->
            <div class="bg-white rounded-2xl p-4 mb-4">
                <h3 class="font-bold mb-3">Last Results</h3>
                <div class="flex justify-center space-x-1">
                    <div v-for="result in lastResults" :key="result.id" class="dice-face">
                        {{ result.result_number || '?' }}
                    </div>
                </div>
            </div>

            <!-- Game Rules -->
            <div class="bg-white rounded-2xl p-4">
                <h3 class="font-bold mb-3">Game Rules</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><strong>BIG:</strong> Dice shows 4, 5, or 6 (1.8x payout)</p>
                    <p><strong>SMALL:</strong> Dice shows 1, 2, or 3 (1.8x payout)</p>
                    <p><strong>EXACT NUMBER:</strong> Dice shows the exact number you bet (5x payout)</p>
                    <p><strong>TIME:</strong> Each round lasts 60 seconds</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;
        
        createApp({
            data() {
                return {
                    userBalance: <?php echo $user['balance']; ?>,
                    timeRemaining: 60,
                    currentRound: <?php echo json_encode($currentRound); ?>,
                    lastResults: <?php echo json_encode($lastResults); ?>,
                    selectedAmount: 10,
                    betAmounts: [10, 50, 100, 500],
                    loading: false
                }
            },
            methods: {
                async placeBet(choice) {
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
                                color: choice,
                                amount: this.selectedAmount,
                                game_type: 'dice'
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.userBalance = data.new_balance;
                            Swal.fire('Success', `Bet placed on ${choice.toUpperCase()} for ₹${this.selectedAmount}`, 'success');
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error', 'Failed to place bet', 'error');
                    }
                    
                    this.loading = false;
                },
                
                async updateTimer() {
                    try {
                        const response = await fetch('../api/get_time_remaining.php?game_type=dice');
                        const data = await response.json();
                        this.timeRemaining = data.time_remaining;
                        
                        if (this.timeRemaining <= 0) {
                            await this.refreshGameData();
                        }
                    } catch (error) {
                        console.error('Failed to update timer:', error);
                    }
                },
                
                async refreshGameData() {
                    try {
                        const response = await fetch('../api/get_game_data.php?game_type=dice');
                        const data = await response.json();
                        
                        this.currentRound = data.current_round;
                        this.lastResults = data.last_results;
                        this.userBalance = data.user_balance;
                        this.timeRemaining = data.time_remaining;
                    } catch (error) {
                        console.error('Failed to refresh game data:', error);
                    }
                }
            },
            
            mounted() {
                setInterval(this.updateTimer, 1000);
                setInterval(this.refreshGameData, 10000);
            }
        }).mount('#app');
    </script>
</body>
</html>