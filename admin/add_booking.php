<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

// FETCH USERS & MOVIES
$users = $conn->query("SELECT id, fullname FROM users");
$movies = $conn->query("SELECT id, title FROM movies");

// INSERT
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $movie_id = $_POST['movie_id'];
    $seat = $_POST['seat'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $amount = $_POST['amount'];

    $conn->query("
        INSERT INTO bookings (user_id, movie_id, seat_number, show_date, show_time, amount, payment_status)
        VALUES ('$user_id','$movie_id','$seat','$date','$time','$amount','Paid')
    ");

    header("Location: bookings.php");
    exit;
}
?>

<h1>Add Booking</h1>

<form method="POST">

<select name="user_id">
<?php while($u = $users->fetch_assoc()): ?>
<option value="<?= $u['id'] ?>"><?= $u['fullname'] ?></option>
<?php endwhile; ?>
</select>

<select name="movie_id">
<?php while($m = $movies->fetch_assoc()): ?>
<option value="<?= $m['id'] ?>"><?= $m['title'] ?></option>
<?php endwhile; ?>
</select>

<input type="text" name="seat" placeholder="Seat" required>
<input type="date" name="date" required>
<input type="time" name="time" required>
<input type="number" name="amount" required>

<button type="submit">Add Booking</button>

</form>

<a href="bookings.php">Back</a>