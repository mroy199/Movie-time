<?php
session_start();
include("config/db.php");

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    die("Invalid movie ID.");
}

$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    die("Movie not found.");
}

$pageTitle = ($movie["title"] ?? "Movie") . " - MovieTime";
include("includes/site_header.php");

$image = !empty($movie["image"]) ? $movie["image"] : "./assets/image.png";
?>
<style>
.details-wrap{max-width:1200px;margin:30px auto;padding:0 20px}
.details-card{
  display:grid;
  grid-template-columns:320px 1fr;
  gap:30px;
  background:#111;
  border:1px solid #222;
  border-radius:18px;
  overflow:hidden;
  box-shadow:0 10px 30px rgba(0,0,0,.35);
}
.details-poster img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
}
.details-content{
  padding:30px;
}
.details-content h1{
  margin:0 0 12px 0;
  font-size:36px;
}
.meta{
  display:flex;
  flex-wrap:wrap;
  gap:12px;
  margin:14px 0 18px;
}
.meta span{
  background:#1c1c1c;
  border:1px solid #2a2a2a;
  padding:8px 12px;
  border-radius:999px;
  color:#ddd;
  font-size:14px;
}
.rating-box{
  display:inline-flex;
  align-items:center;
  gap:8px;
  background:#1a1a1a;
  border:1px solid #2a2a2a;
  border-radius:14px;
  padding:12px 16px;
  margin-bottom:18px;
  font-weight:700;
}
.desc{
  color:#cfcfcf;
  line-height:1.8;
  margin-top:15px;
}
.btn-row{
  display:flex;
  gap:12px;
  flex-wrap:wrap;
  margin-top:26px;
}
.primary-btn,.secondary-btn{
  padding:12px 20px;
  border:none;
  border-radius:10px;
  cursor:pointer;
  font-weight:700;
  text-decoration:none;
  display:inline-block;
}
.primary-btn{
  background:#f84464;
  color:#fff;
}
.secondary-btn{
  background:#1d1d1d;
  color:#fff;
  border:1px solid #333;
}
.primary-btn:hover{background:#d63350}
.secondary-btn:hover{background:#2a2a2a}

@media (max-width: 768px){
  .details-card{grid-template-columns:1fr}
  .details-content{padding:20px}
  .details-content h1{font-size:28px}
}
</style>

<div class="details-wrap">
  <div class="details-card">
    <div class="details-poster">
      <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($movie["title"] ?? "Movie") ?>">
    </div>

    <div class="details-content">
      <h1><?= htmlspecialchars($movie["title"] ?? "Untitled Movie") ?></h1>

      <div class="rating-box">
        <i class="fa-solid fa-star" style="color:#fcb25d;"></i>
        <?= htmlspecialchars($movie["rating"] ?? "New Release") ?>
        <?php if(!empty($movie["votes"])): ?>
          <span style="color:#aaa;font-weight:400;">• <?= htmlspecialchars($movie["votes"]) ?></span>
        <?php endif; ?>
      </div>

      <div class="meta">
        <span><?= htmlspecialchars($movie["language"] ?? "Language N/A") ?></span>
        <span><?= htmlspecialchars($movie["duration"] ?? "Duration N/A") ?></span>
        <span><?= htmlspecialchars($movie["genre"] ?? "Genre N/A") ?></span>
      </div>

      <div class="desc">
        <?= nl2br(htmlspecialchars($movie["description"] ?? "No description available.")) ?>
      </div>

      <div class="btn-row">
        <button class="primary-btn" onclick="bookMovie(<?= (int)$movie['id'] ?>)">
          Book Now
        </button>

        <a href="show.php" class="secondary-btn">Back to Home</a>
      </div>
    </div>
  </div>
</div>

<script>
function bookMovie(movieId){
  window.location.href = "seat.php?movie_id=" + movieId;
}
</script>

<?php include("includes/site_footer.php"); ?>