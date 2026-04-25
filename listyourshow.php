<?php
session_start();
$pageTitle = "ListYourShow - MovieTime";
include("includes/site_header.php");
?>

<style>
  .page-wrap{
    max-width:1200px;
    margin:30px auto 60px;
    padding:0 20px;
  }

  .promo-box{
    background:
      radial-gradient(900px 350px at 85% 10%, rgba(248,68,100,.16), transparent 60%),
      linear-gradient(180deg, rgba(20,20,28,.96), rgba(10,10,14,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:30px;
    padding:44px 34px;
    box-shadow:0 20px 45px rgba(0,0,0,.35);
  }

  .promo-box h1{
    margin:0 0 14px;
    color:#fff;
    font-size:46px;
    line-height:1.05;
  }

  .promo-box p{
    margin:0;
    color:#bdbdc8;
    font-size:17px;
    line-height:1.7;
    max-width:760px;
  }

  .action-row{
    margin-top:24px;
    display:flex;
    gap:14px;
    flex-wrap:wrap;
  }

  .btn-main,
  .btn-alt{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:13px 22px;
    border-radius:14px;
    text-decoration:none;
    font-weight:800;
  }

  .btn-main{
    background:linear-gradient(90deg, #f84464, #ff5b7d);
    color:#fff;
  }

  .btn-alt{
    background:#1a1b22;
    border:1px solid rgba(255,255,255,.08);
    color:#fff;
  }
</style>

<div class="page-wrap">
  <div class="promo-box">
    <h1>List Your Show</h1>
    <p>Promote your event, partner with MovieTime, and reach more audiences through a modern entertainment platform built for visibility and easy bookings.</p>

    <div class="action-row">
      <a class="btn-main" href="#">Get Started</a>
      <a class="btn-alt" href="contact.php">Contact Us</a>
    </div>
  </div>
</div>

<?php include("includes/site_footer.php"); ?>