<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/send_mail.php";
session_start();

$error = "";
$success = "";
$showOtpForDev = "";

function eyeSvg($open){
  if($open){
    return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7S2.5 12 2.5 12Z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>';
  }
  return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 3l18 18" stroke="currentColor" stroke-width="2"/><path d="M2.5 12s3.5-7 9.5-7c2.2 0 4.1.9 5.6 2" stroke="currentColor" stroke-width="2"/><path d="M21.5 12s-3.5 7-9.5 7c-2.2 0-4.1-.9-5.6-2" stroke="currentColor" stroke-width="2"/><path d="M10.2 10.2A3 3 0 0012 15a3 3 0 002.8-1.8" stroke="currentColor" stroke-width="2"/></svg>';
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $step = $_POST["step"] ?? "";

    if ($step === "send_otp") {
        $email = trim($_POST["email"] ?? "");

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            $stmt = $conn->prepare("SELECT id, fullname, email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if (!$user) {
                $error = "No account found with this email.";
            } else {
                $otp = (string)random_int(100000, 999999);
                $expiresAt = date("Y-m-d H:i:s", time() + 300);

                $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $del->bind_param("s", $email);
                $del->execute();

                $ins = $conn->prepare("INSERT INTO password_resets (user_id, email, otp, expires_at) VALUES (?, ?, ?, ?)");
                $ins->bind_param("isss", $user["id"], $email, $otp, $expiresAt);
                $ins->execute();

                $_SESSION["reset_email"] = $email;

                $subject = "MovieTime Password Reset OTP";
                $htmlBody = "
                    <div style='font-family:Arial,sans-serif'>
                        <h2>MovieTime Password Reset</h2>
                        <p>Hello " . htmlspecialchars($user["fullname"]) . ",</p>
                        <p>Your OTP for password reset is:</p>
                        <h1 style='letter-spacing:4px;color:#f84464'>" . htmlspecialchars($otp) . "</h1>
                        <p>This OTP is valid for 5 minutes.</p>
                    </div>
                ";
                $plainBody = "Hello {$user["fullname"]}, Your MovieTime password reset OTP is {$otp}. It is valid for 5 minutes.";

                $mailResult = sendMovieTimeMail($email, $user["fullname"], $subject, $htmlBody, $plainBody);

                if ($mailResult['ok']) {
                    $success = "OTP sent successfully to your email.";
                } else {
                    $showOtpForDev = $otp;
                    $success = "SMTP mail failed in local setup. Use this OTP for testing.";
                    $error = "Mail error: " . $mailResult['message'];
                }
            }
        }
    }

    if ($step === "verify_otp") {
        $email = trim($_POST["email"] ?? "");
        $otp = trim($_POST["otp"] ?? "");
        $new_password = $_POST["new_password"] ?? "";
        $confirm_password = $_POST["confirm_password"] ?? "";

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        } elseif (!preg_match('/^\d{6}$/', $otp)) {
            $error = "OTP must be 6 digits.";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $stmt = $conn->prepare("
                SELECT pr.*, u.id AS user_id
                FROM password_resets pr
                JOIN users u ON pr.user_id = u.id
                WHERE pr.email = ? AND pr.otp = ?
                ORDER BY pr.id DESC
                LIMIT 1
            ");
            $stmt->bind_param("ss", $email, $otp);
            $stmt->execute();
            $reset = $stmt->get_result()->fetch_assoc();

            if (!$reset) {
                $error = "Invalid OTP.";
            } elseif (strtotime($reset["expires_at"]) < time()) {
                $error = "OTP has expired. Please request a new one.";
            } else {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);

                $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->bind_param("si", $hashed, $reset["user_id"]);

                if ($upd->execute()) {
                    $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                    $del->bind_param("s", $email);
                    $del->execute();

                    unset($_SESSION["reset_email"]);
                    $success = "Password reset successful. You can now login.";
                } else {
                    $error = "Failed to reset password.";
                }
            }
        }
    }
}

$defaultEmail = $_SESSION["reset_email"] ?? "";
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password | MovieTime</title>
  <style>
    :root{
      --bg:#0b0b0f;
      --card:#121218;
      --card2:#0f0f14;
      --border:#242432;
      --text:#ffffff;
      --muted:#b9b9c6;
      --pink:#f84464;
      --pink2:#ff5c7a;
      --shadow: 0 18px 45px rgba(0,0,0,.55);
    }
    *{box-sizing:border-box}
    body{
      margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
      background:
        radial-gradient(800px 500px at 20% 10%, rgba(248,68,100,.20), transparent 60%),
        radial-gradient(900px 550px at 80% 20%, rgba(120,90,255,.14), transparent 60%),
        var(--bg);
      color:var(--text); font-family:Arial,sans-serif; padding:18px;
    }
    .wrap{width:460px;max-width:100%}
    .card{background:linear-gradient(180deg, rgba(18,18,24,.92), rgba(12,12,16,.92));border:1px solid var(--border);border-radius:18px;box-shadow:var(--shadow);overflow:hidden}
    .top{padding:18px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
    .logo{width:34px;height:34px;border-radius:12px;background:radial-gradient(circle at 30% 30%, var(--pink2), var(--pink))}
    .title h1{margin:0;font-size:18px}
    .title p{margin:3px 0 0;color:var(--muted);font-size:13px}
    .body{padding:18px 20px 20px}
    .msg{padding:10px 12px;border-radius:12px;border:1px solid;font-size:13px;margin-bottom:12px}
    .msg.ok{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.35);color:#b6ffd0}
    .msg.err{background:rgba(248,68,100,.12);border-color:rgba(248,68,100,.35);color:#ffd0da}
    .msg.dev{background:rgba(59,130,246,.12);border-color:rgba(59,130,246,.35);color:#bfdbfe}
    .field{margin-bottom:12px}
    label{display:block;margin-bottom:6px;color:#d8d8e4;font-size:13px}
    .input{width:100%;padding:12px;border-radius:12px;border:1px solid var(--border);background:var(--card2);color:var(--text);outline:none}
    .pw-wrap{position:relative}
    .eye{position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:transparent;color:var(--muted);cursor:pointer;padding:6px;border-radius:10px;line-height:0}
    .eye svg{width:20px;height:20px}
    .btn{width:100%;border:none;border-radius:14px;padding:12px 14px;background:linear-gradient(90deg, var(--pink), var(--pink2));color:#fff;font-weight:800;cursor:pointer;margin-top:6px}
    .row{margin-top:12px;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;font-size:13px}
    a{color:var(--pink2);text-decoration:none;font-weight:700}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="top">
        <div class="logo"></div>
        <div class="title">
          <h1>Forgot Password</h1>
          <p>Reset your password using OTP</p>
        </div>
      </div>

      <div class="body">
        <?php if($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if($success): ?><div class="msg ok"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if($showOtpForDev): ?><div class="msg dev">Dev OTP: <strong><?= htmlspecialchars($showOtpForDev) ?></strong></div><?php endif; ?>

        <form method="POST" style="margin-bottom:18px;">
          <input type="hidden" name="step" value="send_otp">
          <div class="field">
            <label>Email</label>
            <input class="input" type="email" name="email" required value="<?= htmlspecialchars($_POST["email"] ?? $defaultEmail) ?>">
          </div>
          <button class="btn" type="submit">Send OTP</button>
        </form>

        <form method="POST">
          <input type="hidden" name="step" value="verify_otp">

          <div class="field">
            <label>Email</label>
            <input class="input" type="email" name="email" required value="<?= htmlspecialchars($_POST["email"] ?? $defaultEmail) ?>">
          </div>

          <div class="field">
            <label>OTP</label>
            <input class="input" type="text" name="otp" maxlength="6" required placeholder="Enter 6-digit OTP">
          </div>

          <div class="field">
            <label>New Password</label>
            <div class="pw-wrap">
              <input class="input" type="password" name="new_password" id="new_password" required minlength="6">
              <button type="button" class="eye" onclick="togglePassword('new_password','eye1')">
                <span id="eye1"><?= eyeSvg(false) ?></span>
              </button>
            </div>
          </div>

          <div class="field">
            <label>Confirm Password</label>
            <div class="pw-wrap">
              <input class="input" type="password" name="confirm_password" id="confirm_password" required minlength="6">
              <button type="button" class="eye" onclick="togglePassword('confirm_password','eye2')">
                <span id="eye2"><?= eyeSvg(false) ?></span>
              </button>
            </div>
          </div>

          <button class="btn" type="submit">Verify OTP & Reset Password</button>

          <div class="row">
            <span><a href="login.php">Back to Login</a></span>
            <span><a href="../show.php">Back to Home</a></span>
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
function togglePassword(inputId, iconSpanId){
  const input = document.getElementById(inputId);
  const iconSpan = document.getElementById(iconSpanId);
  const show = input.type === "password";
  input.type = show ? "text" : "password";
  iconSpan.innerHTML = eyeSvg(show);
}
function eyeSvg(open){
  if(open){
    return `
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7S2.5 12 2.5 12Z" stroke="currentColor" stroke-width="2"/>
        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
      </svg>`;
  }
  return `
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M3 3l18 18" stroke="currentColor" stroke-width="2"/>
      <path d="M2.5 12s3.5-7 9.5-7c2.2 0 4.1.9 5.6 2" stroke="currentColor" stroke-width="2"/>
      <path d="M21.5 12s-3.5 7-9.5 7c-2.2 0-4.1-.9-5.6-2" stroke="currentColor" stroke-width="2"/>
      <path d="M10.2 10.2A3 3 0 0012 15a3 3 0 002.8-1.8" stroke="currentColor" stroke-width="2"/>
    </svg>`;
}
</script>
</body>
</html>