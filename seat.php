<?php
session_start();
include("config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$movie_id = (int)($_GET["movie_id"] ?? 0);

if ($movie_id <= 0) {
    die("Invalid movie selected.");
}

/* Get movie details */
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    die("Movie not found.");
}

/* FIXED IMAGE */
$movieTitle = $movie["title"] ?? "Untitled Movie";
$movieImage = !empty($movie["image"]) ? $movie["image"] : "./assets/image.png";

/* Get booked seats WITH DATE + TIME FIX */
$bookedSeats = [];
$seatQuery = $conn->prepare("
  SELECT seat_number 
  FROM bookings 
  WHERE movie_id = ?
");
$seatQuery->bind_param("i", $movie_id);
$seatQuery->execute();
$seatResult = $seatQuery->get_result();

while ($row = $seatResult->fetch_assoc()) {
    $bookedSeats[] = $row["seat_number"];
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selectedSeats = $_POST["selected_seats"] ?? "";
    $show_date = trim($_POST["show_date"] ?? "");
    $show_time = trim($_POST["show_time"] ?? "");

    if ($selectedSeats === "") {
        $error = "Please select at least one seat.";
    } elseif ($show_date === "" || $show_time === "") {
        $error = "Please select date and time.";
    } else {
        $seatsArray = explode(",", $selectedSeats);

        $_SESSION["pending_booking"] = [
            "movie_id" => $movie_id,
            "selected_seats" => $seatsArray,
            "show_date" => $show_date,
            "show_time" => $show_time
        ];

        header("Location: payment.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seat Booking - <?= htmlspecialchars($movieTitle) ?></title>
<link rel="stylesheet" href="movie.css">

<style>
body{background:#000;color:#fff;font-family:Arial}
.seat-page{max-width:1000px;margin:30px auto;padding:20px}

.movie-box{
display:flex;gap:20px;background:#111;padding:20px;
border-radius:16px;margin-bottom:25px;flex-wrap:wrap
}
.movie-box img{width:180px;border-radius:12px}

.seats{
display:grid;
grid-template-columns:repeat(5,60px);
gap:15px;justify-content:center
}
.seat{
width:60px;height:45px;border:none;border-radius:8px;
background:#2c2c2c;color:#fff;cursor:pointer;font-weight:bold
}
.seat.selected{background:#4CAF50}
.seat.booked{background:#777;cursor:not-allowed}

.btn{
margin-top:20px;padding:12px 22px;
background:#f84464;border:none;border-radius:8px;
color:#fff;font-weight:700;cursor:pointer
}
</style>
</head>

<body>

<div class="seat-page">

<div class="movie-box">
<img src="<?= htmlspecialchars($movieImage) ?>">
<div>
<h1><?= htmlspecialchars($movieTitle) ?></h1>
<p>⭐ <?= htmlspecialchars($movie["rating"] ?? "New") ?></p>
<p><?= htmlspecialchars($movie["language"] ?? "") ?></p>
</div>
</div>

<form method="POST">

<div class="seats">
<?php
$allSeats = ["A1","A2","A3","A4","A5","B1","B2","B3","B4","B5","C1","C2","C3","C4","C5"];

foreach ($allSeats as $seat):
$isBooked = in_array($seat, $bookedSeats);
?>
<button type="button"
class="seat <?= $isBooked ? 'booked' : '' ?>"
data-seat="<?= $seat ?>"
<?= $isBooked ? 'disabled' : '' ?>>
<?= $seat ?>
</button>
<?php endforeach; ?>
</div>

<br>

<input type="date" name="show_date" required>
<select name="show_time" required>
<option value="">Select Time</option>
<option>10:00 AM</option>
<option>1:00 PM</option>
<option>4:00 PM</option>
<option>7:00 PM</option>
</select>

<input type="hidden" name="selected_seats" id="selectedSeats">

<p>Selected: <span id="seatList">None</span></p>

<button class="btn">Continue to Payment</button>

</form>
</div>

<script>
const selectedSeats = [];
const buttons = document.querySelectorAll(".seat:not(.booked)");
const list = document.getElementById("seatList");
const input = document.getElementById("selectedSeats");

buttons.forEach(btn=>{
btn.onclick=()=>{
const seat=btn.dataset.seat;

if(btn.classList.contains("selected")){
btn.classList.remove("selected");
selectedSeats.splice(selectedSeats.indexOf(seat),1);
}else{
btn.classList.add("selected");
selectedSeats.push(seat);
}

list.innerText = selectedSeats.length ? selectedSeats.join(", ") : "None";
input.value = selectedSeats.join(",");
};
});
</script>

</body>
</html>