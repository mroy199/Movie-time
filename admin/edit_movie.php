<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

$id = (int)($_GET["id"] ?? 0);
$err = "";

if ($id <= 0) {
  die("Invalid movie ID.");
}

/* Get movie data */
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
  die("Movie not found.");
}

/* Update movie */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = trim($_POST["title"] ?? "");
  $image = trim($_POST["poster"] ?? ""); // form field kept as poster, DB column is image
  $rating = trim($_POST["rating"] ?? "");
  $votes = trim($_POST["votes"] ?? "");
  $language = trim($_POST["language"] ?? "");
  $duration = trim($_POST["duration"] ?? "");
  $genre = trim($_POST["genre"] ?? "");
  $description = trim($_POST["description"] ?? "");

  if ($title === "") {
    $err = "Movie title is required.";
  } else {
    $stmt = $conn->prepare("
      UPDATE movies
      SET title = ?, image = ?, rating = ?, votes = ?, language = ?, duration = ?, genre = ?, description = ?
      WHERE id = ?
    ");
    $stmt->bind_param("ssssssssi", $title, $image, $rating, $votes, $language, $duration, $genre, $description, $id);

    if ($stmt->execute()) {
      header("Location: movies.php");
      exit;
    } else {
      $err = "Failed to update movie.";
    }
  }
}

$currentImage = !empty($movie["image"]) ? $movie["image"] : "../assets/image.png";
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Movie</title>
  <link rel="stylesheet" href="/movie.css">
  <style>
    body{background:#000;color:#fff}
    .wrap{max-width:950px;margin:35px auto;padding:0 15px}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:12px;flex-wrap:wrap}
    .btn{background:#f84464;color:#fff;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:700;border:none;cursor:pointer}
    .card{background:#111;border:1px solid #222;border-radius:14px;padding:18px}
    input,textarea{width:100%;padding:10px;border-radius:10px;border:none;background:#222;color:#fff;margin-top:8px}
    textarea{min-height:100px}
    .err{background:#2a0f14;border:1px solid #f84464;color:#fff;padding:10px;border-radius:10px;margin-bottom:10px}
    .poster-preview{
      width:140px;
      height:200px;
      object-fit:cover;
      border-radius:12px;
      border:1px solid #333;
      background:#222;
      margin-bottom:16px;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <h1>Edit Movie</h1>
      <div>
        <a class="btn" href="movies.php">Back</a>
        <a class="btn" href="../show.php" style="margin-left:8px;">Go Site</a>
      </div>
    </div>

    <div class="card">
      <?php if($err): ?>
        <div class="err"><?= htmlspecialchars($err) ?></div>
      <?php endif; ?>

      <img class="poster-preview" src="<?= htmlspecialchars($currentImage) ?>" alt="<?= htmlspecialchars($movie["title"] ?? "Movie") ?>">

      <form method="POST">
        <label>Movie Title</label>
        <input name="title" value="<?= htmlspecialchars($movie["title"] ?? "") ?>" required>

        <label style="display:block;margin-top:10px;">Poster URL</label>
        <input name="poster" value="<?= htmlspecialchars($movie["image"] ?? "") ?>">

        <label style="display:block;margin-top:10px;">Rating</label>
        <input name="rating" value="<?= htmlspecialchars($movie["rating"] ?? "") ?>">

        <label style="display:block;margin-top:10px;">Votes</label>
        <input name="votes" value="<?= htmlspecialchars($movie["votes"] ?? "") ?>">

        <label style="display:block;margin-top:10px;">Language</label>
        <input name="language" value="<?= htmlspecialchars($movie["language"] ?? "") ?>">

        <label style="display:block;margin-top:10px;">Duration</label>
        <input name="duration" value="<?= htmlspecialchars($movie["duration"] ?? "") ?>">

        <label style="display:block;margin-top:10px;">Genre</label>
        <input name="genre" value="<?= htmlspecialchars($movie["genre"] ?? "") ?>">

        <label style="display:block;margin-top:10px;">Description</label>
        <textarea name="description"><?= htmlspecialchars($movie["description"] ?? "") ?></textarea>

        <button class="btn" style="margin-top:12px;" type="submit">Update Movie</button>
      </form>
    </div>
  </div>
</body>
</html>