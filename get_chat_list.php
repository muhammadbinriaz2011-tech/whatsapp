<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) exit(json_encode([]));
include 'db.php';
$me = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as contact_id FROM messages WHERE sender_id = ? OR receiver_id = ?");
$stmt->execute([$me, $me, $me]);
$contact_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($contact_ids)) {
    echo json_encode([]);
    exit;
}
$placeholders = implode(',', array_fill(0, count($contact_ids), '?'));
$stmt_users = $pdo->prepare("SELECT id, username FROM users WHERE id IN ($placeholders)");
$stmt_users->execute($contact_ids);
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
$user_map = array_column($users, 'username', 'id');
$contacts = [];
$last_msg_stmt = $pdo->prepare("SELECT content, timestamp, sender_id FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp DESC LIMIT 1");
foreach ($contact_ids as $cid) {
    $last_msg_stmt->execute([$me, $cid, $cid, $me]);
    $last = $last_msg_stmt->fetch();
    $contacts[] = [
        'id' => $cid,
        'username' => $user_map[$cid],
        'last_content' => $last ? substr($last['content'], 0, 50) : '',
        'last_time' => $last ? date('h:i A', strtotime($last['timestamp'])) : ''
    ];
}
usort($contacts, fn($a, $b) => strtotime($b['last_time']) <=> strtotime($a['last_time']));
echo json_encode($contacts);
?>
