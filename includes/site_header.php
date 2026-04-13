<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION["role"] ?? "";
$name = $_SESSION["name"] ?? "";
$isLoggedIn = !empty($_SESSION["user_id"]);
$isAdmin = ($role === "admin");

$profilePhoto = $_SESSION["profile_photo"] ?? "";
$profilePhoto = !empty($profilePhoto) ? $profilePhoto : "assets/image.png";
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
    body{background:#000;color:#fff;font-family:Arial,sans-serif;margin:0}
    .overlay{
      position:fixed; inset:0; background:rgba(0,0,0,.55);
      backdrop-filter:blur(2px); opacity:0; pointer-events:none;
      transition:.2s; z-index:9998;
    }
    .overlay.show{opacity:1;pointer-events:auto}

    aside#sidebar.sidebar{
      position:fixed; top:0; right:0; height:100vh; width:320px; max-width:88vw;
      background:linear-gradient(180deg,#14141a,#0b0b0f);
      border-left:1px solid #b9aeae; transform:translateX(105%);
      transition:.25s ease; z-index:9999; box-shadow:-20px 0 60px rgba(0,0,0,.6);
      display:flex; flex-direction:column;
    }
    aside#sidebar.sidebar.open{transform:translateX(0)}

    .side-top{
      display:flex; align-items:center; justify-content:space-between;
      padding:16px; border-bottom:1px solid #e0d7d7;
    }
    .brand{display:flex;align-items:center;gap:10px}
    .logo-dot{
      width:36px;height:36px;border-radius:14px;
      background:radial-gradient(circle at 30% 30%, #ff5c7a, #f84464);
      box-shadow:0 0 22px rgba(248,68,100,.35);
    }
    .brand-name{color:#fff;font-weight:800}
    .brand-sub{color:#bdbdbd;font-size:12px;margin-top:2px}
    .close-btn{
      width:38px;height:38px;border:none;border-radius:12px;
      background:#111;border:1px solid #e2dcdc;color:#fff;cursor:pointer;
    }

    .side-links{padding:10px 12px;display:flex;flex-direction:column;gap:8px}
    .side-link{
      display:flex;align-items:center;gap:10px;padding:12px 12px;border-radius:14px;
      text-decoration:none;color:#eaeaea;background:rgba(255,255,255,.03);
      border:1px solid rgba(255,255,255,.06);transition:.15s;font-weight:700;
    }
    .side-link:hover{transform:translateX(2px);background:rgba(255,255,255,.06)}
    .side-link.pink{
      border-color:rgba(248,68,100,.35);
      background:rgba(248,68,100,.10);
    }
    .divider{height:1px;background:#222;margin:8px 4px}
    .side-footer{margin-top:auto;padding:14px 16px;border-top:1px solid #222;color:#888}

    .menu-icon{
      cursor:pointer; margin-left:12px; color:#fff; font-size:20px;
      display:flex; align-items:center; justify-content:center;
      width:42px; height:42px; border-radius:12px; border:1px solid #222; background:#111;
    }

    .user-chip{display:flex;align-items:center;gap:10px;margin-right:10px}
    .user-chip img{
      width:38px;height:38px;border-radius:50%;object-fit:cover;
      border:2px solid #f84464;background:#222;
    }
    .sidebar-profile{
      display:flex;align-items:center;gap:12px;padding:10px 12px;
      background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.05);border-radius:14px;
    }
    .sidebar-profile img{
      width:50px;height:50px;border-radius:50%;object-fit:cover;
      border:2px solid #f84464;background:#222;
    }

    .sec-nav a{
      color:#fff; text-decoration:none; margin-right:20px; font-weight:600; transition:.3s;
    }
    .sec-nav a:hover{color:#f84464}
    .s-right a{margin-left:20px}
    .page-wrap{max-width:1200px;margin:30px auto;padding:20px}
    .page-card{
      background:#111;border:1px solid #222;border-radius:16px;padding:24px;
    }
    .grid{
      display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;
    }
    .media-card{
      background:#161616;border:1px solid #262626;border-radius:14px;padding:14px;
    }
    .media-card img{
      width:100%;height:300px;object-fit:cover;border-radius:10px;background:#222;
    }
    .media-card h3{margin:12px 0 8px}
    .media-card p{color:#bbb;margin:6px 0}
    .btn-page{
      display:inline-block;background:#f84464;color:#fff;text-decoration:none;
      padding:10px 14px;border-radius:10px;font-weight:700;
    }

    .search-box{position:relative}
.search-results{
  position:absolute;
  top:48px;
  left:0;
  width:100%;
  background:#111;
  border:1px solid #e0d6d6;
  border-radius:12px;
  display:none;
  z-index:999;
  max-height:320px;
  overflow-y:auto;
}
.search-item{
  display:flex;
  gap:10px;
  padding:10px;
  cursor:pointer;
  align-items:center;
  border-bottom:1px solid #d3cbcb3a;
}
.search-item:hover{background:#1a1a1a}
.search-item img{
  width:45px;
  height:60px;
  object-fit:cover;
  border-radius:8px;
  background:#222;
}
  </style>
</head>
<body>

<div id="overlay" class="overlay" onclick="closeSidebar()"></div>

<aside id="sidebar" class="sidebar">
  <div class="side-top">
    <div class="brand">
      <div class="logo-dot"></div>
      <div>
        <div class="brand-name">MovieTime</div>
        <div class="brand-sub">
          <?php if($isLoggedIn): ?>
            <?= $isAdmin ? "Admin Account" : "User Account" ?>
          <?php else: ?>
            Guest Mode
          <?php endif; ?>
        </div>
      </div>
    </div>
    <button class="close-btn" onclick="closeSidebar()">✕</button>
  </div>

  <nav class="side-links">
    <a class="side-link" href="show.php">🏠 Home</a>

    <?php if($isLoggedIn): ?>
      <div class="sidebar-profile">
        <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Profile">
        <div>
          <div style="color:#fff;font-weight:700;"><?= htmlspecialchars($name ?: "User") ?></div>
          <small style="color:#aaa;"><?= $isAdmin ? "Admin" : "User" ?></small>
        </div>
      </div>

      <div class="divider"></div>

      <a class="side-link" href="user/profile.php">👤 My Profile</a>
      <a class="side-link" href="user/orders.php">🎟️ My Bookings</a>

      <?php if($isAdmin): ?>
        <a class="side-link" href="admin/index.php">🛠️ Admin Panel</a>
      <?php endif; ?>

      <div class="divider"></div>
      <a class="side-link pink" href="auth/logout.php">🚪 Logout</a>
    <?php else: ?>
      <a class="side-link pink" href="auth/login.php">🔑 Login</a>
      <a class="side-link" href="auth/register.php">📝 Register</a>
    <?php endif; ?>
  </nav>

  <div class="side-footer">
    <small>© <?= date("Y") ?> MovieTime</small>
  </div>
</aside>

<nav class="navbar">
  <div class="nav-left">
    <a href="show.php"><img src="./assets/image.png" class="logo" alt="logo"></a>
    <div class="search-box">
<input type="text" id="searchInput" placeholder="Search for Movies..." onkeyup="handleSearch(event)">
<div id="searchResults" class="search-results"></div>
    </div>
  </div>

  <div class="nav-right">
    <select class="location">
      <option>Rajkot</option>
      <option>Ahmedabad</option>
      <option>Surat</option>
      <option>Mumbai</option>
      <option>Delhi</option>
    </select>

    <?php if(!$isLoggedIn): ?>
      <a href="auth/login.php"><button class="login-btn">Login</button></a>
      <a href="auth/register.php"><button class="login-btn" style="margin-left:8px;">Register</button></a>
    <?php else: ?>
      <div class="user-chip">
        <img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Profile">
        <span style="color:#fff;">Hi, <?= htmlspecialchars($name ?: "User") ?></span>
      </div>

      <?php if($isAdmin): ?>
        <a href="admin/index.php"><button class="login-btn">Admin Panel</button></a>
      <?php else: ?>
        <a href="user/orders.php"><button class="login-btn">My Orders</button></a>
        <a href="user/profile.php"><button class="login-btn" style="margin-left:8px;">Profile</button></a>
      <?php endif; ?>

      <a href="auth/logout.php"><button class="login-btn" style="margin-left:8px;">Logout</button></a>
    <?php endif; ?>

    <div class="menu-icon" onclick="openSidebar()">
      <i class="fa-solid fa-bars"></i>
    </div>
  </div>
</nav>

<nav class="sec-nav">
  <div class="s-left">
    <a href="movies.php">Movies</a>
    <a href="stream.php">Stream</a>
    <a href="events.php">Events</a>
    <a href="sports.php">Sports</a>
  </div>
  <div class="s-right">
    <a href="listyourshow.php">ListYourShow</a>
    <a href="offers.php">Offers</a>
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