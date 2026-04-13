<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

// ================= COUNTS =================
$moviesCount = $conn->query("SELECT COUNT(*) c FROM movies")->fetch_assoc()['c'];
$usersCount = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$bookingsCount = $conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'];

// ================= DATA =================
$movieList = $conn->query("SELECT * FROM movies ORDER BY id DESC");
$usersList = $conn->query("SELECT * FROM users ORDER BY id DESC");
$bookingList = $conn->query("
SELECT bookings.*, users.fullname, movies.title
FROM bookings
JOIN users ON bookings.user_id = users.id
JOIN movies ON bookings.movie_id = movies.id
ORDER BY bookings.id DESC
");

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
  $stmt->bind_param("sssisss", $title,$path,$genre,$rating,$duration,$language,$description);
  $stmt->execute();

  header("Location: index.php");
  exit;
}
?>

<?php include "layout.php"; ?>

<!-- DASHBOARD -->
<div class="header">
  <h1>Dashboard</h1>
</div>

<div class="cards">
  <div class="card">
    <h3>Movies</h3>
    <h1><?= $moviesCount ?></h1>
  </div>
  <div class="card">
    <h3>Users</h3>
    <h1><?= $usersCount ?></h1>
  </div>
  <div class="card">
    <h3>Bookings</h3>
    <h1><?= $bookingsCount ?></h1>
  </div>
</div>

<!-- MOVIES -->
<div class="header">
  <h1>🎬 Movies Management</h1>
</div>

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

<div class="card">
<h3>All Movies</h3>

<table>
<tr>
<th>ID</th>
<th>Poster</th>
<th>Title</th>
<th>Genre</th>
<th>Rating</th>
</tr>

<?php while($m = $movieList->fetch_assoc()): ?>
<tr>
<td><?= $m['id'] ?></td>
<td><img src="<?= $m['image'] ?>"></td>
<td><?= htmlspecialchars($m['title']) ?></td>
<td><span class="badge"><?= htmlspecialchars($m['genre']) ?></span></td>
<td>⭐ <?= htmlspecialchars($m['rating']) ?></td>
</tr>
<?php endwhile; ?>

</table>
</div>

<!-- USERS -->
<div class="header">
  <h1>👤 Users</h1>
</div>

<div class="card">
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
</tr>

<?php while($u = $usersList->fetch_assoc()): ?>
<tr>
<td><?= $u['id'] ?></td>
<td><?= htmlspecialchars($u['fullname']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
</tr>
<?php endwhile; ?>

</table>
</div>

<!-- BOOKINGS -->
<div class="header">
  <h1>🎟 Bookings</h1>
</div>

<div class="card">
<table>
<tr>
<th>ID</th>
<th>User</th>
<th>Movie</th>
<th>Seat</th>
<th>Date</th>
</tr>

<?php while($b = $bookingList->fetch_assoc()): ?>
<tr>
<td><?= $b['id'] ?></td>
<td><?= htmlspecialchars($b['fullname']) ?></td>
<td><?= htmlspecialchars($b['title']) ?></td>
<td><?= htmlspecialchars($b['seat_number']) ?></td>
<td><?= $b['show_date'] ?></td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div> <!-- main from layout -->
</body>
</html>