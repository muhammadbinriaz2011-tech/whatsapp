<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) exit(json_encode([]));
include 'db.php';
$me = $_SESSION['user_id'];
$chat_with = $_GET['chat_with'];
$last_id = $_GET['last_id'] ?? 0;
$stmt = $pdo->prepare("SELECT id, sender_id, content, timestamp, status FROM messages WHERE 
    ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND id > ? ORDER BY timestamp ASC");
$stmt->execute([$me, $chat_with, $chat_with, $me, $last_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
