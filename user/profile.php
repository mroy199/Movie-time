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

$stmt = $conn->prepare("SELECT id, fullname, email, mobile, role, created_at, profile_photo, bio, city, gender FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $bio = trim($_POST["bio"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $profile_photo = $user["profile_photo"] ?? "";

    if ($fullname === "" || $email === "") {
        $error = "Full name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $user_id);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();

        if ($exists) {
            $error = "This email is already used by another account.";
        } else {
            if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] === 0) {
                $allowed = ["image/jpeg", "image/png", "image/webp"];
                $fileType = mime_content_type($_FILES["profile_photo"]["tmp_name"]);
                $ext = strtolower(pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION));
                $allowed_ext = ["jpg","jpeg","png","webp"];

                if (!in_array($fileType, $allowed, true) || !in_array($ext, $allowed_ext, true)) {
                    $error = "Only JPG, PNG, and WEBP images are allowed.";
                } else {
                    $newName = "profile_" . $user_id . "_" . time() . "." . $ext;
                    $uploadDir = "../uploads/profile/";

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $targetPath = $uploadDir . $newName;
                    if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetPath)) {
                        $profile_photo = "uploads/profile/" . $newName;
                    } else {
                        $error = "Failed to upload profile photo.";
                    }
                }
            }

            if ($error === "") {
                $update = $conn->prepare("
                    UPDATE users 
                    SET fullname = ?, email = ?, mobile = ?, bio = ?, city = ?, gender = ?, profile_photo = ?
                    WHERE id = ?
                ");
                $update->bind_param("sssssssi", $fullname, $email, $mobile, $bio, $city, $gender, $profile_photo, $user_id);

                if ($update->execute()) {
                    $_SESSION["name"] = $fullname;
                    $_SESSION["profile_photo"] = $profile_photo;
                    $success = "Profile updated successfully.";

                    $stmt = $conn->prepare("SELECT id, fullname, email, mobile, role, created_at, profile_photo, bio, city, gender FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                } else {
                    $error = "Failed to update profile.";
                }
            }
        }
    }
}

$photo = (!empty($user["profile_photo"]) && file_exists("../".$user["profile_photo"]))
    ? "../" . $user["profile_photo"]
    : "../assets/image.png";

$pageTitle = "My Profile - MovieTime";
include("../includes/site_header_user.php");
?>

<style>
.wrap{max-width:1000px;margin:30px auto;padding:20px}
.card{background:#111;border:1px solid #222;border-radius:16px;padding:20px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.full{grid-column:1/-1}
label{display:block;margin-bottom:6px;color:#ddd}
input, textarea, select{width:100%;padding:12px;border:none;border-radius:10px;background:#222;color:#fff}
textarea{min-height:100px;resize:vertical}
.btn-page{display:inline-block;background:#f84464;color:#fff;text-decoration:none;padding:12px 16px;border-radius:10px;font-weight:700;border:none;cursor:pointer}
.btn-dark{background:#222}
.msg{padding:12px;border-radius:10px;margin-bottom:15px}
.ok{background:#12361f;border:1px solid #245c36;color:#7dffa6}
.err{background:#331218;border:1px solid #5c2431;color:#ff9ab0}
.meta{background:#151515;border:1px solid #222;border-radius:12px;padding:16px;margin-top:20px}
.meta p{margin:8px 0;color:#ccc}
.photo-box{display:flex;align-items:center;gap:20px;margin-bottom:20px;flex-wrap:wrap}
.photo-box img{width:120px;height:120px;object-fit:cover;border-radius:50%;border:3px solid #f84464;background:#222}
@media (max-width:768px){.grid{grid-template-columns:1fr}}
</style>

<div class="wrap">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
    <h1 style="margin:0;">My Profile</h1>
    <div>
      <a class="btn-page btn-dark" href="orders.php">My Orders</a>
      <a class="btn-page" href="change_password.php">Change Password</a>
    </div>
  </div>

  <div class="card">
    <?php if($success): ?><div class="msg ok"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if($error): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="photo-box">
        <img src="<?= htmlspecialchars($photo) ?>" alt="Profile Photo">
        <div>
          <h2 style="margin:0;"><?= htmlspecialchars($user["fullname"]) ?></h2>
          <p style="color:#aaa;margin-top:4px;"><?= ucfirst(($user["role"] === "member" ? "user" : ($user["role"] ?? "user"))) ?></p>
          <label>Profile Photo</label>
          <input type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp">
        </div>
      </div>

      <div class="grid">
        <div>
          <label>Full Name</label>
          <input type="text" name="fullname" value="<?= htmlspecialchars($user["fullname"] ?? "") ?>" required>
        </div>

        <div>
          <label>Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user["email"] ?? "") ?>" required>
        </div>

        <div>
          <label>Mobile</label>
          <input type="text" name="mobile" value="<?= htmlspecialchars($user["mobile"] ?? "") ?>">
        </div>

        <div>
          <label>City</label>
          <input type="text" name="city" value="<?= htmlspecialchars($user["city"] ?? "") ?>">
        </div>

        <div>
          <label>Gender</label>
          <select name="gender">
            <option value="">Select Gender</option>
            <option value="Male" <?= (($user["gender"] ?? "") === "Male") ? "selected" : "" ?>>Male</option>
            <option value="Female" <?= (($user["gender"] ?? "") === "Female") ? "selected" : "" ?>>Female</option>
            <option value="Other" <?= (($user["gender"] ?? "") === "Other") ? "selected" : "" ?>>Other</option>
          </select>
        </div>

        <div class="full">
          <label>Bio</label>
          <textarea name="bio" placeholder="Write something about yourself..."><?= htmlspecialchars($user["bio"] ?? "") ?></textarea>
        </div>
      </div>

      <button class="btn-page" type="submit" style="margin-top:18px;">Update Profile</button>
    </form>

    <div class="meta">
      <p><strong>Role:</strong> <?= ucfirst(($user["role"] === "member" ? "user" : ($user["role"] ?? "user"))) ?></p>
      <p><strong>Joined:</strong> <?= htmlspecialchars($user["created_at"] ?? "N/A") ?></p>
    </div>
  </div>
</div>

<?php include("../includes/site_footer_user.php"); ?>