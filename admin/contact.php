<?php
session_start();
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Delete message
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: contact.php");
    exit;
}

$result = $conn->query("SELECT * FROM contact_messages ORDER BY id DESC");
$totalMessages = $result ? $result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family: Arial, sans-serif;
            background:#f3f6fb;
            color:#1f2937;
        }

        .page{
            max-width:1300px;
            margin:40px auto;
            padding:0 20px;
        }

        .topbar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:16px;
            flex-wrap:wrap;
            margin-bottom:24px;
        }

        .topbar h1{
            font-size:32px;
            color:#111827;
        }

        .topbar p{
            margin-top:6px;
            color:#6b7280;
        }

        .back-btn{
            text-decoration:none;
            background:#111827;
            color:#fff;
            padding:12px 18px;
            border-radius:12px;
            font-weight:600;
            transition:.2s;
        }

        .back-btn:hover{
            background:#2563eb;
        }

        .card{
            background:#fff;
            border-radius:20px;
            box-shadow:0 12px 30px rgba(0,0,0,0.07);
            overflow:hidden;
            border:1px solid #e5e7eb;
        }

        .card-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:22px 24px;
            border-bottom:1px solid #eef2f7;
            flex-wrap:wrap;
            gap:10px;
        }

        .card-header h2{
            font-size:20px;
            color:#111827;
        }

        .badge{
            background:#e0e7ff;
            color:#3730a3;
            padding:8px 14px;
            border-radius:999px;
            font-size:13px;
            font-weight:700;
        }

        .table-wrap{
            overflow-x:auto;
        }

        table{
            width:100%;
            border-collapse:collapse;
            min-width:1050px;
        }

        thead{
            background:#f9fafb;
        }

        th, td{
            text-align:left;
            padding:16px 20px;
            border-bottom:1px solid #eef2f7;
            vertical-align:top;
        }

        th{
            font-size:14px;
            color:#374151;
        }

        td{
            font-size:14px;
            color:#4b5563;
        }

        tbody tr:hover{
            background:#f8fbff;
        }

        .id-box{
            display:inline-block;
            background:#eff6ff;
            color:#1d4ed8;
            padding:6px 10px;
            border-radius:10px;
            font-weight:700;
            min-width:42px;
            text-align:center;
        }

        .name{
            font-weight:700;
            color:#111827;
        }

        .email{
            color:#2563eb;
            text-decoration:none;
            word-break:break-word;
        }

        .message-box{
            background:#f9fafb;
            border:1px solid #e5e7eb;
            border-radius:12px;
            padding:12px 14px;
            line-height:1.6;
            max-width:380px;
            word-break:break-word;
        }

        .date{
            white-space:nowrap;
            color:#6b7280;
            font-weight:600;
        }

        .actions{
            display:flex;
            flex-wrap:wrap;
            gap:8px;
        }

        .action-btn{
            display:inline-block;
            text-decoration:none;
            padding:10px 14px;
            border-radius:10px;
            font-size:13px;
            font-weight:700;
            transition:.2s;
            border:none;
            cursor:pointer;
        }

        .reply-btn{
            background:#dbeafe;
            color:#1d4ed8;
        }

        .reply-btn:hover{
            background:#2563eb;
            color:#fff;
        }

        .delete-btn{
            background:#fee2e2;
            color:#b91c1c;
        }

        .delete-btn:hover{
            background:#ef4444;
            color:#fff;
        }

        .empty{
            text-align:center;
            padding:60px 20px;
        }

        .empty h3{
            font-size:24px;
            margin-bottom:8px;
            color:#111827;
        }

        .empty p{
            color:#6b7280;
        }
    </style>
</head>
<body>

<div class="page">
    <div class="topbar">
        <div>
            <h1>Contact Messages</h1>
            <p>View all customer messages from the contact page.</p>
        </div>
        <a href="index.php" class="back-btn">Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>All Messages</h2>
            <div class="badge"><?php echo (int)$totalMessages; ?> Messages</div>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                                $id = (int)$row['id'];
                                $name = $row['name'] ?? '';
                                $email = $row['email'] ?? '';
                                $message = $row['message'] ?? '';
                                $createdAt = !empty($row['created_at']) ? date("d M Y, h:i A", strtotime($row['created_at'])) : '-';

                                $replySubject = rawurlencode("Reply from MovieTime");
                                $replyBody = rawurlencode(
                                    "Hello " . $name . ",\n\n" .
                                    "Thank you for contacting MovieTime.\n\n" .
                                    "Regarding your message:\n" .
                                    "\"" . $message . "\"\n\n" .
                                    "Best regards,\nMovieTime Support"
                                );
                            ?>
                            <tr>
                                <td>
                                    <span class="id-box"><?php echo $id; ?></span>
                                </td>

                                <td>
                                    <div class="name"><?php echo clean($name); ?></div>
                                </td>

                                <td>
                                    <a class="email" href="mailto:<?php echo clean($email); ?>">
                                        <?php echo clean($email); ?>
                                    </a>
                                </td>

                                <td>
                                    <div class="message-box">
                                        <?php echo nl2br(clean($message)); ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="date"><?php echo clean($createdAt); ?></div>
                                </td>

                                <td>
                                    <div class="actions">
                                        <a class="action-btn reply-btn"
                                           href="mailto:<?php echo clean($email); ?>?subject=<?php echo $replySubject; ?>&body=<?php echo $replyBody; ?>">
                                           Reply
                                        </a>

                                        <a class="action-btn delete-btn"
                                           href="contact.php?delete=<?php echo $id; ?>"
                                           onclick="return confirm('Delete this message?')">
                                           Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty">
                <h3>No messages yet</h3>
                <p>When customers submit the contact form, their messages will show here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>