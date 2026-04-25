<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$name = $_SESSION['name'] ?? "User";
$role = $_SESSION['role'] ?? "";
$isAdmin = ($role === "admin");

$profilePhoto = !empty($_SESSION['profile_photo'])
    ? $_SESSION['profile_photo']
    : "assets/image.png";

$currentPage = basename($_SERVER['PHP_SELF']);

/* Hide top-right user/profile/menu area on these pages */
$hideTopUserSection = in_array($currentPage, [
    'movies.php',
    'stream.php',
    'events.php',
    'sports.php',
    'offers.php',
    'listyourshow.php'
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : "MovieTime" ?></title>

<link rel="stylesheet" href="movie.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
crossorigin="anonymous"
referrerpolicy="no-referrer" />

<style>
body{
  background:#000;
  color:#fff;
  font-family:Arial,sans-serif;
  margin:0;
}

/* Overlay */
.overlay{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.55);
  backdrop-filter:blur(2px);
  opacity:0;
  pointer-events:none;
  transition:.2s;
  z-index:9998;
}
.overlay.show{
  opacity:1;
  pointer-events:auto;
}

/* Sidebar */
aside#sidebar{
  position:fixed;
  top:0;
  right:0;
  height:100vh;
  width:320px;
  background:#111;
  transform:translateX(100%);
  transition:.3s;
  z-index:9999;
  display:flex;
  flex-direction:column;
  box-shadow:-10px 0 30px rgba(0,0,0,.35);
}
aside#sidebar.open{
  transform:translateX(0);
}

.side-top{
  padding:15px;
  border-bottom:1px solid #333;
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.side-top strong{
  color:#fff;
  font-size:18px;
}

.side-top button{
  background:#1a1a1a;
  color:#fff;
  border:none;
  padding:8px 10px;
  border-radius:8px;
  cursor:pointer;
}

.side-links{
  padding:10px;
  display:flex;
  flex-direction:column;
  gap:10px;
}

.side-link{
  padding:12px 14px;
  border-radius:10px;
  background:#1a1a1a;
  text-decoration:none;
  color:#fff;
  transition:.2s;
}

.side-link:hover{
  background:#f84464;
}

.user-chip{
  display:flex;
  align-items:center;
  gap:10px;
}

.user-chip img{
  width:35px;
  height:35px;
  border-radius:50%;
  object-fit:cover;
  border:2px solid rgba(248,68,100,.35);
}

/* Navbar */
.navbar{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:14px 20px;
  background:#111;
}

.logo{
  width:54px;
  height:auto;
  display:block;
}

.navbar-right{
  display:flex;
  align-items:center;
  gap:14px;
}

.navbar .user-chip span{
  color:#fff;
  font-weight:600;
}

.nav-login{
  color:#fff;
  text-decoration:none;
  font-weight:600;
}

.menu-icon{
  cursor:pointer;
  font-size:24px;
  color:#fff;
  line-height:1;
}

/* Secondary nav */
.sec-nav{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:14px 40px;
  background:#f5f5f5;
  border-bottom:1px solid #ddd;
}

.s-left,
.s-right{
  display:flex;
  gap:30px;
  align-items:center;
}

.sec-nav a{
  text-decoration:none;
  color:#222;
  font-size:16px;
  font-weight:500;
  position:relative;
  transition:.3s;
}

.sec-nav a::after{
  content:"";
  position:absolute;
  left:0;
  bottom:-6px;
  width:0%;
  height:2px;
  background:#f84464;
  transition:.3s;
}

.sec-nav a:hover,
.sec-nav a.active{
  color:#f84464;
}

.sec-nav a:hover::after,
.sec-nav a.active::after{
  width:100%;
}

@media (max-width: 900px){
  .sec-nav{
    padding:14px 18px;
    overflow-x:auto;
    gap:20px;
  }

  .s-left,
  .s-right{
    gap:18px;
    flex-shrink:0;
  }
}

@media (max-width: 640px){
  .navbar{
    padding:12px 14px;
  }

  .sec-nav{
    padding:12px 14px;
  }

  .sec-nav a{
    font-size:14px;
  }
}
</style>
</head>

<body>

<!-- Overlay -->
<div id="overlay" class="overlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar">
  <div class="side-top">
    <strong>MovieTime</strong>
    <button type="button" onclick="closeSidebar()">✕</button>
  </div>

  <div class="side-links">
    <a class="side-link" href="show.php">🏠 Home</a>
    <a class="side-link" href="movies.php">🎬 Movies</a>
    <a class="side-link" href="stream.php">📺 Stream</a>
    <a class="side-link" href="events.php">🎉 Events</a>
    <a class="side-link" href="sports.php">🏏 Sports</a>
    <a class="side-link" href="offers.php">🎁 Offers</a>

    <?php if($isLoggedIn): ?>
      <div class="user-chip" style="padding:8px 4px;">
        <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Profile">
        <span><?= htmlspecialchars($name) ?></span>
      </div>

      <a class="side-link" href="user/profile.php">👤 Profile</a>
      <a class="side-link" href="user/orders.php">🎟 Orders</a>

      <?php if($isAdmin): ?>
        <a class="side-link" href="admin/index.php">⚙ Admin</a>
      <?php endif; ?>

      <a class="side-link" href="auth/logout.php">🚪 Logout</a>
    <?php else: ?>
      <a class="side-link" href="auth/login.php">Login</a>
      <a class="side-link" href="auth/register.php">Register</a>
    <?php endif; ?>
  </div>
</aside>

<!-- Navbar -->
<div class="navbar">
  <div>
    <!-- Logo should always go to home page -->
    <a href="show.php">
      <img src="assets/image.png" class="logo" alt="MovieTime">
    </a>
  </div>

  <?php if (!$hideTopUserSection): ?>
    <div class="navbar-right">
      <?php if($isLoggedIn): ?>
        <div class="user-chip">
          <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Profile">
          <span>Hi, <?= htmlspecialchars($name) ?></span>
        </div>
      <?php else: ?>
        <a href="auth/login.php" class="nav-login">Login</a>
      <?php endif; ?>

      <span class="menu-icon" onclick="openSidebar()">☰</span>
    </div>
  <?php endif; ?>
</div>

<!-- Secondary navigation -->
<nav class="sec-nav">
  <div class="s-left">
    <a href="movies.php" class="<?= $currentPage == 'movies.php' ? 'active' : '' ?>">Movies</a>
    <a href="stream.php" class="<?= $currentPage == 'stream.php' ? 'active' : '' ?>">Stream</a>
    <a href="events.php" class="<?= $currentPage == 'events.php' ? 'active' : '' ?>">Events</a>
    <a href="sports.php" class="<?= $currentPage == 'sports.php' ? 'active' : '' ?>">Sports</a>
  </div>

  <div class="s-right">
    <a href="listyourshow.php" class="<?= $currentPage == 'listyourshow.php' ? 'active' : '' ?>">ListYourShow</a>
    <a href="offers.php" class="<?= $currentPage == 'offers.php' ? 'active' : '' ?>">Offers</a>
  </div>
</nav>

<script>
function openSidebar(){
  document.getElementById("sidebar").classList.add("open");
  document.getElementById("overlay").classList.add("show");
}

function closeSidebar(){
  document.getElementById("sidebar").classList.remove("open");
  document.getElementById("overlay").classList.remove("show");
}
</script>