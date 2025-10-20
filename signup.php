<?php
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'chat.php';</script>";
    exit;
}
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'] ?? null;
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $email]);
        echo "<script>alert('Signup successful'); window.location.href = 'index.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error: Username or email already exists.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Signup - WhatsApp Clone</title>
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
        <h2>Signup</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email (optional)">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Signup</button>
        </form>
        <p>Have an account? <a href="index.php">Login</a></p>
    </div>
</body>
</html>
