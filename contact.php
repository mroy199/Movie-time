<?php
session_start();
require_once __DIR__ . "/config/db.php";

$success = $_SESSION["contact_success"] ?? "";
$error = $_SESSION["contact_error"] ?? "";

unset($_SESSION["contact_success"], $_SESSION["contact_error"]);

function clean($value){
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if ($name === "" || $email === "" || $message === "") {
        $_SESSION["contact_error"] = "All fields are required.";
        header("Location: contact.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["contact_error"] = "Please enter a valid email address.";
        header("Location: contact.php");
        exit;
    }

    // Prevent duplicate message within last 10 seconds
    $check = $conn->prepare("
        SELECT id 
        FROM contact_messages 
        WHERE name = ? AND email = ? AND message = ?
          AND created_at >= (NOW() - INTERVAL 10 SECOND)
        LIMIT 1
    ");

    if ($check) {
        $check->bind_param("sss", $name, $email, $message);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $_SESSION["contact_error"] = "This message was already sent. Please wait a moment before sending again.";
            $check->close();
            header("Location: contact.php");
            exit;
        }

        $check->close();
    }

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            $_SESSION["contact_success"] = "Your message has been sent successfully.";
        } else {
            $_SESSION["contact_error"] = "Something went wrong. Please try again.";
        }

        $stmt->close();
    } else {
        $_SESSION["contact_error"] = "Database error. Please try again.";
    }

    header("Location: contact.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MovieTime</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }

        .contact-box{
            width:100%;
            max-width:650px;
            background:#ffffff;
            border-radius:20px;
            padding:35px;
            box-shadow:0 20px 50px rgba(0,0,0,0.18);
        }

        .contact-box h1{
            font-size:32px;
            margin-bottom:10px;
            color:#111827;
        }

        .contact-box p{
            color:#6b7280;
            margin-bottom:25px;
        }

        .alert{
            padding:14px 16px;
            border-radius:12px;
            margin-bottom:18px;
            font-size:14px;
            font-weight:600;
        }

        .alert.success{
            background:#dcfce7;
            color:#166534;
        }

        .alert.error{
            background:#fee2e2;
            color:#b91c1c;
        }

        .form-group{
            margin-bottom:18px;
        }

        label{
            display:block;
            margin-bottom:8px;
            color:#111827;
            font-weight:600;
        }

        input, textarea{
            width:100%;
            padding:14px 15px;
            border:1px solid #d1d5db;
            border-radius:12px;
            outline:none;
            font-size:15px;
            transition:0.2s ease;
        }

        input:focus, textarea:focus{
            border-color:#2563eb;
            box-shadow:0 0 0 3px rgba(37,99,235,0.12);
        }

        textarea{
            min-height:140px;
            resize:vertical;
        }

        .btn{
            width:100%;
            border:none;
            background:#111827;
            color:#fff;
            padding:15px;
            border-radius:12px;
            font-size:16px;
            font-weight:700;
            cursor:pointer;
            transition:0.25s ease;
        }

        .btn:hover{
            background:#2563eb;
        }

        .btn:disabled{
            opacity:0.7;
            cursor:not-allowed;
        }

        .back-link{
            display:inline-block;
            margin-top:16px;
            color:#2563eb;
            text-decoration:none;
            font-weight:600;
        }
    </style>
</head>
<body>

<div class="contact-box">
    <h1>Contact Us</h1>
    <p>Have a question or message? Send it to us below.</p>

    <?php if($success): ?>
        <div class="alert success"><?php echo clean($success); ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert error"><?php echo clean($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="" id="contactForm">
        <div class="form-group">
            <label>Your Name</label>
            <input type="text" name="name" placeholder="Enter your name" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label>Your Message</label>
            <textarea name="message" placeholder="Write your message here..." required></textarea>
        </div>

        <button type="submit" class="btn" id="sendBtn">Send Message</button>
    </form>

    <a href="show.php" class="back-link">← Back to site</a>
</div>

<script>
document.getElementById("contactForm").addEventListener("submit", function () {
    const btn = document.getElementById("sendBtn");
    btn.disabled = true;
    btn.textContent = "Sending...";
});
</script>

</body>
</html>