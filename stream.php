<?php
session_start();
$pageTitle = "Stream - MovieTime";
include("includes/site_header.php");
?>

<div class="page-wrap">
  <div class="page-card">
    <h1>Stream</h1>
    <p style="color:#bbb;">Watch exclusive online content, trailers and featured entertainment here.</p>

    <div class="grid" style="margin-top:20px;">
      <div class="media-card">
        <img src="./assets/my banner.png" alt="Stream">
        <h3>Exclusive Trailers</h3>
        <p>Watch trending trailers and digital premieres.</p>
      </div>
      <div class="media-card">
        <img src="./assets/toxic1.jpg" alt="Stream">
        <h3>Featured Content</h3>
        <p>Explore streaming picks curated for MovieTime users.</p>
      </div>
    </div>
  </div>
</div>

<?php include("includes/site_footer.php"); ?>