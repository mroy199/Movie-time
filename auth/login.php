<?php
require_once __DIR__ . "/../config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = "";
$success = "";

$email = trim($_POST['email'] ?? '');

function clean($s){
    return htmlspecialchars($s ?? "", ENT_QUOTES, "UTF-8");
}

if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = "Account created successfully. Please login.";
}

if (isset($_GET['verified']) && $_GET['verified'] == '1') {
    $success = "Email verified successfully. Please login.";
}

if (isset($_POST['login'])) {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === "" || $password === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");

        if (!$stmt) {
            $error = "Database error. Please try again.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {

                $user = $result->fetch_assoc();

                if (!password_verify($password, $user['password'])) {
                    $error = "Wrong password.";
                } else {
                    $role = $user['role'] ?? 'user';
                    $isVerified = isset($user['is_verified']) ? (int)$user['is_verified'] : 0;

                    // Admin does not need email verification
                    if ($role !== 'admin' && $isVerified !== 1) {
                        $error = "Please verify your email before logging in.";
                    } else {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['name'] = $user['fullname'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $role;
                        $_SESSION['profile_photo'] = $user['profile_photo'] ?? "";

                        if ($role === 'admin') {
                            header("Location: ../admin/index.php");
                        } else {
                            header("Location: ../show.php");
                        }
                        exit;
                    }
                }

            } else {
                $error = "User not found.";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | MovieTime</title>

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
      --success-bg: rgba(34,197,94,.12);
      --success-border: rgba(34,197,94,.35);
      --success-text: #d1fae5;
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
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      padding:18px;
    }

    .wrap{
      width:430px;
      max-width:100%;
    }

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
      width:34px;
      height:34px;
      border-radius:12px;
      background: radial-gradient(circle at 30% 30%, var(--pink2), var(--pink));
      box-shadow: 0 0 24px rgba(248,68,100,.35);
      flex:0 0 auto;
    }

    .title h1{
      margin:0;
      font-size:18px;
      letter-spacing:.2px;
    }

    .title p{
      margin:3px 0 0;
      color:var(--muted);
      font-size:13px;
    }

    .body{
      padding:18px 20px 20px;
    }

    .msg{
      padding:10px 12px;
      border-radius:12px;
      border:1px solid;
      font-size:13px;
      margin-bottom:12px;
    }

    .msg.err{
      background: rgba(248,68,100,.12);
      border-color: rgba(248,68,100,.35);
      color:#ffd0da;
    }

    .msg.success{
      background: var(--success-bg);
      border-color: var(--success-border);
      color: var(--success-text);
    }

    .field{
      margin-bottom:14px;
    }

    label{
      display:block;
      margin-bottom:6px;
      color:#d8d8e4;
      font-size:13px;
    }

    .input{
      width:100%;
      padding:12px 12px;
      border-radius:12px;
      border:1px solid var(--border);
      background: var(--card2);
      color:var(--text);
      outline:none;
      transition:.15s border, .15s box-shadow;
      font-size:15px;
    }

    .input:focus{
      border-color: rgba(248,68,100,.55);
      box-shadow: 0 0 0 4px rgba(248,68,100,.12);
    }

    .pw-wrap{
      position:relative;
    }

    .pw-wrap .input{
      padding-right:46px;
    }

    .eye{
      position:absolute;
      right:10px;
      top:50%;
      transform: translateY(-50%);
      border:none;
      background:transparent;
      color:var(--muted);
      cursor:pointer;
      padding:6px;
      border-radius:10px;
      line-height:0;
    }

    .eye:hover{
      background: rgba(255,255,255,.06);
      color:#fff;
    }

    .eye svg{
      width:20px;
      height:20px;
    }

    .options{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:10px;
      margin-top:-2px;
      margin-bottom:14px;
      flex-wrap:wrap;
    }

    .options a,
    .row a{
      color:var(--pink2);
      text-decoration:none;
      font-weight:700;
    }

    .options a:hover,
    .row a:hover{
      text-decoration:underline;
    }

    .btn{
      width:100%;
      border:none;
      border-radius:14px;
      padding:12px 14px;
      background: linear-gradient(90deg, var(--pink), var(--pink2));
      color:#fff;
      font-weight:800;
      font-size:16px;
      cursor:pointer;
      margin-top:6px;
      box-shadow: 0 10px 30px rgba(248,68,100,.25);
    }

    .btn:hover{
      filter:brightness(1.04);
    }

    .row{
      margin-top:14px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      color:var(--muted);
      font-size:13px;
      gap:10px;
      flex-wrap:wrap;
    }

    .small{
      font-size:12px;
      color:var(--muted);
      margin-top:10px;
      text-align:center;
    }
  </style>
</head>
<body>

  <div class="wrap">
    <div class="card">
      <div class="top">
        <div class="logo"></div>
        <div class="title">
          <h1>Welcome Back</h1>
          <p>Login to book tickets and manage your account</p>
        </div>
      </div>

      <div class="body">
        <?php if($error): ?>
          <div class="msg err"><?= clean($error) ?></div>
        <?php endif; ?>

        <?php if($success): ?>
          <div class="msg success"><?= clean($success) ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="field">
            <label>Email</label>
            <input
              class="input"
              type="email"
              name="email"
              id="email"
              placeholder="you@example.com"
              value="<?= clean($email) ?>"
              required
            >
          </div>

          <div class="field">
            <label>Password</label>
            <div class="pw-wrap">
              <input
                class="input"
                type="password"
                name="password"
                id="password"
                placeholder="Enter your password"
                required
              >
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

          <div class="options">
            <span></span>
            <a href="forgot_password.php">Forgot Password?</a>
          </div>

          <button class="btn" type="submit" name="login">Login</button>

          <div class="row">
            <span>Don’t have an account? <a href="register.php">Create Account</a></span>
            <span><a href="../show.php">Back to Home</a></span>
          </div>

          <div class="small">Login to continue your MovieTime experience.</div>
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
</script>

</body>
</html>