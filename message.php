<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$currentUser = $_SESSION['email'];
$users = $conn->query("SELECT name, email FROM users WHERE email != '$currentUser'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            background-color: #fff5f5;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #cc1f3a;
            margin-bottom: 30px;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .user-list a {
            display: block;
            padding: 12px 15px;
            margin: 10px 0;
            background-color: #ffe5e9;
            border: 1px solid #f3c5cc;
            border-radius: 8px;
            text-decoration: none;
            color: #cc1f3a;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .user-list a:hover {
            background-color: #fbd0d7;
            transform: translateX(5px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Send a Message</h2>
        <div class="user-list">
            <?php while ($row = $users->fetch_assoc()): ?>
                <a href="send_message.php?to=<?= urlencode($row['email']) ?>">
                    📩 Message <?= htmlspecialchars($row['name']) ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
