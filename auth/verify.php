<?php
require_once __DIR__ . "/../config/db.php";
session_start();

$message = "";
$type = "error";

$token = trim($_GET["token"] ?? "");

if ($token === "") {
    $message = "Invalid verification link.";
} else {
    $stmt = $conn->prepare("
        SELECT id, fullname, email, is_verified, verification_expires
        FROM users
        WHERE verification_token = ?
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;

        if (!$user) {
            $message = "Invalid or already used verification link.";
        } elseif ((int)$user["is_verified"] === 1) {
            header("Location: login.php?verified=1");
            exit;
        } elseif (
            empty($user["verification_expires"]) ||
            strtotime($user["verification_expires"]) === false ||
            strtotime($user["verification_expires"]) < time()
        ) {
            $message = "This verification link has expired. Please register again or request a new verification email.";
        } else {
            $update = $conn->prepare("
                UPDATE users
                SET is_verified = 1,
                    verification_token = NULL,
                    verification_expires = NULL
                WHERE id = ?
            ");

            if ($update) {
                $update->bind_param("i", $user["id"]);

                if ($update->execute()) {
                    header("Location: login.php?verified=1");
                    exit;
                } else {
                    $message = "Verification failed. Please try again.";
                }
            } else {
                $message = "Could not process verification request.";
            }
        }
    } else {
        $message = "Database error. Please try again.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verify Email | MovieTime</title>
  <style>
    :root{
      --bg:#0b0b0f;
      --card:#121218;
      --border:#242432;
      --text:#ffffff;
      --muted:#b9b9c6;
      --pink:#f84464;
      --pink2:#ff5c7a;
      --shadow:0 18px 45px rgba(0,0,0,.45);
      --success:#8ff0b3;
      --error:#ffd0da;
    }

    *{box-sizing:border-box}

    body{
      margin:0;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      background:
        radial-gradient(800px 500px at 20% 10%, rgba(248,68,100,.20), transparent 60%),
        radial-gradient(900px 550px at 80% 20%, rgba(120,90,255,.14), transparent 60%),
        var(--bg);
      font-family:Arial,sans-serif;
      color:var(--text);
      padding:20px;
    }

    .box{
      width:100%;
      max-width:500px;
      background:linear-gradient(180deg, rgba(18,18,24,.92), rgba(12,12,16,.92));
      border:1px solid var(--border);
      border-radius:18px;
      padding:28px 24px;
      text-align:center;
      box-shadow:var(--shadow);
    }

    h2{
      margin:0 0 12px;
      font-size:28px;
    }

    p{
      margin:0;
      font-size:16px;
      line-height:1.6;
    }

    .success{color:var(--success);}
    .error{color:var(--error);}

    a{
      display:inline-block;
      margin-top:22px;
      padding:12px 18px;
      background:linear-gradient(90deg, var(--pink), var(--pink2));
      color:#fff;
      text-decoration:none;
      border-radius:12px;
      font-weight:700;
      box-shadow:0 10px 30px rgba(248,68,100,.25);
    }

    a:hover{
      filter:brightness(1.05);
    }
  </style>
</head>
<body>
  <div class="box">
    <h2>Email Verification</h2>
    <p class="<?= $type === 'success' ? 'success' : 'error' ?>">
      <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
    </p>
    <a href="login.php">Go to Login</a>
  </div>
</body>
</html>