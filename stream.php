<?php
session_start();
$pageTitle = "Stream - MovieTime";
include("includes/site_header.php");
?>

<style>
  .page-wrap{
    max-width:1300px;
    margin:30px auto 60px;
    padding:0 20px;
  }

  .hero-box{
    background:
      radial-gradient(800px 300px at 85% 0%, rgba(91,110,255,.18), transparent 60%),
      linear-gradient(180deg, rgba(20,20,28,.96), rgba(10,10,14,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:28px;
    padding:34px 30px;
    margin-bottom:28px;
    box-shadow:0 20px 45px rgba(0,0,0,.35);
  }

  .hero-box h1{margin:0 0 10px;color:#fff;font-size:42px}
  .hero-box p{margin:0;color:#bdbdc8;font-size:16px;line-height:1.6;max-width:760px}

  .content-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));
    gap:22px;
  }

  .feature-card{
    background:linear-gradient(180deg, rgba(18,18,24,.96), rgba(12,12,16,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 14px 30px rgba(0,0,0,.28);
    transition:.2s ease;
  }

  .feature-card:hover{transform:translateY(-5px)}
  .feature-card img{width:100%;height:260px;object-fit:cover;display:block}
  .feature-body{padding:20px}
  .feature-body h3{margin:0 0 10px;color:#fff;font-size:26px}
  .feature-body p{margin:0;color:#bbb;line-height:1.6}
</style>

<div class="page-wrap">
  <div class="hero-box">
    <h1>Stream</h1>
    <p>Watch exclusive content, featured trailers, and digital entertainment picks selected for MovieTime users.</p>
  </div>

  <div class="content-grid">
    <div class="feature-card">
      <img src="./assets/my banner.png" alt="Exclusive Trailers">
      <div class="feature-body">
        <h3>Exclusive Trailers</h3>
        <p>Watch trending trailers, teasers, and upcoming digital premieres in one place.</p>
      </div>
    </div>

    <div class="feature-card">
      <img src="./assets/toxic1.jpg" alt="Featured Content">
      <div class="feature-body">
        <h3>Featured Content</h3>
        <p>Explore premium entertainment picks and highlighted streaming content curated for you.</p>
      </div>
    </div>
  </div>
</div>

<?php include("includes/site_footer.php"); ?>