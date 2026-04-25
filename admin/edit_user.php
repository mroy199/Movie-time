<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

function clean($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    die("Invalid user ID");
}

$result = $conn->query("SELECT * FROM users WHERE id = $id");
$user = $result ? $result->fetch_assoc() : null;

if (!$user) {
    die("User not found");
}

// Protect main admin
$isProtectedAdmin = ((int)$user["id"] === 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Block editing protected admin completely
    if ($isProtectedAdmin) {
        die("Main admin account cannot be edited.");
    }

    $name  = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $role  = trim($_POST["role"] ?? "user");

    if ($name === "" || $email === "") {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!in_array($role, ["admin", "user"], true)) {
        $error = "Invalid role selected.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $role, $id);

        if ($stmt->execute()) {
            header("Location: users.php?success=updated");
            exit;
        } else {
            $error = "Failed to update user.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family: Arial, sans-serif;
            background:#0f172a;
            color:#fff;
            min-height:100vh;
            padding:40px 20px;
        }

        .wrap{
            max-width:700px;
            margin:0 auto;
        }

        .card{
            background:#111827;
            border:1px solid rgba(255,255,255,0.08);
            border-radius:18px;
            padding:28px;
            box-shadow:0 12px 30px rgba(0,0,0,0.25);
        }

        h2{
            font-size:28px;
            margin-bottom:10px;
        }

        .subtext{
            color:#94a3b8;
            margin-bottom:24px;
        }

        .alert{
            padding:14px 16px;
            border-radius:12px;
            margin-bottom:18px;
            font-size:14px;
            font-weight:600;
        }

        .alert.error{
            background:rgba(239,68,68,0.15);
            color:#fecaca;
            border:1px solid rgba(239,68,68,0.3);
        }

        .alert.protected{
            background:rgba(245,158,11,0.15);
            color:#fde68a;
            border:1px solid rgba(245,158,11,0.3);
        }

        .form-group{
            margin-bottom:18px;
        }

        label{
            display:block;
            margin-bottom:8px;
            font-weight:600;
            color:#e5e7eb;
        }

        input, select{
            width:100%;
            padding:14px 15px;
            border-radius:12px;
            border:1px solid rgba(255,255,255,0.12);
            background:#0b1220;
            color:#fff;
            font-size:15px;
            outline:none;
        }

        input:focus, select:focus{
            border-color:#3b82f6;
        }

        input[disabled], select[disabled]{
            opacity:.7;
            cursor:not-allowed;
        }

        .actions{
            display:flex;
            gap:12px;
            flex-wrap:wrap;
            margin-top:10px;
        }

        .btn{
            display:inline-block;
            padding:13px 18px;
            border:none;
            border-radius:12px;
            text-decoration:none;
            font-weight:700;
            cursor:pointer;
        }

        .btn-primary{
            background:#2563eb;
            color:#fff;
        }

        .btn-primary:hover{
            background:#1d4ed8;
        }

        .btn-secondary{
            background:#374151;
            color:#fff;
        }

        .btn-secondary:hover{
            background:#4b5563;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h2>Edit User</h2>
            <p class="subtext">Update user information and role.</p>

            <?php if (!empty($error)): ?>
                <div class="alert error"><?php echo clean($error); ?></div>
            <?php endif; ?>

            <?php if ($isProtectedAdmin): ?>
                <div class="alert protected">
                    This is the main admin account. It cannot be edited.
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input
                        type="text"
                        name="fullname"
                        value="<?php echo clean($user["fullname"]); ?>"
                        <?php echo $isProtectedAdmin ? 'disabled' : ''; ?>
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        value="<?php echo clean($user["email"]); ?>"
                        <?php echo $isProtectedAdmin ? 'disabled' : ''; ?>
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" <?php echo $isProtectedAdmin ? 'disabled' : ''; ?>>
                        <option value="user" <?php echo $user["role"] === "user" ? "selected" : ""; ?>>User</option>
                        <option value="admin" <?php echo $user["role"] === "admin" ? "selected" : ""; ?>>Admin</option>
                    </select>
                </div>

                <div class="actions">
                    <?php if (!$isProtectedAdmin): ?>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    <?php endif; ?>

                    <a href="users.php" class="btn btn-secondary">Back to Users</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>