<?php
session_start();
include("config/db.php");
$pageTitle = "Movies - MovieTime";
include("includes/site_header.php");

$result = $conn->query("SELECT * FROM movies WHERE is_active = 1 ORDER BY id DESC");

function clean($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
  .page-wrap{
    max-width:1320px;
    margin:30px auto 60px;
    padding:0 20px;
  }

  .hero-box{
    position:relative;
    overflow:hidden;
    background:
      radial-gradient(900px 300px at 10% 0%, rgba(248,68,100,.18), transparent 60%),
      linear-gradient(180deg, rgba(20,20,28,.96), rgba(10,10,14,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:28px;
    padding:40px 34px;
    margin-bottom:28px;
    box-shadow:0 20px 45px rgba(0,0,0,.35);
  }

  .hero-box h1{
    margin:0 0 10px;
    font-size:46px;
    color:#fff;
    letter-spacing:-1px;
  }

  .hero-box p{
    margin:0;
    color:#bdbdc8;
    font-size:16px;
    line-height:1.7;
    max-width:760px;
  }

  .hero-actions{
    margin-top:22px;
    display:flex;
    gap:12px;
    flex-wrap:wrap;
  }

  .hero-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    padding:13px 20px;
    border-radius:14px;
    font-weight:800;
    transition:.2s ease;
  }

  .hero-btn.primary{
    background:linear-gradient(90deg, #f84464, #ff5b7d);
    color:#fff;
  }

  .hero-btn.secondary{
    background:rgba(255,255,255,.06);
    color:#fff;
    border:1px solid rgba(255,255,255,.10);
  }

  .hero-btn:hover{
    transform:translateY(-2px);
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
    color:#fff;
    letter-spacing:-.5px;
  }

  .section-head p{
    margin:8px 0 0;
    color:#a8adbb;
    font-size:15px;
  }

  .movie-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));
    gap:22px;
  }

  .movie-card{
    background:linear-gradient(180deg, rgba(18,19,26,.96), rgba(12,13,18,.96));
    border:1px solid rgba(255,255,255,.08);
    border-radius:22px;
    overflow:hidden;
    box-shadow:0 14px 30px rgba(0,0,0,.28);
    transition:.2s ease;
  }

  .movie-card:hover{
    transform:translateY(-6px);
    box-shadow:0 20px 40px rgba(0,0,0,.38);
  }

  .movie-card img{
    width:100%;
    height:340px;
    object-fit:cover;
    display:block;
    background:#1d1d24;
  }

  .movie-body{
    padding:18px;
  }

  .movie-body h3{
    margin:0 0 10px;
    font-size:24px;
    color:#fff;
  }

  .rating-row{
    margin-bottom:10px;
    color:#f8d8de;
    font-weight:700;
    font-size:14px;
  }

  .meta{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:14px;
  }

  .pill{
    padding:7px 12px;
    border-radius:999px;
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.08);
    color:#d9d9e5;
    font-size:13px;
    font-weight:600;
  }

  .movie-desc{
    color:#aeb4c2;
    font-size:14px;
    line-height:1.6;
    min-height:44px;
  }

  .btn-row{
    display:flex;
    gap:10px;
    margin-top:16px;
  }

  .btn-page{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    padding:12px 16px;
    border-radius:14px;
    font-weight:800;
    transition:.2s ease;
    flex:1;
  }

  .btn-page.primary{
    background:linear-gradient(90deg, #f84464, #ff5b7d);
    color:#fff;
  }

  .btn-page.secondary{
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.08);
    color:#fff;
  }

  .btn-page:hover{
    filter:brightness(1.04);
    transform:translateY(-1px);
  }

  .empty-box{
    padding:40px 20px;
    text-align:center;
    color:#bbb;
    background:#111;
    border-radius:20px;
    border:1px solid rgba(255,255,255,.08);
  }

  @media (max-width: 760px){
    .page-wrap{
      padding:0 14px;
    }

    .hero-box{
      padding:28px 22px;
    }

    .hero-box h1{
      font-size:34px;
    }

    .section-head h2{
      font-size:28px;
    }

    .movie-card img{
      height:300px;
    }

    .btn-row{
      flex-direction:column;
    }
  }
</style>

<div class="page-wrap">
  <div class="hero-box">
    <h1>Now Showing Movies</h1>
    <p>Discover trending films, explore genres, and book your favorite shows with a clean, premium MovieTime experience.</p>

    <div class="hero-actions">
      <a href="show.php" class="hero-btn secondary">← Back to Home</a>
      <a href="#movie-list" class="hero-btn primary">Browse All Movies</a>
    </div>
  </div>

  <div class="section-head" id="movie-list">
    <div>
      <h2>All Active Movies</h2>
      <p>Choose a movie to view details or book tickets instantly.</p>
    </div>
  </div>

  <?php if ($result && $result->num_rows > 0): ?>
    <div class="movie-grid">
      <?php while($m = $result->fetch_assoc()): ?>
        <?php
          $movieId = (int)$m["id"];
          $title = $m["title"] ?? "Untitled";
          $image = !empty($m["image"]) ? $m["image"] : "./assets/image.png";
          $language = $m["language"] ?? "Language N/A";
          $genre = $m["genre"] ?? "Genre N/A";
          $rating = $m["rating"] ?? "";
          $duration = $m["duration"] ?? "";
          $description = $m["description"] ?? "";
        ?>
        <div class="movie-card">
          <img src="<?= clean($image) ?>" alt="<?= clean($title) ?>">

          <div class="movie-body">
            <?php if ($rating !== ""): ?>
              <div class="rating-row">⭐ <?= clean($rating) ?></div>
            <?php endif; ?>

            <h3><?= clean($title) ?></h3>

            <div class="meta">
              <span class="pill"><?= clean($language) ?></span>
              <span class="pill"><?= clean($genre) ?></span>
              <?php if ($duration !== ""): ?>
                <span class="pill"><?= clean($duration) ?></span>
              <?php endif; ?>
            </div>

            <div class="movie-desc">
              <?= clean($description !== "" ? mb_strimwidth($description, 0, 85, "...") : "Book tickets and enjoy the latest entertainment experience with MovieTime.") ?>
            </div>

            <div class="btn-row">
              <a class="btn-page secondary" href="movie_details.php?id=<?= $movieId ?>">View Details</a>
              <a class="btn-page primary" href="seat.php?movie_id=<?= $movieId ?>">Book Now</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="empty-box">No movies available right now.</div>
  <?php endif; ?>
</div>

<?php include("includes/site_footer.php"); ?>