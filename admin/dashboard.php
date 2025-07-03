<?php
require_once '../config/functions.php';
requireAdminLogin();

$admin = getCurrentAdmin();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as pending_deposits FROM deposits WHERE status = 'pending'");
$pendingDeposits = $stmt->fetch()['pending_deposits'];

$stmt = $pdo->query("SELECT COUNT(*) as pending_withdraws FROM withdraws WHERE status = 'pending'");
$pendingWithdraws = $stmt->fetch()['pending_withdraws'];

$stmt = $pdo->query("SELECT SUM(balance) as total_balance FROM users");
$totalBalance = $stmt->fetch()['total_balance'] ?? 0;

$stmt = $pdo->query("SELECT SUM(amount) as today_deposits FROM deposits WHERE DATE(created_at) = CURDATE() AND status = 'approved'");
$todayDeposits = $stmt->fetch()['today_deposits'] ?? 0;

$stmt = $pdo->query("SELECT SUM(amount) as today_withdraws FROM withdraws WHERE DATE(created_at) = CURDATE() AND status = 'approved'");
$todayWithdraws = $stmt->fetch()['today_withdraws'] ?? 0;

// Get current round
$currentRound = getCurrentRound();

// Get recent activities
$stmt = $pdo->prepare("SELECT p.*, u.name, u.uid, r.period_number FROM predictions p 
                       JOIN users u ON p.user_id = u.id 
                       JOIN rounds r ON p.round_id = r.id 
                       ORDER BY p.created_at DESC LIMIT 10");
$stmt->execute();
$recentBets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - 91CLUB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: linear-gradient(180deg, #1e3a8a 0%, #3730a3 100%);
        }
        .stat-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .nav-link.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div id="app" class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 text-white p-6">
            <div class="mb-8">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-shield-alt mr-3"></i>91CLUB
                </h1>
                <p class="text-sm opacity-75">Admin Panel</p>
            </div>
            
            <nav class="space-y-2">
                <a href="#" @click="currentTab = 'dashboard'" :class="['nav-link block px-4 py-3 rounded-lg', currentTab === 'dashboard' ? 'active' : '']">
                    <i class="fas fa-home mr-3"></i>Dashboard
                </a>
                <a href="#" @click="currentTab = 'users'" :class="['nav-link block px-4 py-3 rounded-lg', currentTab === 'users' ? 'active' : '']">
                    <i class="fas fa-users mr-3"></i>Users
                </a>
                <a href="#" @click="currentTab = 'deposits'" :class="['nav-link block px-4 py-3 rounded-lg', currentTab === 'deposits' ? 'active' : '']">
                    <i class="fas fa-plus mr-3"></i>Deposits
                    <?php if ($pendingDeposits > 0): ?>
                        <span class="badge bg-warning ms-2"><?php echo $pendingDeposits; ?></span>
                    <?php endif; ?>
                </a>
                <a href="#" @click="currentTab = 'withdraws'" :class="['nav-link block px-4 py-3 rounded-lg', currentTab === 'withdraws' ? 'active' : '']">
                    <i class="fas fa-minus mr-3"></i>Withdraws
                    <?php if ($pendingWithdraws > 0): ?>
                        <span class="badge bg-warning ms-2"><?php echo $pendingWithdraws; ?></span>
                    <?php endif; ?>
                </a>
                <a href="#" @click="currentTab = 'games'" :class="['nav-link block px-4 py-3 rounded-lg', currentTab === 'games' ? 'active' : '']">
                    <i class="fas fa-gamepad mr-3"></i>Game Control
                </a>
                <a href="#" @click="currentTab = 'notifications'" :class="['nav-link block px-4 py-3 rounded-lg', currentTab === 'notifications' ? 'active' : '']">
                    <i class="fas fa-bell mr-3"></i>Notifications
                </a>
                <a href="logout.php" class="nav-link block px-4 py-3 rounded-lg text-red-300 hover:text-red-100">
                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Header -->
            <div class="bg-white shadow-sm p-6 border-b">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold capitalize">{{ currentTab }}</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Welcome, <?php echo $admin['username']; ?></span>
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                            <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Tab -->
            <div v-if="currentTab === 'dashboard'" class="p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-8">
                    <div class="stat-card rounded-2xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Users</p>
                                <p class="text-2xl font-bold"><?php echo $totalUsers; ?></p>
                            </div>
                            <i class="fas fa-users text-3xl text-blue-500"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card rounded-2xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Balance</p>
                                <p class="text-2xl font-bold">₹<?php echo number_format($totalBalance, 0); ?></p>
                            </div>
                            <i class="fas fa-wallet text-3xl text-green-500"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card rounded-2xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Today Deposits</p>
                                <p class="text-2xl font-bold">₹<?php echo number_format($todayDeposits, 0); ?></p>
                            </div>
                            <i class="fas fa-plus text-3xl text-purple-500"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card rounded-2xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Today Withdraws</p>
                                <p class="text-2xl font-bold">₹<?php echo number_format($todayWithdraws, 0); ?></p>
                            </div>
                            <i class="fas fa-minus text-3xl text-red-500"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card rounded-2xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Pending Deposits</p>
                                <p class="text-2xl font-bold"><?php echo $pendingDeposits; ?></p>
                            </div>
                            <i class="fas fa-clock text-3xl text-yellow-500"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card rounded-2xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Pending Withdraws</p>
                                <p class="text-2xl font-bold"><?php echo $pendingWithdraws; ?></p>
                            </div>
                            <i class="fas fa-hourglass-half text-3xl text-orange-500"></i>
                        </div>
                    </div>
                </div>

                <!-- Current Round Status -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">Current Round</h3>
                        <?php if ($currentRound): ?>
                            <div class="space-y-2">
                                <p><strong>Period:</strong> <?php echo $currentRound['period_number']; ?></p>
                                <p><strong>Status:</strong> <span class="badge bg-success"><?php echo ucfirst($currentRound['status']); ?></span></p>
                                <p><strong>Started:</strong> <?php echo date('H:i:s', strtotime($currentRound['start_time'])); ?></p>
                                <p><strong>Time Remaining:</strong> <span id="timer">{{ timeRemaining }}s</span></p>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500">No active round</p>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Bets -->
                    <div class="bg-white rounded-2xl p-6 shadow-lg">
                        <h3 class="text-lg font-bold mb-4">Recent Bets</h3>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <?php foreach ($recentBets as $bet): ?>
                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                    <div>
                                        <p class="text-sm font-medium"><?php echo $bet['name']; ?> (<?php echo $bet['uid']; ?>)</p>
                                        <p class="text-xs text-gray-600"><?php echo $bet['color']; ?> - ₹<?php echo $bet['amount']; ?></p>
                                    </div>
                                    <span class="badge bg-<?php echo $bet['status'] === 'win' ? 'success' : ($bet['status'] === 'loss' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($bet['status']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Tab -->
            <div v-if="currentTab === 'users'" class="p-6">
                <div class="bg-white rounded-2xl p-6 shadow-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Users Management</h3>
                        <button @click="loadUsers" class="btn btn-primary">
                            <i class="fas fa-sync mr-2"></i>Refresh
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>UID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Balance</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users" :key="user.id">
                                    <td>{{ user.uid }}</td>
                                    <td>{{ user.name }}</td>
                                    <td>{{ user.phone }}</td>
                                    <td>₹{{ parseFloat(user.balance).toFixed(2) }}</td>
                                    <td>{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                    <td>
                                        <button @click="editUserBalance(user)" class="btn btn-sm btn-warning me-2">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="viewUserBets(user)" class="btn btn-sm btn-info">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Game Control Tab -->
            <div v-if="currentTab === 'games'" class="p-6">
                <div class="bg-white rounded-2xl p-6 shadow-lg">
                    <h3 class="text-lg font-bold mb-4">Game Control</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Manual Result Control</h5>
                            <form @submit.prevent="setManualResult">
                                <div class="mb-3">
                                    <label class="form-label">Select Result Color:</label>
                                    <select v-model="manualResult" class="form-select" required>
                                        <option value="">Choose result...</option>
                                        <option value="red">RED</option>
                                        <option value="green">GREEN</option>
                                        <option value="violet">VIOLET</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-danger" :disabled="loading">
                                    <i class="fas fa-stop mr-2"></i>End Current Round
                                </button>
                            </form>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Round History</h5>
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th>Result</th>
                                            <th>Status</th>
                                            <th>End Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="round in roundHistory" :key="round.id">
                                            <td>{{ round.period_number }}</td>
                                            <td>
                                                <span :class="['badge', getResultBadgeClass(round.result_color)]">
                                                    {{ round.result_color ? round.result_color.toUpperCase() : 'PENDING' }}
                                                </span>
                                            </td>
                                            <td>{{ round.status }}</td>
                                            <td>{{ round.end_time ? new Date(round.end_time).toLocaleTimeString() : '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue;
        
        createApp({
            data() {
                return {
                    currentTab: 'dashboard',
                    users: [],
                    roundHistory: [],
                    manualResult: '',
                    timeRemaining: 60,
                    loading: false
                }
            },
            methods: {
                async loadUsers() {
                    try {
                        const response = await fetch('../api/admin/get_users.php');
                        const data = await response.json();
                        this.users = data.users || [];
                    } catch (error) {
                        console.error('Failed to load users:', error);
                    }
                },
                
                async loadRoundHistory() {
                    try {
                        const response = await fetch('../api/admin/get_rounds.php');
                        const data = await response.json();
                        this.roundHistory = data.rounds || [];
                    } catch (error) {
                        console.error('Failed to load round history:', error);
                    }
                },
                
                async setManualResult() {
                    if (!this.manualResult) return;
                    
                    this.loading = true;
                    try {
                        const response = await fetch('../api/admin/set_result.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ result: this.manualResult })
                        });
                        
                        const data = await response.json();
                        if (data.success) {
                            Swal.fire('Success', 'Round ended with result: ' + this.manualResult.toUpperCase(), 'success');
                            this.manualResult = '';
                            this.loadRoundHistory();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error', 'Failed to set result', 'error');
                    }
                    this.loading = false;
                },
                
                async editUserBalance(user) {
                    const { value: newBalance } = await Swal.fire({
                        title: `Edit Balance for ${user.name}`,
                        input: 'number',
                        inputValue: user.balance,
                        inputLabel: 'New Balance',
                        inputPlaceholder: 'Enter new balance',
                        showCancelButton: true
                    });
                    
                    if (newBalance) {
                        try {
                            const response = await fetch('../api/admin/update_balance.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ user_id: user.id, balance: newBalance })
                            });
                            
                            const data = await response.json();
                            if (data.success) {
                                Swal.fire('Success', 'Balance updated successfully', 'success');
                                this.loadUsers();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        } catch (error) {
                            Swal.fire('Error', 'Failed to update balance', 'error');
                        }
                    }
                },
                
                getResultBadgeClass(color) {
                    const classes = {
                        'red': 'bg-danger',
                        'green': 'bg-success',
                        'violet': 'bg-primary'
                    };
                    return classes[color] || 'bg-secondary';
                },
                
                async updateTimer() {
                    try {
                        const response = await fetch('../api/get_time_remaining.php');
                        const data = await response.json();
                        this.timeRemaining = data.time_remaining;
                    } catch (error) {
                        console.error('Failed to update timer:', error);
                    }
                }
            },
            
            watch: {
                currentTab(newTab) {
                    if (newTab === 'users') {
                        this.loadUsers();
                    } else if (newTab === 'games') {
                        this.loadRoundHistory();
                    }
                }
            },
            
            mounted() {
                // Update timer every second
                setInterval(this.updateTimer, 1000);
                
                // Load initial data
                this.loadUsers();
                this.loadRoundHistory();
            }
        }).mount('#app');
    </script>
</body>
</html>