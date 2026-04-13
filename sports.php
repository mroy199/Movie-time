<?php
session_start();
$pageTitle = "Sports - MovieTime";
include("includes/site_header.php");
?>

<div class="page-wrap">
  <div class="page-card">
    <h1>Sports</h1>
    <p style="color:#bbb;">Book tickets for cricket, football, badminton and other sports events here.</p>

    <div class="grid" style="margin-top:20px;">
      <div class="media-card">
        <img src="./assets/workshop-and-more-web-collection-202211140440.avif" alt="Sports">
        <h3>Cricket Events</h3>
        <p>Catch your favorite teams live in the stadium.</p>
      </div>
      <div class="media-card">
        <img src="./assets/kids-banner-desktop-collection-202503251132.avif" alt="Sports">
        <h3>Football Matches</h3>
        <p>Watch local and major football fixtures.</p>
      </div>
    </div>
  </div>
</div>

<?php include("includes/site_footer.php"); ?>