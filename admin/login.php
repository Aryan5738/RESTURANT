<?php
session_start();
if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    if ($user === 'admin' && $pass === 'admin123') {
        $_SESSION['admin'] = true;
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid credentials';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
<div class="card p-4" style="max-width:400px;">
    <h4 class="text-center mb-3">Admin Login</h4>
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?=$error?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="user" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="pass" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Login</button>
    </form>
</div>
</body>
</html>