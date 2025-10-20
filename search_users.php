<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) exit(json_encode([]));
include 'db.php';
$me = $_SESSION['user_id'];
$query = '%' . ($_GET['query'] ?? '') . '%';
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE username ILIKE ? AND id != ? LIMIT 10");
$stmt->execute([$query, $me]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
