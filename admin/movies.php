<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

// ================= DELETE =================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: movies.php");
    exit;
}

// ================= ADD MOVIE =================
if (isset($_POST['add_movie'])) {
    $title = trim($_POST['title'] ?? '');
    $image = trim($_POST['image'] ?? ''); // URL now
    $genre = trim($_POST['genre'] ?? '');
    $rating = trim($_POST['rating'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $language = trim($_POST['language'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $stmt = $conn->prepare("INSERT INTO movies (title, image, genre, rating, duration, language, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $title, $image, $genre, $rating, $duration, $language, $description);
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

  <form method="POST">
    <input type="text" name="title" placeholder="Title" required>

    <input type="url" name="image" placeholder="Poster Image URL / Movie Link" required>

<input type="text" name="genre" placeholder="Genre" required>
    <input type="text" name="rating" placeholder="Rating">
    <input type="text" name="duration" placeholder="Duration">
    <input type="text" name="language" placeholder="Language">
    <textarea name="description" placeholder="Description"></textarea>

    <button type="submit" name="add_movie">Add Movie</button>
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

    <?php while ($m = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $m["id"] ?></td>

      <td>
        <?php if (!empty($m["image"])): ?>
          <img src="<?= htmlspecialchars($m["image"]) ?>" alt="Poster" style="width:70px;height:100px;object-fit:cover;border-radius:8px;">
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