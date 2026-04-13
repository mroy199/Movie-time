<?php
session_start();
$pageTitle = "Offers - MovieTime";
include("includes/site_header.php");
?>

<div class="page-wrap">
  <div class="page-card">
    <h1>Offers</h1>
    <p style="color:#bbb;">Explore the latest offers, discounts and cashback deals.</p>

    <div class="grid" style="margin-top:20px;">
      <div class="media-card">
        <h3>10% OFF</h3>
        <p>Get 10% off on your first movie booking.</p>
      </div>
      <div class="media-card">
        <h3>Bank Offer</h3>
        <p>Use selected cards and get instant cashback.</p>
      </div>
      <div class="media-card">
        <h3>Weekend Combo</h3>
        <p>Book 2 tickets and unlock special combo discounts.</p>
      </div>
    </div>
  </div>
</div>

<?php include("includes/site_footer.php"); ?>