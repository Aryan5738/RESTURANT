<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
$stmt = $pdo->prepare('SELECT uid, balance, name FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="../assets/js/vue.global.js"></script>
    <script src="../assets/js/sweetalert2.min.js"></script>
    <style>
        .btn-purple { background:#8e2de2; color:#fff; }
    </style>
</head>
<body>
<div id="app" class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <strong>{{ user.uid }}</strong><br>
            Balance: ₹{{ user.balance.toFixed(2) }}
        </div>
        <button @click="logout" class="btn btn-outline-danger btn-sm">Logout</button>
    </div>

    <div class="card mb-3 text-center p-3">
        <h5>Round #{{ round.period_number }}</h5>
        <div class="display-6">{{ countdown }}s</div>
        <div class="d-flex justify-content-center gap-2 mt-2">
            <button class="btn btn-danger"  @click="bet('RED')">RED</button>
            <button class="btn btn-success" @click="bet('GREEN')">GREEN</button>
            <button class="btn btn-purple" @click="bet('VIOLET')">VIOLET</button>
        </div>
    </div>

    <h6>Last Results</h6>
    <div class="d-flex gap-1 mb-4">
        <div v-for="r in lastResults" :key="r.id" class="border" :style="{width:'24px',height:'24px',background:r.result_color.toLowerCase()}" ></div>
    </div>

    <div class="alert alert-info py-2 text-center" v-if="notification">
        {{ notification.message }}
    </div>
</div>

<nav class="fixed-bottom bg-white border-top d-flex justify-content-around py-2">
    <a href="dashboard.php" class="text-center text-decoration-none"><img src="../assets/icons/home.png" width="24"><br><small>Home</small></a>
    <a href="bigsmall.php" class="text-center text-decoration-none"><img src="../assets/icons/dice.png" width="24"><br><small>Games</small></a>
    <a href="wallet.php" class="text-center text-decoration-none"><img src="../assets/icons/wallet.png" width="24"><br><small>Wallet</small></a>
    <a href="profile.php" class="text-center text-decoration-none"><img src="../assets/icons/user.png" width="24"><br><small>Profile</small></a>
</nav>

<script>
const { createApp } = Vue;
createApp({
    data(){
        return {
            user: <?php echo json_encode($user); ?>,
            round: { period_number: 0, timestamp: '' },
            countdown: 60,
            lastResults: [],
            notification: null
        }
    },
    created(){
        this.fetchRound();
        setInterval(this.fetchRound, 10000);
        setInterval(()=>{ if(this.countdown>0) this.countdown--; },1000);
        this.fetchHistory();
        setInterval(this.fetchNotification,10000);
    },
    methods:{
        async fetchRound(){
            const res  = await fetch('../api/get_round.php');
            const json = await res.json();
            if(json.period_number){
                this.round = json;
                const ts     = new Date(json.timestamp).getTime() / 1000;
                const now    = Math.floor(Date.now()/1000);
                this.countdown = 60 - (now - ts);
            }
        },
        async bet(color){
            const { value: amount } = await Swal.fire({ title:'Enter Amount', input:'number', inputAttributes:{ min:1 }, showCancelButton:true });
            if(!amount) return;
            const fd = new FormData();
            fd.append('game_type','color');
            fd.append('color',color);
            fd.append('amount',amount);
            const res  = await fetch('../api/predict.php', { method:'POST', body:fd });
            const json = await res.json();
            if(json.success){
                this.user.balance = json.new_balance;
                Swal.fire('Success','Bet Placed','success');
            }else{
                Swal.fire('Error', json.error,'error');
            }
        },
        async fetchHistory(){
            const res  = await fetch('../api/get_round.php?history=1');
        },
        logout(){
            fetch('../api/logout.php').then(()=>location.href='login.php');
        },
        async fetchNotification(){
            const res  = await fetch('../api/notification.php');
            const json = await res.json();
            if(json.success){
                this.notification = json;
            }
        }
    }
}).mount('#app');
</script>
</body>
</html>