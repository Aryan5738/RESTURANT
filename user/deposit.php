<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/../config/db.php';
// fetch balance
$stmt=$pdo->prepare('SELECT balance FROM users WHERE id=?');
$stmt->execute([$_SESSION['user_id']]);
$balance=$stmt->fetchColumn();
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Deposit</title><link rel="stylesheet" href="../assets/css/bootstrap.min.css"><script src="../assets/js/vue.global.js"></script><script src="../assets/js/sweetalert2.min.js"></script></head>
<body class="p-3">
<div id="app" class="container" style="max-width:500px;">
<h4>Deposit Funds</h4>
<p>Current Balance: ₹{{balance.toFixed(2)}}</p>
<form @submit.prevent="submitDep">
<div class="mb-3"><label class="form-label">Amount</label><input v-model="amount" type="number" min="1" class="form-control" required></div>
<div class="mb-3"><label class="form-label">UPI Transaction ID</label><input v-model="txn_id" type="text" class="form-control" required></div>
<button class="btn btn-success w-100">Submit Deposit</button>
</form>
</div>
<script>
const {createApp}=Vue;
createApp({
 data(){return{balance:<?php echo (float)$balance;?>,amount:'',txn_id:''}},
 methods:{
  async submitDep(){
   const fd=new FormData();
   fd.append('amount',this.amount);
   fd.append('txn_id',this.txn_id);
   const res=await fetch('../api/deposit.php',{method:'POST',body:fd});
   const json=await res.json();
   if(json.success){
     Swal.fire('Success','Deposit request submitted','success').then(()=>location.href='dashboard.php');
   }else{Swal.fire('Error',json.error,'error');}
  }
 }
}).mount('#app');
</script>
</body></html>