<?php
session_start();
include("config/db.php");
$pageTitle = "Movies - MovieTime";
include("includes/site_header.php");

$result = $conn->query("SELECT * FROM movies WHERE is_active = 1 ORDER BY id DESC");
?>

<div class="page-wrap">
  <div class="page-card">
    <h1>All Movies</h1>
    <div class="grid">
      <?php while($m = $result->fetch_assoc()): ?>
        <div class="media-card">
          <img src="<?= htmlspecialchars(!empty($m["image"]) ? $m["image"] : "./assets/image.png") ?>" alt="<?= htmlspecialchars($m["title"]) ?>">
          <h3><?= htmlspecialchars($m["title"]) ?></h3>
          <p><?= htmlspecialchars($m["language"] ?? "Language N/A") ?></p>
          <p><?= htmlspecialchars($m["genre"] ?? "Genre N/A") ?></p>
          <a class="btn-page" href="movie_details.php?id=<?= (int)$m["id"] ?>">View Details</a>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>

<?php include("includes/site_footer.php"); ?>