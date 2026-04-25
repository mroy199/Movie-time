<?php
session_start();
include("config/db.php");

$role = $_SESSION["role"] ?? "";
$name = $_SESSION["name"] ?? "";
$isLoggedIn = !empty($_SESSION["user_id"]);
$isAdmin = ($role === "admin");

/* Load active movies from database */
$movies = [];
$result = $conn->query("SELECT * FROM movies WHERE is_active = 1 ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}

function clean($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MovieTime</title>

  <link rel="stylesheet" href="movie.css">

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />

  <style>
    :root{
      --bg:#09090d;
      --card:#12131a;
      --card2:#171923;
      --border:rgba(255,255,255,.08);
      --text:#ffffff;
      --muted:#a8adbb;
      --pink:#f84464;
      --pink2:#ff5b7d;
      --shadow:0 20px 45px rgba(0,0,0,.45);
    }

    *{box-sizing:border-box}

    body{
      margin:0;
      background:
        radial-gradient(900px 500px at 10% 0%, rgba(248,68,100,.14), transparent 60%),
        radial-gradient(900px 600px at 100% 0%, rgba(91,110,255,.10), transparent 60%),
        var(--bg);
      color:var(--text);
      font-family:Inter, Arial, sans-serif;
    }

    .overlay{
      position:fixed;
      inset:0;
      background: rgba(0,0,0,0.55);
      backdrop-filter: blur(2px);
      opacity:0;
      pointer-events:none;
      transition:.2s;
      z-index:9998;
    }
    .overlay.show{
      opacity:1;
      pointer-events:auto;
    }

    aside#sidebar.sidebar{
      position:fixed;
      top:0;
      right:0;
      left:auto !important;
      height:100vh;
      width:320px;
      max-width:88vw;
      background: linear-gradient(180deg, #14141a, #0b0b0f);
      border-left:1px solid #222;
      transform: translateX(105%);
      transition:.25s ease;
      z-index:9999;
      box-shadow: -20px 0 60px rgba(0,0,0,.6);
      display:flex;
      flex-direction:column;
    }
    aside#sidebar.sidebar.open{ transform: translateX(0); }

    .side-top{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:16px;
      border-bottom:1px solid #222;
    }
    .brand{display:flex;align-items:center;gap:10px}
    .logo-dot{
      width:36px;height:36px;border-radius:14px;
      background: radial-gradient(circle at 30% 30%, #ff5c7a, #f84464);
      box-shadow: 0 0 22px rgba(248,68,100,.35);
    }
    .brand-name{color:#fff;font-weight:800}
    .brand-sub{color:#bdbdbd;font-size:12px;margin-top:2px}

    .close-btn{
      width:38px;height:38px;
      border:none;border-radius:12px;
      background:#111;border:1px solid #222;
      color:#fff;cursor:pointer;
    }

    .side-links{padding:10px 12px;display:flex;flex-direction:column;gap:8px}
    .side-link{
      display:flex;
      align-items:center;
      gap:10px;
      padding:12px 12px;
      border-radius:14px;
      text-decoration:none;
      color:#eaeaea;
      background: rgba(255,255,255,0.03);
      border:1px solid rgba(255,255,255,0.06);
      transition:.15s;
      font-weight:700;
    }
    .side-link:hover{
      transform: translateX(2px);
      background: rgba(255,255,255,0.06);
    }
    .side-link.pink{
      border-color: rgba(248,68,100,.35);
      background: rgba(248,68,100,.10);
    }
    .divider{height:1px;background:#222;margin:8px 4px;}
    .side-footer{
      margin-top:auto;
      padding:14px 16px;
      border-top:1px solid #222;
      color:#888;
    }

    .navbar{
      position:sticky;
      top:0;
      z-index:100;
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:22px;
      padding:16px 22px;
      background:rgba(7, 8, 14, 0.82);
      backdrop-filter:blur(18px);
      -webkit-backdrop-filter:blur(18px);
      border-bottom:1px solid rgba(255,255,255,.08);
      box-shadow:0 12px 35px rgba(0,0,0,.25);
    }

    .nav-left{
      display:flex;
      align-items:center;
      gap:18px;
      flex:1;
      min-width:0;
    }

    .logo-link{
      display:flex;
      align-items:center;
      justify-content:center;
      width:66px;
      height:66px;
      flex:0 0 auto;
      border-radius:18px;
      background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
      border:1px solid rgba(255,255,255,.06);
      box-shadow:0 10px 24px rgba(0,0,0,.22);
    }

    .logo{
      width:42px;
      height:auto;
      display:block;
    }

    .search-wrap{
      display:flex;
      align-items:center;
      gap:12px;
      flex:1;
      min-width:0;
    }

    .search-box{
      position:relative;
      flex:1;
      min-width:280px;
    }

    .search-box input{
      width:100%;
      height:58px;
      border-radius:20px;
      border:1px solid rgba(248,68,100,.28);
      background:
        linear-gradient(180deg, rgba(20,24,38,.96), rgba(15,19,31,.96));
      color:#fff;
      padding:0 52px 0 50px;
      outline:none;
      font-size:16px;
      font-weight:500;
      box-shadow:
        0 0 0 3px rgba(248,68,100,.07),
        inset 0 0 0 1px rgba(255,255,255,.03),
        0 12px 28px rgba(0,0,0,.18);
      transition:.22s ease;
    }

    .search-box input::placeholder{
      color:#8f96a8;
    }

    .search-box input:focus{
      border-color:rgba(248,68,100,.58);
      box-shadow:
        0 0 0 4px rgba(248,68,100,.12),
        0 14px 34px rgba(248,68,100,.12);
    }

    .search-icon{
      position:absolute;
      left:18px;
      top:50%;
      transform:translateY(-50%);
      color:#98a0b4;
      font-size:16px;
      pointer-events:none;
    }

    .clear-search{
      position:absolute;
      right:12px;
      top:50%;
      transform:translateY(-50%);
      width:32px;
      height:32px;
      border:none;
      border-radius:10px;
      background:rgba(255,255,255,.06);
      color:#b6bfd3;
      cursor:pointer;
      display:flex;
      align-items:center;
      justify-content:center;
      transition:.18s ease;
    }

    .clear-search:hover{
      background:rgba(248,68,100,.12);
      color:#fff;
    }

    .filter-select,
    .location{
      height:54px;
      min-width:170px;
      border-radius:18px;
      background:
        linear-gradient(180deg, rgba(20,24,38,.96), rgba(15,19,31,.96));
      color:#fff;
      border:1px solid rgba(255,255,255,.08);
      padding:0 16px;
      outline:none;
      font-size:15px;
      font-weight:500;
      cursor:pointer;
      box-shadow:
        inset 0 0 0 1px rgba(255,255,255,.02),
        0 8px 22px rgba(0,0,0,.16);
    }

    .filter-select:focus,
    .location:focus{
      border-color:rgba(248,68,100,.4);
      box-shadow:
        0 0 0 4px rgba(248,68,100,.10),
        0 12px 28px rgba(248,68,100,.08);
    }

    .nav-right{
      display:flex;
      align-items:center;
      gap:12px;
      flex-wrap:nowrap;
      flex-shrink:0;
    }

    .profile-box{
      display:flex;
      align-items:center;
      gap:10px;
      padding:7px 12px 7px 8px;
      border-radius:999px;
      background:linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.02));
      border:1px solid rgba(255,255,255,.07);
      box-shadow:0 8px 20px rgba(0,0,0,.14);
    }

    .profile-box img{
      width:40px;
      height:40px;
      border-radius:50%;
      object-fit:cover;
      border:2px solid rgba(248,68,100,.45);
    }

    .profile-box span{
      color:#fff;
      font-weight:600;
      white-space:nowrap;
      font-size:15px;
    }

    .login-btn{
      border:none;
      outline:none;
      background:linear-gradient(90deg, #f84464, #ff5b7d);
      color:#fff;
      padding:12px 18px;
      border-radius:14px;
      font-weight:800;
      cursor:pointer;
      white-space:nowrap;
      box-shadow:0 12px 28px rgba(248,68,100,.24);
      transition:.18s ease;
    }

    .login-btn:hover{
      transform:translateY(-1px);
      filter:brightness(1.03);
    }

    .alt-btn{
      background:linear-gradient(180deg, rgba(255,255,255,.07), rgba(255,255,255,.04));
      border:1px solid rgba(255,255,255,.08);
      box-shadow:none;
    }

    .menu-icon{
      width:50px;
      height:50px;
      border:none;
      border-radius:16px;
      background:linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
      border:1px solid rgba(255,255,255,.08);
      color:#fff;
      font-size:22px;
      display:flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      flex:0 0 auto;
      transition:.18s ease;
    }

    .menu-icon:hover{
      background:rgba(248,68,100,.12);
      border-color:rgba(248,68,100,.25);
    }

    .search-results{
      position:absolute;
      top:100%;
      left:0;
      width:100%;
      background:#111827;
      border:1px solid rgba(255,255,255,.08);
      border-radius:16px;
      margin-top:10px;
      display:none;
      z-index:1000;
      max-height:360px;
      overflow:auto;
      box-shadow:0 18px 40px rgba(0,0,0,.40);
    }

    .search-item{
      display:flex;
      align-items:center;
      gap:12px;
      padding:12px 14px;
      cursor:pointer;
      border-bottom:1px solid rgba(255,255,255,.05);
    }

    .search-item:last-child{
      border-bottom:none;
    }

    .search-item:hover{
      background:rgba(255,255,255,.05);
    }

    .search-item img{
      width:44px;
      height:60px;
      object-fit:cover;
      border-radius:10px;
      background:#222;
    }

    .sec-nav{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:14px 40px;
      background:#f5f5f5;
      border-bottom:1px solid #ddd;
    }

    .s-left,.s-right{
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
      transition:0.3s;
    }

    .sec-nav a::after{
      content:"";
      position:absolute;
      bottom:-6px;
      left:0;
      width:0%;
      height:2px;
      background:#f84464;
      transition:0.3s;
    }

    .sec-nav a:hover{
      color:#f84464;
    }

    .sec-nav a:hover::after{
      width:100%;
    }

    .hero-home{
      max-width:1320px;
      margin:26px auto 0;
      padding:0 20px;
    }

    .hero-banner{
      position:relative;
      overflow:hidden;
      border-radius:28px;
      min-height:430px;
      background:#111;
      box-shadow:var(--shadow);
      border:1px solid var(--border);
    }

    .hero-banner img{
      width:100%;
      height:430px;
      object-fit:cover;
      display:block;
      filter:brightness(.68);
    }

    .hero-overlay{
      position:absolute;
      inset:0;
      background:linear-gradient(90deg, rgba(0,0,0,.78), rgba(0,0,0,.20));
      display:flex;
      align-items:center;
    }

    .hero-content{
      max-width:620px;
      padding:34px;
    }

    .hero-content h1{
      margin:0 0 10px;
      font-size:54px;
      line-height:1.02;
      letter-spacing:-1.5px;
    }

    .hero-content p{
      margin:0 0 22px;
      color:#d8dbe5;
      font-size:17px;
      line-height:1.7;
    }

    .hero-actions{
      display:flex;
      gap:12px;
      flex-wrap:wrap;
    }

    .hero-btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding:13px 22px;
      border-radius:14px;
      text-decoration:none;
      font-weight:800;
    }

    .hero-btn.primary{
      background:linear-gradient(90deg, var(--pink), var(--pink2));
      color:#fff;
    }

    .hero-btn.secondary{
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.10);
      color:#fff;
    }

    .section-wrap{
      max-width:1320px;
      margin:34px auto 0;
      padding:0 20px 60px;
    }

    .section-head{
      display:flex;
      justify-content:space-between;
      align-items:end;
      gap:20px;
      margin-bottom:20px;
      flex-wrap:wrap;
    }

    .section-head h2{
      margin:0;
      font-size:34px;
      letter-spacing:-.5px;
    }

    .section-head p{
      margin:8px 0 0;
      color:var(--muted);
    }

    .movie-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(230px, 1fr));
      gap:22px;
    }

    .movie{
      background:linear-gradient(180deg, rgba(18,19,26,.96), rgba(12,13,18,.96));
      border:1px solid var(--border);
      border-radius:22px;
      overflow:hidden;
      transition:.2s ease;
      box-shadow:0 14px 30px rgba(0,0,0,.28);
    }

    .movie:hover{
      transform: translateY(-6px);
      box-shadow:0 20px 40px rgba(0,0,0,.38);
    }

    .movie img{
      width:100%;
      height:330px;
      object-fit:cover;
      display:block;
      background:#1b1b1b;
    }

    .movie .movie-body{
      padding:18px;
    }

    .movie h3{
      margin:8px 0 10px;
      color:#fff;
      font-size:24px;
    }

    .movie p{
      margin:0;
      color:#ddd;
      line-height:1.4;
    }

    .movie .meta{
      color:#bbb;
      font-size:14px;
      margin-top:8px;
    }

    .movie button{
      margin-top:14px;
      width:100%;
      padding:12px;
      border:none;
      border-radius:12px;
      background:#f84464;
      color:#fff;
      font-weight:800;
      cursor:pointer;
    }

    .movie button:hover{
      background:#ff5c7a;
    }

    .movie.hidden{
      display:none !important;
    }

    .empty-movies{
      background:#111;
      color:#ddd;
      padding:24px;
      border-radius:16px;
      text-align:center;
    }

    .promo-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
      gap:18px;
      margin-top:18px;
    }

    .promo-card{
      background:linear-gradient(180deg, rgba(18,19,26,.96), rgba(12,13,18,.96));
      border:1px solid var(--border);
      border-radius:22px;
      overflow:hidden;
      box-shadow:0 14px 30px rgba(0,0,0,.28);
    }

    .promo-card img{
      width:100%;
      height:180px;
      object-fit:cover;
      display:block;
    }

    .promo-body{
      padding:16px;
    }

    .promo-body h3{
      margin:0 0 8px;
      font-size:20px;
    }

    .promo-body p{
      margin:0;
      color:#bbb;
      line-height:1.6;
    }

    .footer{
      background:#0e0f14;
      border-top:1px solid rgba(255,255,255,.06);
      margin-top:20px;
    }

    .footer-services{
      max-width:1320px;
      margin:0 auto;
      padding:26px 20px;
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
      gap:18px;
    }

    .service{
      background:#151823;
      border:1px solid rgba(255,255,255,.06);
      border-radius:18px;
      padding:20px;
    }

    .service i{
      font-size:24px;
      color:#ff6b88;
      margin-bottom:10px;
    }

    .service h5{
      margin:0 0 8px;
      font-size:18px;
    }

    .service p{
      margin:0;
      color:#b8bcc8;
    }

    .footer-main{
      max-width:1320px;
      margin:0 auto;
      padding:16px 20px 26px;
      display:grid;
      grid-template-columns:2fr 1fr 1fr 1fr 1.4fr;
      gap:22px;
    }

    .footer-col h4{
      margin:0 0 12px;
      font-size:18px;
    }

    .footer-col a{
      display:block;
      color:#cfd3df;
      text-decoration:none;
      margin-bottom:10px;
    }

    .footer-col a:hover{
      color:#ff6b88;
    }

    .footer-logo{
      width:60px;
      display:block;
      margin-bottom:12px;
    }

    .footer-about{
      color:#b8bcc8;
      line-height:1.7;
      margin:0;
    }

    .newsletter p{
      color:#b8bcc8;
    }

    .newsletter input{
      width:100%;
      height:44px;
      border-radius:12px;
      border:1px solid rgba(255,255,255,.08);
      background:#171923;
      color:#fff;
      padding:0 14px;
      margin-bottom:10px;
    }

    .newsletter button{
      width:100%;
      height:44px;
      border:none;
      border-radius:12px;
      background:linear-gradient(90deg, var(--pink), var(--pink2));
      color:#fff;
      font-weight:800;
      cursor:pointer;
    }

    .footer-bottom{
      max-width:1320px;
      margin:0 auto;
      padding:16px 20px 26px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      flex-wrap:wrap;
      gap:16px;
      color:#aeb4c2;
    }

    .footer-social{
      display:flex;
      gap:14px;
      font-size:20px;
    }

    .footer-social i{
      cursor:pointer;
      color:#fff;
    }

    .footer-social i:hover{
      color:#ff6b88;
    }

    @media (max-width: 1380px){
      .navbar{
        flex-direction:column;
        align-items:stretch;
      }

      .nav-left,
      .nav-right{
        width:100%;
      }

      .nav-right{
        flex-wrap:wrap;
        justify-content:flex-end;
      }

      .search-wrap{
        flex-wrap:wrap;
      }
    }

    @media (max-width: 980px){
      .footer-main{
        grid-template-columns:1fr 1fr;
      }

      .hero-content h1{
        font-size:42px;
      }
    }

    @media (max-width: 768px){
      .navbar{
        padding:12px 14px;
      }

      .nav-left{
        flex-direction:column;
        align-items:stretch;
      }

      .search-wrap{
        flex-direction:column;
        align-items:stretch;
      }

      .search-box{
        min-width:unset;
      }

      .filter-select,
      .location{
        width:100%;
        min-width:unset;
      }

      .nav-right{
        justify-content:flex-start;
      }

      .profile-box span{
        display:none;
      }

      .login-btn{
        padding:11px 14px;
      }

      .sec-nav{
        padding:12px 14px;
        overflow-x:auto;
        gap:20px;
      }

      .s-left,.s-right{
        gap:18px;
        flex-shrink:0;
      }

      .hero-banner,
      .hero-banner img{
        min-height:360px;
        height:360px;
      }

      .hero-content{
        padding:24px;
      }

      .hero-content h1{
        font-size:34px;
      }

      .footer-main{
        grid-template-columns:1fr;
      }
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
    <a class="side-link" href="movies.php">🎬 Movies</a>
    <a class="side-link" href="stream.php">📺 Stream</a>
    <a class="side-link" href="events.php">🎉 Events</a>
    <a class="side-link" href="sports.php">🏏 Sports</a>
    <a class="side-link" href="offers.php">🎁 Offers</a>

    <?php if($isLoggedIn): ?>
      <a class="side-link" href="user/orders.php">🎟️ My Bookings</a>
      <a class="side-link" href="user/profile.php">👤 Edit Profile</a>
    <?php else: ?>
      <a class="side-link" href="auth/login.php">🎟️ My Bookings</a>
    <?php endif; ?>

    <?php if($isAdmin): ?>
      <a class="side-link" href="admin/index.php">🛠️ Admin Panel</a>
    <?php endif; ?>

    <div class="divider"></div>

    <?php if(!$isLoggedIn): ?>
      <a class="side-link pink" href="auth/login.php">🔑 Login</a>
      <a class="side-link" href="auth/register.php">📝 Register</a>
    <?php else: ?>
      <a class="side-link pink" href="auth/logout.php">🚪 Logout</a>
    <?php endif; ?>
  </nav>

  <div class="side-footer">
    <small>© <?= date("Y") ?> MovieTime</small>
  </div>
</aside>

<nav class="navbar">
  <div class="nav-left">
    <a href="show.php" class="logo-link">
      <img src="./assets/image.png" class="logo" alt="MovieTime">
    </a>

    <div class="search-wrap">
      <div class="search-box">
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
        <input type="text" id="searchInput" placeholder="Search movies, genre, language..." onkeyup="handleSearch(event)">
        <button type="button" class="clear-search" onclick="clearSearch()" aria-label="Clear search">
          <i class="fa-solid fa-xmark"></i>
        </button>
        <div id="searchResults" class="search-results"></div>
      </div>

      <select id="genreFilter" class="filter-select" onchange="filterMovieGrid()">
        <option value="">All Genres</option>
        <option value="Action">Action</option>
        <option value="Drama">Drama</option>
        <option value="Romantic">Romantic</option>
        <option value="Sci-Fi">Sci-Fi</option>
        <option value="Biography">Biography</option>
        <option value="Comedy">Comedy</option>
        <option value="Thriller">Thriller</option>
        <option value="Horror">Horror</option>
      </select>

      <select id="languageFilter" class="filter-select" onchange="filterMovieGrid()">
        <option value="">All Languages</option>
        <option value="English">English</option>
        <option value="Hindi">Hindi</option>
        <option value="Bengali">Bengali</option>
        <option value="Tamil">Tamil</option>
        <option value="Telugu">Telugu</option>
      </select>
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
      <a href="auth/register.php"><button class="login-btn alt-btn">Register</button></a>
    <?php else: ?>
      <?php
        $navPhoto = !empty($_SESSION["profile_photo"]) ? $_SESSION["profile_photo"] : "assets/image.png";
        $shortName = function_exists('mb_strimwidth') ? mb_strimwidth($name, 0, 18, '...') : substr($name, 0, 18);
      ?>
      <div class="profile-box">
        <img src="<?= clean($navPhoto) ?>" alt="Profile">
        <span>Hi, <?= clean($shortName) ?></span>
      </div>

      <?php if($isAdmin): ?>
        <a href="admin/index.php"><button class="login-btn">Admin Panel</button></a>
      <?php else: ?>
        <a href="user/orders.php"><button class="login-btn">My Orders</button></a>
      <?php endif; ?>

      <a href="user/profile.php"><button class="login-btn alt-btn">Edit Profile</button></a>
      <a href="auth/logout.php"><button class="login-btn alt-btn">Logout</button></a>
    <?php endif; ?>

    <button type="button" class="menu-icon" onclick="openSidebar()" title="Menu" aria-label="Menu">
      <i class="fa-solid fa-bars"></i>
    </button>
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

<div class="hero-home">
  <div class="hero-banner">
    <img src="./assets/toxic1.jpg" alt="MovieTime Banner">
    <div class="hero-overlay">
      <div class="hero-content">
        <h1>Book Movies, Events & More</h1>
        <p>Experience a premium entertainment platform with trending movies, easy seat booking, secure payments, and a modern MovieTime experience.</p>
        <div class="hero-actions">
          <a href="movies.php" class="hero-btn primary">Explore Movies</a>
          <a href="events.php" class="hero-btn secondary">Browse Events</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="section-wrap">
  <div class="section-head">
    <div>
      <h2>Recommended Movies</h2>
      <p>Choose from the latest active movies and book instantly.</p>
    </div>
  </div>

  <div class="movie-grid">
    <?php if (!empty($movies)): ?>
      <?php foreach ($movies as $movie): ?>
        <?php
          $movieId = (int)$movie["id"];
          $title = $movie["title"] ?? "Untitled";
          $image = !empty($movie["image"]) ? $movie["image"] : "./assets/image.png";
          $rating = $movie["rating"] ?? "New";
          $votes = $movie["votes"] ?? "";
          $genre = $movie["genre"] ?? "";
          $language = $movie["language"] ?? "";
          $duration = $movie["duration"] ?? "";
        ?>
        <div class="movie"
             data-id="<?= $movieId ?>"
             data-name="<?= clean($title) ?>"
             data-image="<?= clean($image) ?>"
             data-rating="<?= clean($rating) ?>"
             data-genre="<?= clean($genre) ?>"
             data-language="<?= clean($language) ?>">

          <img src="<?= clean($image) ?>" alt="<?= clean($title) ?>">

          <div class="movie-body">
            <p>
              <i class="fa-solid fa-star" style="color: rgb(252, 150, 116);"></i>
              <?= clean($rating) ?>
              <?php if ($votes !== ""): ?>
                <?= " " . clean($votes) ?>
              <?php endif; ?>
            </p>

            <h3><?= clean($title) ?></h3>

            <p class="meta">
              <?= clean($genre) ?>
              <?php if ($language !== ""): ?> • <?= clean($language) ?><?php endif; ?>
              <?php if ($duration !== ""): ?> • <?= clean($duration) ?><?php endif; ?>
            </p>

            <button type="button" onclick="openSeatSelection(<?= $movieId ?>)">Book Now</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-movies">
        No active movies found.
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="section-wrap" style="padding-top:0;">
  <div class="section-head">
    <div>
      <h2>Best of Live Events</h2>
      <p>Explore comedy, music, theatre, workshops, and more.</p>
    </div>
  </div>

  <div class="promo-grid">
    <div class="promo-card">
      <img src="./assets/comedy-shows-collection-202211140440.avif" alt="Comedy Shows">
      <div class="promo-body">
        <h3>Comedy Shows</h3>
        <p>Laugh out loud with trending stand-up and live comedy performances.</p>
      </div>
    </div>

    <div class="promo-card">
      <img src="./assets/theatre-shows-collection-202211140440.avif" alt="Theatre">
      <div class="promo-body">
        <h3>Theatre</h3>
        <p>Enjoy stage dramas, classic plays, and cultural performances.</p>
      </div>
    </div>

    <div class="promo-card">
      <img src="./assets/music-shows-collection-202211140440.avif" alt="Music">
      <div class="promo-body">
        <h3>Music Shows</h3>
        <p>Book tickets for concerts and live musical experiences.</p>
      </div>
    </div>

    <div class="promo-card">
      <img src="./assets/workshop-and-more-web-collection-202211140440.avif" alt="Workshops">
      <div class="promo-body">
        <h3>Workshops & More</h3>
        <p>Discover exciting workshops, family events, and entertainment activities.</p>
      </div>
    </div>
  </div>
</div>

<div class="footer">
  <div class="footer-services">
    <div class="service">
      <i class="fa-solid fa-headset"></i>
      <h5>24/7 Support</h5>
      <p>Customer assistance anytime</p>
    </div>

    <div class="service">
      <i class="fa-solid fa-shield-halved"></i>
      <h5>Secure Payments</h5>
      <p>100% safe & encrypted</p>
    </div>

    <div class="service">
      <i class="fa-solid fa-ticket"></i>
      <h5>Instant Booking</h5>
      <p>Fast & easy reservations</p>
    </div>
  </div>

  <div class="footer-main">
    <div class="footer-col">
      <img src="./assets/image.png" class="footer-logo" alt="logo">
      <p class="footer-about">
        MovieTime is India's emerging online ticketing platform for
        movies, sports, events and entertainment experiences.
      </p>
    </div>

    <div class="footer-col">
      <h4>Explore</h4>
      <a href="movies.php">Movies</a>
      <a href="events.php">Events</a>
      <a href="sports.php">Sports</a>
      <a href="offers.php">Offers</a>
    </div>

<div class="footer-col">
  <h4>Company</h4>
  <a href="/movietime/listyourshow.php">ListYourShow</a>
  <a href="/movietime/contact.php">Contact</a>
</div>

    <div class="footer-col">
      <h4>Support</h4>
      <a href="#">FAQs</a>
      <a href="#">Terms & Conditions</a>
      <a href="#">Privacy Policy</a>
      <a href="#">Refund Policy</a>
    </div>

    <div class="footer-col newsletter">
      <h4>Subscribe</h4>
      <p>Get latest updates & offers</p>
      <input type="email" placeholder="Enter your email">
      <button type="button">Subscribe</button>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="footer-social">
      <i class="fa-brands fa-facebook"></i>
      <i class="fa-brands fa-instagram"></i>
      <i class="fa-brands fa-x-twitter"></i>
      <i class="fa-brands fa-youtube"></i>
      <i class="fa-brands fa-linkedin"></i>
    </div>

    <p>© 2026 MovieTime Entertainment Pvt. Ltd. All Rights Reserved.</p>
  </div>
</div>

<script>
  function openSidebar(){
    document.getElementById("sidebar").classList.add("open");
    document.getElementById("overlay").classList.add("show");
  }

  function closeSidebar(){
    document.getElementById("sidebar").classList.remove("open");
    document.getElementById("overlay").classList.remove("show");
  }

  function clearSearch() {
    document.getElementById("searchInput").value = "";
    document.getElementById("searchResults").style.display = "none";

    const genreFilter = document.getElementById("genreFilter");
    const languageFilter = document.getElementById("languageFilter");

    if (genreFilter) genreFilter.value = "";
    if (languageFilter) languageFilter.value = "";

    filterMovieGrid();
  }

  function handleSearch(event) {
    searchMovies();
    filterMovieGrid();

    if (event.key === "Enter") {
      goToFirstMatchedMovie();
    }
  }

  function searchMovies() {
    const input = document.getElementById("searchInput").value.toLowerCase().trim();
    const resultsBox = document.getElementById("searchResults");
    const movies = document.getElementsByClassName("movie");

    resultsBox.innerHTML = "";

    if (input === "") {
      resultsBox.style.display = "none";
      return;
    }

    let found = false;

    for (let i = 0; i < movies.length; i++) {
      const id = movies[i].dataset.id;
      const name = (movies[i].dataset.name || "").toLowerCase();
      const image = movies[i].dataset.image || "";
      const rating = movies[i].dataset.rating || "";
      const genre = movies[i].dataset.genre || "";
      const language = movies[i].dataset.language || "";

      if (
        name.includes(input) ||
        genre.toLowerCase().includes(input) ||
        language.toLowerCase().includes(input)
      ) {
        found = true;

        const item = document.createElement("div");
        item.classList.add("search-item");

        item.innerHTML = `
          <img src="${image}" alt="${name}">
          <div>
            <strong style="color:#fff;">${movies[i].dataset.name}</strong>
            <p style="margin:4px 0 0;font-size:13px;color:#9aa3b8;">
              ⭐ ${rating} • ${genre} • ${language}
            </p>
          </div>
        `;

        item.onclick = function() {
          openSeatSelection(id);
        };

        resultsBox.appendChild(item);
      }
    }

    if (!found) {
      resultsBox.innerHTML = "<p style='padding:12px 14px;color:#9aa3b8;margin:0;'>No movie found</p>";
    }

    resultsBox.style.display = "block";
  }

  function filterMovieGrid() {
    const searchValue = document.getElementById("searchInput").value.toLowerCase().trim();
    const genreValue = document.getElementById("genreFilter").value.toLowerCase().trim();
    const languageValue = document.getElementById("languageFilter").value.toLowerCase().trim();
    const movies = document.getElementsByClassName("movie");

    for (let i = 0; i < movies.length; i++) {
      const name = (movies[i].dataset.name || "").toLowerCase();
      const genre = (movies[i].dataset.genre || "").toLowerCase();
      const language = (movies[i].dataset.language || "").toLowerCase();

      const matchesSearch =
        searchValue === "" ||
        name.includes(searchValue) ||
        genre.includes(searchValue) ||
        language.includes(searchValue);

      const matchesGenre =
        genreValue === "" || genre === genreValue;

      const matchesLanguage =
        languageValue === "" || language === languageValue;

      if (matchesSearch && matchesGenre && matchesLanguage) {
        movies[i].classList.remove("hidden");
      } else {
        movies[i].classList.add("hidden");
      }
    }
  }

  function goToFirstMatchedMovie() {
    const visibleMovie = document.querySelector(".movie:not(.hidden)");
    if (visibleMovie) {
      visibleMovie.scrollIntoView({ behavior: "smooth", block: "center" });
      visibleMovie.style.border = "3px solid #f84464";

      setTimeout(() => {
        visibleMovie.style.border = "";
      }, 1500);
    } else {
      alert("No matching movie found!");
    }
  }

  function openSeatSelection(movieId) {
    window.location.href = "seat.php?movie_id=" + movieId;
  }

  document.addEventListener("click", function(event){
    const searchBox = document.querySelector(".search-box");
    const resultsBox = document.getElementById("searchResults");

    if (searchBox && !searchBox.contains(event.target)) {
      resultsBox.style.display = "none";
    }
  });
</script>

</body>
</html>