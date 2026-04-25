<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$error = "";
$success = "";

function clean($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* Get current user */
$stmt = $conn->prepare("
    SELECT id, fullname, email, mobile, profile_photo, gender, bio, dob, city, state, country
    FROM users
    WHERE id = ?
");

if (!$stmt) {
    die("Failed to prepare user query.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $bio = trim($_POST["bio"] ?? "");
    $dob = trim($_POST["dob"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $state = trim($_POST["state"] ?? "");
    $country = trim($_POST["country"] ?? "");
    $profile_photo = $user["profile_photo"] ?? "";

    // Convert empty DOB to null
    $dob = ($dob === "") ? null : $dob;

    if ($fullname === "" || strlen($fullname) < 3) {
        $error = "Full name must be at least 3 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($gender !== "" && !in_array($gender, ["Male", "Female", "Other"], true)) {
        $error = "Please select a valid gender.";
    } elseif ($dob !== null && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $dob)) {
        $error = "Please enter a valid date of birth.";
    } elseif (strlen($bio) > 500) {
        $error = "Bio must be less than 500 characters.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");

        if (!$check) {
            $error = "Failed to validate email.";
        } else {
            $check->bind_param("si", $email, $user_id);
            $check->execute();
            $exists = $check->get_result()->fetch_assoc();
            $check->close();

            if ($exists) {
                $error = "This email is already used by another account.";
            }
        }

        if ($error === "" && !empty($_FILES["profile_photo"]["name"])) {
            $allowed = ["jpg", "jpeg", "png", "webp"];
            $fileName = $_FILES["profile_photo"]["name"] ?? "";
            $tmpName = $_FILES["profile_photo"]["tmp_name"] ?? "";
            $fileSize = (int) ($_FILES["profile_photo"]["size"] ?? 0);
            $fileError = (int) ($_FILES["profile_photo"]["error"] ?? 0);
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileError !== UPLOAD_ERR_OK) {
                $error = "There was an error uploading the profile photo.";
            } elseif (!in_array($ext, $allowed, true)) {
                $error = "Only JPG, JPEG, PNG, and WEBP files are allowed.";
            } elseif ($fileSize > 2 * 1024 * 1024) {
                $error = "Profile photo must be less than 2MB.";
            } else {
                $uploadDir = __DIR__ . "/../uploads/profile/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = "profile_" . $user_id . "_" . time() . "." . $ext;
                $targetPath = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $profile_photo = "uploads/profile/" . $newFileName;
                } else {
                    $error = "Failed to upload profile photo.";
                }
            }
        }

        if ($error === "") {
            $update = $conn->prepare("
                UPDATE users
                SET fullname = ?, email = ?, mobile = ?, profile_photo = ?, gender = ?, bio = ?, dob = ?, city = ?, state = ?, country = ?
                WHERE id = ?
            ");

            if (!$update) {
                $error = "Failed to prepare update query.";
            } else {
                $update->bind_param(
                    "ssssssssssi",
                    $fullname,
                    $email,
                    $mobile,
                    $profile_photo,
                    $gender,
                    $bio,
                    $dob,
                    $city,
                    $state,
                    $country,
                    $user_id
                );

                if ($update->execute()) {
                    $_SESSION["name"] = $fullname;
                    $_SESSION["email"] = $email;
                    $_SESSION["profile_photo"] = $profile_photo;

                    $success = "Profile updated successfully.";

                    $stmt = $conn->prepare("
                        SELECT id, fullname, email, mobile, profile_photo, gender, bio, dob, city, state, country
                        FROM users
                        WHERE id = ?
                    ");

                    if ($stmt) {
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $user = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                    }
                } else {
                    $error = "Failed to update profile.";
                }

                $update->close();
            }
        }
    }
}

$photo = !empty($user["profile_photo"]) ? "../" . $user["profile_photo"] : "../assets/image.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile - MovieTime</title>
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
    }

    *{box-sizing:border-box}

    body{
      margin:0;
      min-height:100vh;
      background:
        radial-gradient(800px 500px at 20% 10%, rgba(248,68,100,.20), transparent 60%),
        radial-gradient(900px 550px at 80% 20%, rgba(120,90,255,.14), transparent 60%),
        var(--bg);
      color:var(--text);
      font-family:Arial, sans-serif;
      padding:30px 15px;
    }

    .wrap{
      max-width:1000px;
      margin:0 auto;
    }

    .card{
      background: linear-gradient(180deg, rgba(18,18,24,.92), rgba(12,12,16,.92));
      border:1px solid var(--border);
      border-radius:18px;
      padding:24px;
      box-shadow:0 18px 45px rgba(0,0,0,.55);
    }

    .top{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:20px;
      margin-bottom:24px;
      flex-wrap:wrap;
    }

    .title h1{
      margin:0 0 6px;
      font-size:28px;
    }

    .title p{
      margin:0;
      color:var(--muted);
    }

    .profile-preview{
      display:flex;
      align-items:center;
      gap:16px;
      flex-wrap:wrap;
    }

    .profile-preview img{
      width:96px;
      height:96px;
      border-radius:50%;
      object-fit:cover;
      border:3px solid rgba(248,68,100,.4);
      background:#222;
    }

    .msg{
      padding:12px 14px;
      border-radius:12px;
      margin-bottom:16px;
      font-size:14px;
      border:1px solid;
    }

    .err{
      background:rgba(248,68,100,.12);
      border-color:rgba(248,68,100,.35);
      color:#ffd0da;
    }

    .success{
      background:rgba(34,197,94,.12);
      border-color:rgba(34,197,94,.35);
      color:#d1fae5;
    }

    .section-title{
      margin:20px 0 14px;
      font-size:18px;
      color:#fff;
    }

    .grid{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:16px;
    }

    .field{
      margin-bottom:16px;
    }

    .full{
      grid-column:1 / -1;
    }

    label{
      display:block;
      margin-bottom:8px;
      font-size:14px;
      color:#ddd;
    }

    .input{
      width:100%;
      padding:13px 14px;
      border-radius:12px;
      border:1px solid var(--border);
      background:var(--card2);
      color:#fff;
      outline:none;
      font-size:15px;
    }

    .input:focus{
      border-color:rgba(248,68,100,.55);
      box-shadow:0 0 0 4px rgba(248,68,100,.12);
    }

    textarea.input{
      min-height:120px;
      resize:vertical;
    }

    input[type="file"].input{
      padding:10px;
    }

    .actions{
      display:flex;
      gap:12px;
      flex-wrap:wrap;
      margin-top:8px;
    }

    .btn{
      border:none;
      border-radius:12px;
      padding:12px 20px;
      background:linear-gradient(90deg, var(--pink), var(--pink2));
      color:#fff;
      font-weight:700;
      cursor:pointer;
      text-decoration:none;
      display:inline-block;
    }

    .btn.secondary{
      background:#222;
      border:1px solid #333;
    }

    .hint{
      color:var(--muted);
      font-size:12px;
      margin-top:6px;
    }

    @media (max-width: 700px){
      .grid{
        grid-template-columns:1fr;
      }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="top">
        <div class="title">
          <h1>Edit Profile</h1>
          <p>Update your personal details, photo, and bio.</p>
        </div>

        <div class="profile-preview">
          <img src="<?= clean($photo) ?>" alt="Profile Photo">
        </div>
      </div>

      <?php if ($error): ?>
        <div class="msg err"><?= clean($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="msg success"><?= clean($success) ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <div class="section-title">Basic Details</div>

        <div class="grid">
          <div class="field">
            <label>Full Name</label>
            <input class="input" type="text" name="fullname" value="<?= clean($user["fullname"] ?? "") ?>" required>
          </div>

          <div class="field">
            <label>Email</label>
            <input class="input" type="email" name="email" value="<?= clean($user["email"] ?? "") ?>" required>
          </div>

          <div class="field">
            <label>Mobile</label>
            <input class="input" type="text" name="mobile" value="<?= clean($user["mobile"] ?? "") ?>" placeholder="Enter mobile number">
          </div>

          <div class="field">
            <label>Gender</label>
            <select class="input" name="gender">
              <option value="">Select gender</option>
              <option value="Male" <?= (($user["gender"] ?? "") === "Male") ? "selected" : "" ?>>Male</option>
              <option value="Female" <?= (($user["gender"] ?? "") === "Female") ? "selected" : "" ?>>Female</option>
              <option value="Other" <?= (($user["gender"] ?? "") === "Other") ? "selected" : "" ?>>Other</option>
            </select>
          </div>

          <div class="field">
            <label>Date of Birth</label>
            <input class="input" type="date" name="dob" value="<?= clean($user["dob"] ?? "") ?>">
          </div>

          <div class="field">
            <label>Profile Photo</label>
            <input class="input" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp">
            <div class="hint">Allowed: JPG, JPEG, PNG, WEBP. Max 2MB.</div>
          </div>
        </div>

        <div class="section-title">About You</div>

        <div class="grid">
          <div class="field full">
            <label>Bio</label>
            <textarea class="input" name="bio" placeholder="Write something about yourself..."><?= clean($user["bio"] ?? "") ?></textarea>
            <div class="hint">Maximum 500 characters.</div>
          </div>
        </div>

        <div class="section-title">Location</div>

        <div class="grid">
          <div class="field">
            <label>City</label>
            <input class="input" type="text" name="city" value="<?= clean($user["city"] ?? "") ?>" placeholder="Enter city">
          </div>

          <div class="field">
            <label>State</label>
            <input class="input" type="text" name="state" value="<?= clean($user["state"] ?? "") ?>" placeholder="Enter state">
          </div>

          <div class="field full">
            <label>Country</label>
            <input class="input" type="text" name="country" value="<?= clean($user["country"] ?? "") ?>" placeholder="Enter country">
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn">Save Changes</button>
          <a href="../show.php" class="btn secondary">Back to Home</a>
          <a href="orders.php" class="btn secondary">My Orders</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>