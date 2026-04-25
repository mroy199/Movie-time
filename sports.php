<?php
session_start();
$pageTitle = "Sports - MovieTime";
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
      radial-gradient(800px 300px at 85% 0%, rgba(34,197,94,.16), transparent 60%),
      linear-gradient(180deg, rgba(20,20,28,.96), rgba(10,10,14,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:28px;
    padding:34px 30px;
    margin-bottom:28px;
    box-shadow:0 20px 45px rgba(0,0,0,.35);
  }

  .hero-box h1{margin:0 0 10px;color:#fff;font-size:42px}
  .hero-box p{margin:0;color:#bdbdc8;font-size:16px;line-height:1.6;max-width:760px}

  .sports-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));
    gap:22px;
  }

  .sports-card{
    background:linear-gradient(180deg, rgba(18,18,24,.96), rgba(12,12,16,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 14px 30px rgba(0,0,0,.28);
    transition:.2s ease;
  }

  .sports-card:hover{transform:translateY(-5px)}
  .sports-card img{width:100%;height:260px;object-fit:cover;display:block}
  .sports-body{padding:20px}
  .sports-body h3{margin:0 0 10px;color:#fff;font-size:24px}
  .sports-body p{margin:0;color:#bbb;line-height:1.6}
</style>

<div class="page-wrap">
  <div class="hero-box">
    <h1>Sports</h1>
    <p>Book tickets for cricket, football, badminton, and other live sports experiences with a modern ticketing flow.</p>
  </div>

  <div class="sports-grid">
    <div class="sports-card">
      <img src="./assets/workshop-and-more-web-collection-202211140440.avif" alt="Cricket Events">
      <div class="sports-body">
        <h3>Cricket Events</h3>
        <p>Catch your favorite teams live in the stadium and enjoy the thrill of big matches.</p>
      </div>
    </div>

    <div class="sports-card">
      <img src="./assets/kids-banner-desktop-collection-202503251132.avif" alt="Football Matches">
      <div class="sports-body">
        <h3>Football Matches</h3>
        <p>Watch exciting football fixtures and experience live action with fans around you.</p>
      </div>
    </div>
  </div>
</div>

<?php include("includes/site_footer.php"); ?>