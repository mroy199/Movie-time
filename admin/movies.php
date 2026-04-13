<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

// ================= DELETE =================
if(isset($_GET['delete'])){
  $id = (int)$_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM movies WHERE id=?");
  $stmt->bind_param("i",$id);
  $stmt->execute();
  header("Location: movies.php");
  exit;
}

// ================= ADD MOVIE =================
if(isset($_POST['add_movie'])){
  $title = $_POST['title'];
  $genre = $_POST['genre'];
  $rating = $_POST['rating'];
  $duration = $_POST['duration'];
  $language = $_POST['language'];
  $description = $_POST['description'];

  $imageName = $_FILES['image']['name'];
  $tmp = $_FILES['image']['tmp_name'];
  $path = "../uploads/" . time() . "_" . $imageName;
  move_uploaded_file($tmp, $path);

  $stmt = $conn->prepare("INSERT INTO movies (title,image,genre,rating,duration,language,description) VALUES (?,?,?,?,?,?,?)");
  $stmt->bind_param("sssisss",$title,$path,$genre,$rating,$duration,$language,$description);
  $stmt->execute();

  header("Location: movies.php");
  exit;
}

// ================= FETCH =================
$result = $conn->query("SELECT * FROM movies ORDER BY id DESC");
?>

<?php include "layout.php"; ?>

<div class="header">
  <h1>🎬 Movies Management</h1>
</div>

<!-- ADD MOVIE -->
<div class="card">
<h3>Add Movie</h3>

<form method="POST" enctype="multipart/form-data">
<input type="text" name="title" placeholder="Title" required>
<input type="file" name="image" required>
<input type="text" name="genre" placeholder="Genre" required>
<input type="text" name="rating" placeholder="Rating">
<input type="text" name="duration" placeholder="Duration">
<input type="text" name="language" placeholder="Language">
<textarea name="description" placeholder="Description"></textarea>

<button name="add_movie">Add Movie</button>
</form>
</div>

<!-- MOVIE LIST -->
<div class="card">
<h3>All Movies</h3>

<table>
<tr>
<th>ID</th>
<th>Poster</th>
<th>Title</th>
<th>Genre</th>
<th>Rating</th>
<th>Action</th>
</tr>

<?php while($m = $result->fetch_assoc()): ?>
<tr>
<td><?= $m["id"] ?></td>

<td>
<?php if(!empty($m["image"])): ?>
<img src="<?= $m["image"] ?>">
<?php endif; ?>
</td>

<td><?= htmlspecialchars($m["title"]) ?></td>

<td>
<span class="badge">
<?= htmlspecialchars($m["genre"]) ?>
</span>
</td>

<td>⭐ <?= htmlspecialchars($m["rating"]) ?></td>

<td class="actions">
<a class="edit" href="edit_movie.php?id=<?= $m["id"] ?>">Edit</a>
<a class="delete" href="?delete=<?= $m["id"] ?>" onclick="return confirm('Delete this movie?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div> <!-- main from layout -->
</body>
</html>