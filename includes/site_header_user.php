<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$name = $_SESSION["name"] ?? "User";
$role = $_SESSION["role"] ?? "";
$isAdmin = ($role === "admin");

$profilePhoto = $_SESSION["profile_photo"] ?? "";
$profilePhoto = !empty($profilePhoto) ? "../" . $profilePhoto : "../assets/image.png";
?>

<nav style="background:#000;padding:10px;display:flex;justify-content:space-between;align-items:center">

  <a href="../show.php" style="color:white;text-decoration:none;">🎬 MovieTime</a>

  <div style="display:flex;align-items:center;gap:10px">

    <img src="<?= $profilePhoto ?>" 
         style="width:40px;height:40px;border-radius:50%;border:2px solid #f43f5e">

    <span><?= htmlspecialchars($name) ?></span>

    <?php if($isAdmin): ?>
      <a href="../admin/index.php" style="color:#f43f5e">Admin</a>
    <?php endif; ?>

    <a href="../auth/logout.php" style="color:#f43f5e">Logout</a>

  </div>

</nav>