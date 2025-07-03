<?php
session_start(); if(!isset($_SESSION['user_id'])){header('Location: login.php');exit;} require_once __DIR__ . '/../config/db.php';
$stmt=$pdo->prepare('SELECT uid,balance FROM users WHERE id=?');
$stmt->execute([$_SESSION['user_id']]);
$user=$stmt->fetch();
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Mines Game</title><link rel="stylesheet" href="../assets/css/bootstrap.min.css"><script src="../assets/js/vue.global.js"></script><script src="../assets/js/sweetalert2.min.js"></script><style>.cell{width:70px;height:70px;border:1px solid #ccc;display:flex;align-items:center;justify-content:center;font-size:24px;cursor:pointer;}@media(max-width:600px){.cell{width:60px;height:60px;font-size:20px;}}</style></head><body>
<div id="app" class="container py-3 text-center">
<div class="d-flex justify-content-between mb-2"><div><strong>{{uid}}</strong> | ₹{{balance.toFixed(2)}}</div><a href="dashboard.php" class="btn btn-sm btn-secondary">Home</a></div>
<h5>Select a Safe Cell</h5>
<div class="d-flex flex-column align-items-center">
<div v-for="row in 3" :key="row" class="d-flex">
  <div v-for="col in 3" :key="col" class="cell" @click="selectCell((row-1)*3+col)">{{ (row-1)*3+col }}</div>
</div>
</div>
</div>
<script>
const{createApp}=Vue;createApp({data(){return{uid:'<?php echo $user['uid'];?>',balance:<?php echo (float)$user['balance'];?>}},methods:{async selectCell(index){const{value:amount}=await Swal.fire({title:'Amount',input:'number',showCancelButton:true});if(!amount)return;const fd=new FormData();fd.append('game_type','mines');fd.append('color',index);fd.append('amount',amount);const res=await fetch('../api/predict.php',{method:'POST',body:fd});const json=await res.json();if(json.success){this.balance=json.new_balance;Swal.fire('Success','Bet placed','success');}else{Swal.fire('Error',json.error,'error');}}}}).mount('#app');
</script></body></html>