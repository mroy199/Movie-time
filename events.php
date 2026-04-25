<?php
session_start();
$pageTitle = "Events - MovieTime";
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
      radial-gradient(800px 300px at 15% 0%, rgba(248,68,100,.18), transparent 60%),
      linear-gradient(180deg, rgba(20,20,28,.96), rgba(10,10,14,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:28px;
    padding:34px 30px;
    margin-bottom:28px;
    box-shadow:0 20px 45px rgba(0,0,0,.35);
  }

  .hero-box h1{margin:0 0 10px;color:#fff;font-size:42px}
  .hero-box p{margin:0;color:#bdbdc8;font-size:16px;line-height:1.6;max-width:760px}

  .event-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));
    gap:22px;
  }

  .event-card{
    background:linear-gradient(180deg, rgba(18,18,24,.96), rgba(12,12,16,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 14px 30px rgba(0,0,0,.28);
    transition:.2s ease;
  }

  .event-card:hover{transform:translateY(-5px)}
  .event-card img{width:100%;height:280px;object-fit:cover;display:block}
  .event-body{padding:20px}
  .event-body h3{margin:0 0 10px;color:#fff;font-size:24px}
  .event-body p{margin:0;color:#bbb;line-height:1.6}
</style>

<div class="page-wrap">
  <div class="hero-box">
    <h1>Live Events</h1>
    <p>Find comedy shows, music performances, theatre, workshops, and more exciting live experiences near you.</p>
  </div>

  <div class="event-grid">
    <div class="event-card">
      <img src="./assets/comedy-shows-collection-202211140440.avif" alt="Comedy Shows">
      <div class="event-body">
        <h3>Comedy Shows</h3>
        <p>Laugh out loud with stand-up performances and trending comedy events.</p>
      </div>
    </div>

    <div class="event-card">
      <img src="./assets/music-shows-collection-202211140440.avif" alt="Music Shows">
      <div class="event-body">
        <h3>Music Shows</h3>
        <p>Book tickets for concerts, live music nights, and energetic performances.</p>
      </div>
    </div>

    <div class="event-card">
      <img src="./assets/theatre-shows-collection-202211140440.avif" alt="Theatre">
      <div class="event-body">
        <h3>Theatre</h3>
        <p>Experience stage plays, dramatic performances, and cultural art shows.</p>
      </div>
    </div>
  </div>
</div>

<?php include("includes/site_footer.php"); ?>