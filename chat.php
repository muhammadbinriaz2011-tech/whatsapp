<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}
include 'db.php';
$me = $_SESSION['user_id'];
$chat_with = $_GET['chat_with'] ?? null;
$contact_username = '';
if ($chat_with) {
    // Update to read
    $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE sender_id = ? AND receiver_id = ? AND status != 'read'");
    $stmt->execute([$chat_with, $me]);
    // Get contact username
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$chat_with]);
    $contact = $stmt->fetch();
    $contact_username = $contact ? $contact['username'] : '';
}
$body_class = $chat_with ? 'chat-open' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>WhatsApp Clone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #EDEDED; }
        .container { display: flex; height: 100vh; }
        .sidebar { width: 30%; background: white; border-right: 1px solid #ccc; overflow-y: auto; }
        .header { background: #075E54; color: white; padding: 10px; display: flex; justify-content: space-between; align-items: center; }
        .header h2 { margin: 0; }
        #new-chat { background: #25D366; border: none; color: white; padding: 5px 10px; cursor: pointer; border-radius: 5px; }
        #chat-list { list-style: none; padding: 0; margin: 0; }
        #chat-list li { padding: 10px; border-bottom: 1px solid #ccc; display: flex; justify-content: space-between; cursor: pointer; }
        #chat-list li:hover { background: #f0f0f0; }
        .contact { flex: 1; }
        .contact h3 { margin: 0; }
        .contact p { margin: 0; color: gray; font-size: 0.9em; }
        .chat-window { width: 70%; display: flex; flex-direction: column; }
        .chat-header { background: #075E54; color: white; padding: 10px; display: flex; align-items: center; }
        .chat-header h2 { margin: 0; flex: 1; }
        #back { display: none; background: none; border: none; color: white; cursor: pointer; }
        .messages { flex: 1; overflow-y: auto; padding: 10px; background: #EDEDED; }
        .message { max-width: 60%; margin-bottom: 10px; padding: 10px; border-radius: 10px; }
        .sent { background: #DCF8C6; align-self: flex-end; margin-left: auto; }
        .received { background: white; align-self: flex-start; margin-right: auto; }
        .message p { margin: 0; }
        .message span { font-size: 0.8em; color: gray; display: block; text-align: right; }
        .status { color: gray; }
        .status.read { color: #34B7F1; }
        form { display: flex; padding: 10px; background: white; }
        input[type="text"] { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 20px; }
        form button { background: #075E54; color: white; border: none; padding: 10px 20px; margin-left: 10px; cursor: pointer; border-radius: 20px; }
        #modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 2; }
        #modal div { background: white; padding: 20px; border-radius: 10px; width: 80%; max-width: 400px; }
        #search-input { width: 100%; padding: 10px; }
        #search-list { list-style: none; padding: 0; }
        #search-list li { padding: 10px; border-bottom: 1px solid #ccc; cursor: pointer; }
        #search-list li:hover { background: #f0f0f0; }
        #logout { background: #128C7E; border: none; color: white; padding: 5px 10px; cursor: pointer; border-radius: 5px; margin-left: 10px; }
        @media (max-width: 768px) {
            .container { flex-direction: column; }
            .sidebar { width: 100%; height: 100%; position: absolute; z-index: 1; }
            .chat-window { width: 100%; height: 100%; position: absolute; display: none; flex-direction: column; }
            .chat-open .sidebar { display: none; }
            .chat-open .chat-window { display: flex; }
            #back { display: block; }
        }
    </style>
</head>
<body class="<?php echo $body_class; ?>">
    <div class="container">
        <div class="sidebar">
            <div class="header">
                <h2>Chats</h2>
                <button id="new-chat">New Chat</button>
                <button id="logout">Logout</button>
            </div>
            <ul id="chat-list"></ul>
        </div>
        <div class="chat-window" <?php if (!$chat_with) echo 'style="display:none;"'; ?>>
            <div class="chat-header">
                <button id="back">←</button>
                <h2><?php echo htmlspecialchars($contact_username); ?></h2>
            </div>
            <div class="messages" id="messages"></div>
            <form id="send-form">
                <input type="text" id="input" placeholder="Type a message">
                <button type="submit">Send</button>
            </form>
        </div>
    </div>
    <div id="modal">
        <div>
            <input type="text" id="search-input" placeholder="Search users...">
            <ul id="search-list"></ul>
        </div>
    </div>
    <script>
        const currentChat = <?php echo $chat_with ?: 0; ?>;
        let lastId = 0;
        const me = <?php echo $me; ?>;
 
        function loadChatList() {
            fetch('get_chat_list.php')
                .then(res => res.json())
                .then(data => {
                    const ul = document.getElementById('chat-list');
                    ul.innerHTML = '';
                    if (data.length === 0) {
                        ul.innerHTML = '<li style="padding:10px;color:gray;">No recent chats. Start a new one!</li>';
                    }
                    data.forEach(c => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <div class="contact">
                                <h3>${c.username}</h3>
                                <p>${c.last_content}</p>
                            </div>
                            <span>${c.last_time}</span>
                        `;
                        li.onclick = () => { window.location.href = `chat.php?chat_with=${c.id}`; };
                        ul.appendChild(li);
                    });
                });
        }
 
        function loadMessages() {
            if (!currentChat) return;
            fetch(`get_messages.php?chat_with=${currentChat}&last_id=${lastId}`)
                .then(res => res.json())
                .then(data => {
                    const div = document.getElementById('messages');
                    data.forEach(m => {
                        const msgDiv = document.createElement('div');
                        const isSent = m.sender_id === me;
                        msgDiv.classList.add('message', isSent ? 'sent' : 'received');
                        let statusHtml = '';
                        if (isSent) {
                            statusHtml = m.status === 'read' ? '<span class="status read">✓✓</span>' : '<span class="status">✓</span>';
                        }
                        msgDiv.innerHTML = `<p>${m.content}</p><span>${new Date(m.timestamp).toLocaleTimeString()} ${statusHtml}</span>`;
                        div.appendChild(msgDiv);
                        lastId = Math.max(lastId, m.id);
                    });
                    div.scrollTop = div.scrollHeight;
                });
        }
 
        setInterval(loadMessages, 1000);
        setInterval(loadChatList, 5000);
        loadChatList();
        loadMessages();
 
        document.getElementById('send-form').addEventListener('submit', e => {
            e.preventDefault();
            const input = document.getElementById('input');
            const content = input.value.trim();
            if (!content) return;
            input.value = '';
            fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `receiver=${currentChat}&content=${encodeURIComponent(content)}`
            }).then(() => {
                loadMessages();
                loadChatList();
            });
        });
 
        document.getElementById('new-chat').onclick = () => {
            document.getElementById('modal').style.display = 'flex';
        };
 
        document.getElementById('search-input').addEventListener('input', () => {
            const q = document.getElementById('search-input').value.trim();
            if (q.length < 2) return;
            fetch(`search_users.php?query=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    const ul = document.getElementById('search-list');
                    ul.innerHTML = '';
                    data.forEach(u => {
                        const li = document.createElement('li');
                        li.textContent = u.username;
                        li.onclick = () => {
                            document.getElementById('modal').style.display = 'none';
                            window.location.href = `chat.php?chat_with=${u.id}`;
                        };
                        ul.appendChild(li);
                    });
                });
        });
 
        document.getElementById('back').onclick = () => {
            window.location.href = 'chat.php';
        };
 
        document.getElementById('logout').onclick = () => {
            window.location.href = 'logout.php';
        };
 
        // Close modal on outside click
        document.getElementById('modal').onclick = (e) => {
            if (e.target === document.getElementById('modal')) {
                document.getElementById('modal').style.display = 'none';
            }
        };
    </script>
</body>
</html>
