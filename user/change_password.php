<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $current_password = $_POST["current_password"] ?? "";
    $new_password = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($current_password === "" || $new_password === "" || $confirm_password === "") {
        $error = "All password fields are required.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($current_password, $user["password"])) {
            $error = "Current password is incorrect.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed, $user_id);

            if ($update->execute()) {
                $success = "Password changed successfully.";
            } else {
                $error = "Failed to change password.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password</title>
  <link rel="stylesheet" href="../movie.css">
  <style>
    body{background:#000;color:#fff;font-family:Arial,sans-serif}
    .wrap{max-width:700px;margin:30px auto;padding:20px}
    .top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:20px}
    .card{background:#111;border:1px solid #222;border-radius:16px;padding:20px}
    label{display:block;margin-bottom:6px;color:#ddd}
    input{width:100%;padding:12px;border:none;border-radius:10px;background:#222;color:#fff;margin-bottom:14px}
    .btn{display:inline-block;background:#f84464;color:#fff;text-decoration:none;padding:12px 16px;border-radius:10px;font-weight:700;border:none;cursor:pointer}
    .btn.secondary{background:#222}
    .msg{padding:12px;border-radius:10px;margin-bottom:15px}
    .ok{background:#12361f;border:1px solid #245c36;color:#7dffa6}
    .err{background:#331218;border:1px solid #5c2431;color:#ff9ab0}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <h1 style="margin:0;">Change Password</h1>
      <div>
        <a class="btn secondary" href="profile.php">Back to Profile</a>
      </div>
    </div>

    <div class="card">
      <?php if($success): ?><div class="msg ok"><?= htmlspecialchars($success) ?></div><?php endif; ?>
      <?php if($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

      <form method="POST">
        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>

        <button class="btn" type="submit">Update Password</button>
      </form>
    </div>
  </div>
</body>
</html>