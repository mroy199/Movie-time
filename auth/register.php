<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$mailConfig = require __DIR__ . "/../config/mail.php";

$error = "";
$success = "";
$name  = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");

function clean($s){
    return htmlspecialchars($s ?? "", ENT_QUOTES, "UTF-8");
}

function sendVerificationEmail($toEmail, $toName, $token, $mailConfig){
    $mail = new PHPMailer(true);

    try {
$baseUrl = "http://localhost:8888/" . basename(dirname(__DIR__));
    $verifyLink = $baseUrl . "/auth/verify.php?token=" . urlencode($token);

        $mail->isSMTP();
        $mail->Host       = $mailConfig['host'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['username'] ?? '';
        $mail->Password   = str_replace(' ', '', $mailConfig['password'] ?? '');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($mailConfig['port'] ?? 587);

        $mail->setFrom(
            $mailConfig['from_email'] ?? $mailConfig['username'],
            $mailConfig['from_name'] ?? 'MovieTime'
        );
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your MovieTime account';

        $safeName = htmlspecialchars($toName, ENT_QUOTES, "UTF-8");
        $safeLink = htmlspecialchars($verifyLink, ENT_QUOTES, "UTF-8");

        $mail->Body = '
        <div style="max-width:600px;margin:0 auto;background:#121218;border:1px solid #242432;border-radius:16px;overflow:hidden;font-family:Arial,sans-serif;color:#ffffff;">
            <div style="padding:20px;border-bottom:1px solid #242432;background:#0f0f14;">
                <h2 style="margin:0;color:#f84464;">MovieTime Email Verification</h2>
            </div>
            <div style="padding:24px;color:#d8d8e4;">
                <p style="font-size:15px;">Hi ' . $safeName . ',</p>
                <p style="font-size:15px;line-height:1.6;">
                    Thank you for creating your MovieTime account.
                    Please verify your email address by clicking the button below.
                </p>
                <p style="margin:30px 0;">
                    <a href="' . $safeLink . '" style="display:inline-block;padding:12px 22px;background:#f84464;color:#ffffff;text-decoration:none;border-radius:10px;font-weight:bold;">
                        Verify Email
                    </a>
                </p>
                <p style="font-size:14px;line-height:1.6;">Or open this link manually:</p>
                <p style="word-break:break-all;">
                    <a href="' . $safeLink . '" style="color:#ff5c7a;">' . $safeLink . '</a>
                </p>
                <p style="font-size:14px;line-height:1.6;">This link will expire in 24 hours.</p>
                <p style="font-size:14px;line-height:1.6;">If you did not create this account, please ignore this email.</p>
            </div>
        </div>';

        $mail->AltBody = "Hi {$toName}, verify your MovieTime account here: {$verifyLink}";

        $mail->send();
        return ["ok" => true, "message" => ""];
    } catch (Exception $e) {
        return ["ok" => false, "message" => $mail->ErrorInfo ?: $e->getMessage()];
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pass  = $_POST["password"] ?? "";
    $cpass = $_POST["confirm_password"] ?? "";

    if ($name === "" || strlen($name) < 3) {
        $error = "Name must be at least 3 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($mobile !== "" && !preg_match('/^[0-9]{10}$/', $mobile)) {
        $error = "Please enter a valid 10-digit mobile number.";
    } elseif (strlen($pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!preg_match('/[A-Z]/', $pass) || !preg_match('/[a-z]/', $pass) || !preg_match('/[0-9]/', $pass)) {
        $error = "Password must include uppercase, lowercase, and a number.";
    } elseif ($pass !== $cpass) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare("
            SELECT id, fullname, email, is_verified
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $check->bind_param("s", $email);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();

        if ($exists) {
            if ((int)$exists["is_verified"] === 1) {
                $error = "This email is already registered. Please login.";
            } else {
                $token = bin2hex(random_bytes(32));
                $expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));

                $update = $conn->prepare("
                    UPDATE users
                    SET fullname = ?, mobile = ?, verification_token = ?, verification_expires = ?
                    WHERE email = ?
                ");
                $update->bind_param("sssss", $name, $mobile, $token, $expiry, $email);

                if ($update->execute()) {
                    $mailResult = sendVerificationEmail($email, $name, $token, $mailConfig);

                    if ($mailResult["ok"]) {
                        $success = "This email was already registered but not verified. A new verification email has been sent.";
                    } else {
                        $error = "This email is already registered but not verified. Resend failed: " . $mailResult["message"];
                    }
                } else {
                    $error = "Could not update unverified account. Please try again.";
                }
            }
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+24 hours"));

            $stmt = $conn->prepare("
                INSERT INTO users (fullname, email, password, mobile, role, is_verified, verification_token, verification_expires)
                VALUES (?, ?, ?, ?, 'user', 0, ?, ?)
            ");
            $stmt->bind_param("ssssss", $name, $email, $hash, $mobile, $token, $expiry);

            if ($stmt->execute()) {
                $mailResult = sendVerificationEmail($email, $name, $token, $mailConfig);

                if ($mailResult["ok"]) {
                    $success = "Registration successful! Verification email sent.";
                    $name = "";
                    $email = "";
                    $mobile = "";
                } else {
                    $error = "Account created, but verification email could not be sent. SMTP error: " . $mailResult["message"];
                }
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Account | MovieTime</title>

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
      --ok:#28c76f;
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
      color:var(--text);
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial;
      padding:18px;
    }
    .wrap{width:460px;max-width:100%}
    .card{
      background: linear-gradient(180deg, rgba(18,18,24,.92), rgba(12,12,16,.92));
      border:1px solid var(--border);
      border-radius:18px;
      box-shadow: var(--shadow);
      overflow:hidden;
    }
    .top{
      padding:18px 20px;
      border-bottom:1px solid var(--border);
      display:flex;
      align-items:center;
      gap:10px;
    }
    .logo{
      width:34px;height:34px;border-radius:12px;
      background: radial-gradient(circle at 30% 30%, var(--pink2), var(--pink));
      box-shadow: 0 0 24px rgba(248,68,100,.35);
      flex:0 0 auto;
    }
    .title h1{margin:0;font-size:18px;letter-spacing:.2px}
    .title p{margin:3px 0 0;color:var(--muted);font-size:13px}
    .body{padding:18px 20px 20px}
    .msg{
      padding:10px 12px;
      border-radius:12px;
      border:1px solid;
      font-size:13px;
      margin-bottom:12px;
      white-space:pre-wrap;
      word-break:break-word;
    }
    .msg.err{
      background: rgba(248,68,100,.12);
      border-color: rgba(248,68,100,.35);
      color:#ffd0da;
    }
    .msg.ok{
      background: rgba(40,199,111,.12);
      border-color: rgba(40,199,111,.35);
      color:#bff3d2;
    }
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .field{margin-bottom:12px}
    label{display:block;margin-bottom:6px;color:#d8d8e4;font-size:13px}
    .input{
      width:100%;
      padding:12px 12px;
      border-radius:12px;
      border:1px solid var(--border);
      background: var(--card2);
      color:var(--text);
      outline:none;
      transition:.15s border, .15s box-shadow;
    }
    .input:focus{
      border-color: rgba(248,68,100,.55);
      box-shadow: 0 0 0 4px rgba(248,68,100,.12);
    }
    .pw-wrap{position:relative}
    .pw-wrap .input{padding-right:46px}
    .eye{
      position:absolute;
      right:10px;
      top:50%;
      transform: translateY(-50%);
      border:none;
      background: transparent;
      color: var(--muted);
      cursor:pointer;
      padding:6px;
      border-radius:10px;
      line-height:0;
    }
    .eye:hover{background: rgba(255,255,255,.06); color:#fff}
    .eye svg{width:20px;height:20px}
    .hint{
      color:var(--muted);
      font-size:12px;
      margin-top:-2px;
      margin-bottom:10px;
      line-height:1.3;
    }
    .btn{
      width:100%;
      border:none;
      border-radius:14px;
      padding:12px 14px;
      background: linear-gradient(90deg, var(--pink), var(--pink2));
      color:#fff;
      font-weight:800;
      cursor:pointer;
      margin-top:6px;
      box-shadow: 0 10px 30px rgba(248,68,100,.25);
    }
    .btn:hover{filter:brightness(1.04)}
    .row{
      margin-top:12px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      color:var(--muted);
      font-size:13px;
      gap:10px;
      flex-wrap:wrap;
    }
    a{color:var(--pink2);text-decoration:none;font-weight:700}
    a:hover{text-decoration:underline}
    .small{font-size:12px;color:var(--muted);margin-top:10px;text-align:center}
    @media (max-width: 520px){
      .grid{grid-template-columns:1fr}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="top">
        <div class="logo"></div>
        <div class="title">
          <h1>Create Account</h1>
          <p>Book tickets faster & track your orders</p>
        </div>
      </div>

      <div class="body">
        <?php if($error): ?>
          <div class="msg err"><?= clean($error) ?></div>
        <?php endif; ?>

        <?php if($success): ?>
          <div class="msg ok"><?= clean($success) ?></div>
        <?php endif; ?>

        <form method="POST" onsubmit="return validateRegister()">
          <div class="field">
            <label>Full Name</label>
            <input class="input" name="name" id="name" required minlength="3"
                   value="<?= clean($name) ?>" placeholder="Eg: Manob Roy">
          </div>

          <div class="field">
            <label>Email</label>
            <input class="input" name="email" id="email" type="email" required
                   value="<?= clean($email) ?>" placeholder="you@example.com">
          </div>

          <div class="field">
            <label>Mobile</label>
            <input class="input" name="mobile" id="mobile" type="text"
                   value="<?= clean($mobile) ?>" placeholder="Enter mobile number">
          </div>

          <div class="grid">
            <div class="field">
              <label>Password</label>
              <div class="pw-wrap">
                <input class="input" name="password" id="password" type="password" required minlength="6" placeholder="Create password">
                <button type="button" class="eye" onclick="togglePassword('password','eye1')" aria-label="Show password">
                  <span id="eye1">
                    <svg viewBox="0 0 24 24" fill="none">
                      <path d="M3 3l18 18" stroke="currentColor" stroke-width="2"/>
                      <path d="M2.5 12s3.5-7 9.5-7c2.2 0 4.1.9 5.6 2" stroke="currentColor" stroke-width="2"/>
                      <path d="M21.5 12s-3.5 7-9.5 7c-2.2 0-4.1-.9-5.6-2" stroke="currentColor" stroke-width="2"/>
                      <path d="M10.2 10.2A3 3 0 0012 15a3 3 0 002.8-1.8" stroke="currentColor" stroke-width="2"/>
                    </svg>
                  </span>
                </button>
              </div>
            </div>

            <div class="field">
              <label>Confirm Password</label>
              <div class="pw-wrap">
                <input class="input" name="confirm_password" id="confirm_password" type="password" required minlength="6" placeholder="Repeat password">
                <button type="button" class="eye" onclick="togglePassword('confirm_password','eye2')" aria-label="Show password">
                  <span id="eye2">
                    <svg viewBox="0 0 24 24" fill="none">
                      <path d="M3 3l18 18" stroke="currentColor" stroke-width="2"/>
                      <path d="M2.5 12s3.5-7 9.5-7c2.2 0 4.1.9 5.6 2" stroke="currentColor" stroke-width="2"/>
                      <path d="M21.5 12s-3.5 7-9.5 7c-2.2 0-4.1-.9-5.6-2" stroke="currentColor" stroke-width="2"/>
                      <path d="M10.2 10.2A3 3 0 0012 15a3 3 0 002.8-1.8" stroke="currentColor" stroke-width="2"/>
                    </svg>
                  </span>
                </button>
              </div>
            </div>
          </div>

          <div class="hint">
            Password must include: <b>1 uppercase</b>, <b>1 lowercase</b>, and <b>1 number</b>.
          </div>

          <button class="btn" type="submit">Create Account</button>

          <div class="row">
            <span>Already have account? <a href="login.php">Login</a></span>
            <span><a href="../index.php">Back to Home</a></span>
          </div>

          <div class="small">By creating an account, you agree to our Terms & Privacy Policy.</div>
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
    iconSpan.innerHTML = show ? eyeOpenSvg() : eyeClosedSvg();
}

function eyeOpenSvg(){
    return `
      <svg viewBox="0 0 24 24" fill="none">
        <path d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7S2.5 12 2.5 12Z" stroke="currentColor" stroke-width="2"/>
        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
      </svg>
    `;
}

function eyeClosedSvg(){
    return `
      <svg viewBox="0 0 24 24" fill="none">
        <path d="M3 3l18 18" stroke="currentColor" stroke-width="2"/>
        <path d="M2.5 12s3.5-7 9.5-7c2.2 0 4.1.9 5.6 2" stroke="currentColor" stroke-width="2"/>
        <path d="M21.5 12s-3.5 7-9.5 7c-2.2 0-4.1-.9-5.6-2" stroke="currentColor" stroke-width="2"/>
        <path d="M10.2 10.2A3 3 0 0012 15a3 3 0 002.8-1.8" stroke="currentColor" stroke-width="2"/>
      </svg>
    `;
}

function validateRegister(){
    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const mobile = document.getElementById("mobile").value.trim();
    const pass = document.getElementById("password").value;
    const cpass = document.getElementById("confirm_password").value;

    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    if(name.length < 3){
        alert("Name must be at least 3 characters.");
        return false;
    }
    if(!emailOk){
        alert("Please enter a valid email.");
        return false;
    }
    if(mobile !== "" && !/^[0-9]{10}$/.test(mobile)){
        alert("Please enter a valid 10-digit mobile number.");
        return false;
    }
    if(pass.length < 6){
        alert("Password must be at least 6 characters.");
        return false;
    }
    if(!/[A-Z]/.test(pass) || !/[a-z]/.test(pass) || !/[0-9]/.test(pass)){
        alert("Password must include uppercase, lowercase, and a number.");
        return false;
    }
    if(pass !== cpass){
        alert("Passwords do not match.");
        return false;
    }
    return true;
}
</script>
</body>
</html>