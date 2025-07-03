<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="../assets/js/vue.global.js"></script>
    <script src="../assets/js/sweetalert2.min.js"></script>
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
<div id="app" class="card p-4 w-100" style="max-width: 400px;">
    <h4 class="text-center mb-3">Create Account</h4>
    <form @submit.prevent="register">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input v-model="name" type="text" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input v-model="phone" type="text" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input v-model="password" type="password" class="form-control" required>
        </div>
        <button class="btn btn-success w-100">Register</button>
        <p class="mt-3 text-center"><a href="login.php">Login</a></p>
    </form>
</div>
<script>
const { createApp } = Vue;
createApp({
    data() {
        return { name: '', phone: '', password: '' };
    },
    methods: {
        async register() {
            const fd = new FormData();
            fd.append('name', this.name);
            fd.append('phone', this.phone);
            fd.append('password', this.password);
            const res  = await fetch('../api/register.php', { method: 'POST', body: fd });
            const json = await res.json();
            if (json.success) {
                Swal.fire('Success', 'Account created. Your UID is ' + json.uid, 'success')
                    .then(() => window.location = 'dashboard.php');
            } else {
                Swal.fire('Error', json.error, 'error');
            }
        }
    }
}).mount('#app');
</script>
</body>
</html>