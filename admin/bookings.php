<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

// ================= ADD BOOKING =================
if(isset($_POST['add_booking'])){
  $user_id = $_POST['user_id'];
  $movie_id = $_POST['movie_id'];
  $seat = $_POST['seat_number'];
  $date = $_POST['show_date'];

  $stmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, seat_number, show_date) VALUES (?,?,?,?)");
  $stmt->bind_param("iiss",$user_id,$movie_id,$seat,$date);
  $stmt->execute();

  header("Location: bookings.php");
  exit;
}

// ================= DELETE =================
if(isset($_GET['delete'])){
  $id = (int)$_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM bookings WHERE id=?");
  $stmt->bind_param("i",$id);
  $stmt->execute();

  header("Location: bookings.php");
  exit;
}

// ================= FETCH =================
$users = $conn->query("SELECT id, fullname FROM users");
$movies = $conn->query("SELECT id, title FROM movies");

$result = $conn->query("
SELECT bookings.*, users.fullname, movies.title
FROM bookings
JOIN users ON bookings.user_id = users.id
JOIN movies ON bookings.movie_id = movies.id
ORDER BY bookings.id DESC
");
?>

<?php include "layout.php"; ?>

<div class="header">
  <h1>🎟 Bookings Management</h1>
</div>

<!-- ADD BOOKING -->
<div class="card">
<h3>Add Booking</h3>

<form method="POST">

<select name="user_id" required>
<option value="">Select User</option>
<?php while($u = $users->fetch_assoc()): ?>
<option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['fullname']) ?></option>
<?php endwhile; ?>
</select>

<select name="movie_id" required>
<option value="">Select Movie</option>
<?php while($m = $movies->fetch_assoc()): ?>
<option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
<?php endwhile; ?>
</select>

<input type="text" name="seat_number" placeholder="Seat Number (A1, B2)" required>
<input type="date" name="show_date" required>

<button name="add_booking">Add Booking</button>

</form>
</div>

<!-- BOOKINGS LIST -->
<div class="card">
<h3>All Bookings</h3>

<table>
<tr>
<th>ID</th>
<th>User</th>
<th>Movie</th>
<th>Seat</th>
<th>Date</th>
<th>Action</th>
</tr>

<?php while($b = $result->fetch_assoc()): ?>
<tr>
<td><?= $b["id"] ?></td>
<td><?= htmlspecialchars($b["fullname"]) ?></td>
<td><?= htmlspecialchars($b["title"]) ?></td>
<td><?= htmlspecialchars($b["seat_number"]) ?></td>
<td><?= $b["show_date"] ?></td>

<td class="actions">
<a class="delete" href="?delete=<?= $b["id"] ?>" onclick="return confirm('Delete this booking?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div> <!-- main -->
</body>
</html>