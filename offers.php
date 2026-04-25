<?php
session_start();
$pageTitle = "Offers - MovieTime";
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
      radial-gradient(800px 300px at 10% 0%, rgba(250,204,21,.15), transparent 60%),
      linear-gradient(180deg, rgba(20,20,28,.96), rgba(10,10,14,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:28px;
    padding:34px 30px;
    margin-bottom:28px;
    box-shadow:0 20px 45px rgba(0,0,0,.35);
  }

  .hero-box h1{margin:0 0 10px;color:#fff;font-size:42px}
  .hero-box p{margin:0;color:#bdbdc8;font-size:16px;line-height:1.6;max-width:760px}

  .offer-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));
    gap:22px;
  }

  .offer-card{
    background:linear-gradient(180deg, rgba(18,18,24,.96), rgba(12,12,16,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:24px;
    padding:24px;
    box-shadow:0 14px 30px rgba(0,0,0,.28);
    transition:.2s ease;
  }

  .offer-card:hover{transform:translateY(-5px)}
  .offer-tag{
    display:inline-block;
    padding:7px 12px;
    border-radius:999px;
    background:rgba(248,68,100,.12);
    border:1px solid rgba(248,68,100,.22);
    color:#ff8ba1;
    font-size:13px;
    font-weight:700;
    margin-bottom:14px;
  }

  .offer-card h3{margin:0 0 10px;color:#fff;font-size:26px}
  .offer-card p{margin:0;color:#bbb;line-height:1.6}
</style>

<div class="page-wrap">
  <div class="hero-box">
    <h1>Offers & Deals</h1>
    <p>Explore the latest discounts, cashback deals, and special booking promotions on MovieTime.</p>
  </div>

  <div class="offer-grid">
    <div class="offer-card">
      <span class="offer-tag">New User</span>
      <h3>10% OFF</h3>
      <p>Get 10% off on your first movie booking and enjoy your first experience with MovieTime.</p>
    </div>

    <div class="offer-card">
      <span class="offer-tag">Bank Offer</span>
      <h3>Instant Cashback</h3>
      <p>Use selected bank cards and get instant cashback on movie and event bookings.</p>
    </div>

    <div class="offer-card">
      <span class="offer-tag">Weekend Deal</span>
      <h3>Combo Discount</h3>
      <p>Book two or more tickets and unlock combo offers for weekends and special shows.</p>
    </div>
  </div>
</div>

<?php include("includes/site_footer.php"); ?>