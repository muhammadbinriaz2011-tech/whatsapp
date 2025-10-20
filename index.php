<?php
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'chat.php';</script>";
    exit;
}
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo "<script>window.location.href = 'chat.php';</script>";
    } else {
        echo "<script>alert('Invalid credentials');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - WhatsApp Clone</title>
    <style>
        body { font-family: Arial, sans-serif; background: #075E54; color: white; margin: 0; padding: 0; }
        .container { max-width: 400px; margin: auto; padding: 20px; background: #128C7E; border-radius: 10px; margin-top: 100px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: none; border-radius: 5px; }
        button { width: 100%; padding: 10px; background: #25D366; border: none; color: white; font-weight: bold; cursor: pointer; border-radius: 5px; }
        button:hover { background: #128C7E; }
        a { color: white; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Signup</a></p>
    </div>
</body>
</html>
