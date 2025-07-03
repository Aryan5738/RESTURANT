<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/../config/db.php';
$stmt=$pdo->prepare('SELECT balance FROM users WHERE id=?');
$stmt->execute([$_SESSION['user_id']]);
$balance=$stmt->fetchColumn();
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Withdraw</title><link rel="stylesheet" href="../assets/css/bootstrap.min.css"><script src="../assets/js/vue.global.js"></script><script src="../assets/js/sweetalert2.min.js"></script></head>
<body class="p-3">
<div id="app" class="container" style="max-width:600px;">
<h4>Withdraw Funds</h4>
<p>Available Balance: ₹{{balance.toFixed(2)}}</p>
<form @submit.prevent="submitW">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Amount</label><input v-model="amount" type="number" min="1" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Password</label><input v-model="password" type="password" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">UPI ID</label><input v-model="upi_id" type="text" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Account No</label><input v-model="account_no" type="text" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">IFSC</label><input v-model="ifsc" type="text" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Bank Name</label><input v-model="bank_name" type="text" class="form-control" required></div>
</div>
<button class="btn btn-danger w-100 mt-3">Request Withdraw</button>
</form>
</div>
<script>
const {createApp}=Vue;
createApp({
 data(){return{balance:<?php echo (float)$balance;?>, amount:'', password:'', upi_id:'', account_no:'', ifsc:'', bank_name:''}},
 methods:{
  async submitW(){
   const fd=new FormData();
   fd.append('amount',this.amount);
   fd.append('password',this.password);
   fd.append('upi_id',this.upi_id);
   fd.append('account_no',this.account_no);
   fd.append('ifsc',this.ifsc);
   fd.append('bank_name',this.bank_name);
   const res=await fetch('../api/withdraw.php',{method:'POST',body:fd});
   const json=await res.json();
   if(json.success){ Swal.fire('Success','Withdraw request submitted','success').then(()=>location.href='dashboard.php'); }
   else{ Swal.fire('Error',json.error,'error'); }
  }
 }
}).mount('#app');
</script>
</body></html>