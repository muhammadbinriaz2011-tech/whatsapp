<?php
header('Content-Type: text/plain');
session_start();
if (!isset($_SESSION['user_id'])) exit('Unauthorized');
include 'db.php';
$me = $_SESSION['user_id'];
$receiver = $_POST['receiver'];
$content = $_POST['content'];
$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
$stmt->execute([$me, $receiver, $content]);
echo 'sent';
?>
