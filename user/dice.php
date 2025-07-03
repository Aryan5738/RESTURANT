<?php
session_start(); if(!isset($_SESSION['user_id'])){header('Location: login.php');exit;}require_once __DIR__ . '/../config/db.php';$stmt=$pdo->prepare('SELECT uid,balance FROM users WHERE id=?');$stmt->execute([$_SESSION['user_id']]);$user=$stmt->fetch();
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Dice Game</title><link rel="stylesheet" href="../assets/css/bootstrap.min.css"><script src="../assets/js/vue.global.js"></script><script src="../assets/js/sweetalert2.min.js"></script></head><body>
<div id="app" class="container py-3">
<div class="d-flex justify-content-between mb-2"><div><strong>{{uid}}</strong> | Balance ₹{{balance.toFixed(2)}}</div><a href="dashboard.php" class="btn btn-sm btn-secondary">Home</a></div>
<div class="card p-3 text-center"><h5>Pick a Dice Number</h5>
<div class="d-flex flex-wrap justify-content-center gap-2 mt-2" style="max-width:300px;margin:auto;">
<button v-for="n in 6" :key="n" class="btn btn-outline-dark" style="width:60px;height:60px;font-size:24px;" @click="bet(n)">{{n}}</button>
</div></div></div>
<script>
const{createApp}=Vue;createApp({data(){return{uid:'<?php echo $user['uid'];?>',balance:<?php echo (float)$user['balance'];?>}},methods:{async bet(num){const{value:amount}=await Swal.fire({title:'Amount',input:'number',showCancelButton:true});if(!amount)return;const fd=new FormData();fd.append('game_type','dice');fd.append('color',num);fd.append('amount',amount);const res=await fetch('../api/predict.php',{method:'POST',body:fd});const json=await res.json();if(json.success){this.balance=json.new_balance;Swal.fire('Success','Bet placed','success');}else{Swal.fire('Error',json.error,'error');}}}}).mount('#app');</script></body></html>