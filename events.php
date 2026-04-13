<?php
session_start();
$pageTitle = "Events - MovieTime";
include("includes/site_header.php");
?>

<div class="page-wrap">
  <div class="page-card">
    <h1>Events</h1>
    <p style="color:#bbb;">Find comedy shows, music shows, theatre, workshops and live events here.</p>

    <div class="grid" style="margin-top:20px;">
      <div class="media-card">
        <img src="./assets/comedy-shows-collection-202211140440.avif" alt="Comedy">
        <h3>Comedy Shows</h3>
        <p>Laugh out loud with live stand-up and comedy events.</p>
      </div>
      <div class="media-card">
        <img src="./assets/music-shows-collection-202211140440.avif" alt="Music">
        <h3>Music Shows</h3>
        <p>Book tickets for concerts and live performances.</p>
      </div>
      <div class="media-card">
        <img src="./assets/theatre-shows-collection-202211140440.avif" alt="Theatre">
        <h3>Theatre</h3>
        <p>Experience stage plays and cultural performances.</p>
      </div>
    </div>
  </div>
</div>

<?php include("includes/site_footer.php"); ?>