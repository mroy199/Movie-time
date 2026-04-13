<?php
session_start();
include("config/db.php");
require_once __DIR__ . "/includes/send_mail.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

if (!isset($_SESSION["pending_booking"])) {
    header("Location: show.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$pending = $_SESSION["pending_booking"];

$movie_id = (int)($pending["movie_id"] ?? 0);
$selectedSeats = $pending["selected_seats"] ?? [];
$show_date = $pending["show_date"] ?? "";
$show_time = $pending["show_time"] ?? "";

if ($movie_id <= 0 || empty($selectedSeats)) {
    unset($_SESSION["pending_booking"]);
    die("Invalid booking session.");
}

$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie) {
    die("Movie not found.");
}

$movieTitle = $movie["title"] ?? "Untitled Movie";
$movieImage = !empty($movie["image"]) ? $movie["image"] : "./assets/image.png";

$ticketPrice = 200;
$totalAmount = count($selectedSeats) * $ticketPrice;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payment_method = trim($_POST["payment_method"] ?? "");

    if ($payment_method === "") {
        $error = "Please select a payment method.";
    } else {
        foreach ($selectedSeats as $seat) {
            $seat = trim($seat);

            $check = $conn->prepare("
                SELECT id 
                FROM bookings 
                WHERE movie_id = ? AND seat_number = ? AND show_date = ? AND show_time = ?
            ");
            $check->bind_param("isss", $movie_id, $seat, $show_date, $show_time);
            $check->execute();
            $checkResult = $check->get_result();

            if ($checkResult->num_rows > 0) {
                $error = "Seat $seat is already booked.";
                break;
            }
        }

        if ($error === "") {
            $bookingIds = [];

            foreach ($selectedSeats as $seat) {
                $seat = trim($seat);

                // store price per seat
                $insert = $conn->prepare("
                    INSERT INTO bookings 
                    (user_id, movie_id, seat_number, show_date, show_time, total_amount, payment_status, payment_method) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Paid', ?)
                ");
                $insert->bind_param(
                    "iisssds",
                    $user_id,
                    $movie_id,
                    $seat,
                    $show_date,
                    $show_time,
                    $ticketPrice,
                    $payment_method
                );
                $insert->execute();

                $bookingIds[] = $conn->insert_id;
            }

          // ================= EMAIL CODE START =================

$userStmt = $conn->prepare("SELECT fullname, email FROM users WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

if (!empty($bookingIds) && !empty($user["email"])) {

    $placeholders = implode(",", array_fill(0, count($bookingIds), "?"));
    $types = str_repeat("i", count($bookingIds));

    $sql = "
        SELECT bookings.*, movies.title
        FROM bookings
        JOIN movies ON bookings.movie_id = movies.id
        WHERE bookings.id IN ($placeholders)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$bookingIds);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
    }

    if ($rows) {
        $movieTitle = $rows[0]["title"];
        $seatNumbers = [];
        $totalAmount = 0;

        foreach ($rows as $r) {
            $seatNumbers[] = $r["seat_number"];
            $totalAmount += $r["total_amount"];
        }

        $subject = "MovieTime Booking Confirmed 🎟️";

        $htmlBody = "
        <div style='font-family:Arial;background:#111;color:#fff;padding:20px;border-radius:12px'>
            <h2 style='color:#f84464'>Booking Confirmed</h2>
            <p>Hello {$user["fullname"]},</p>
            <p>Your booking is successful!</p>

            <div style='background:#1a1a1a;padding:15px;border-radius:10px;margin-top:10px'>
                <p><b>Movie:</b> {$movieTitle}</p>
                <p><b>Seats:</b> " . implode(", ", $seatNumbers) . "</p>
                <p><b>Date:</b> {$show_date}</p>
                <p><b>Time:</b> {$show_time}</p>
                <p><b>Total Paid:</b> ₹" . number_format($totalAmount, 2) . "</p>
                <p><b>Status:</b> Paid</p>
            </div>

            <p style='margin-top:15px'>Enjoy your movie 🎬</p>
        </div>
        ";

        sendMovieTimeMail(
            $user["email"],
            $user["fullname"],
            $subject,
            $htmlBody
        );
    }
}

// ================= EMAIL CODE END =================

// redirect
$_SESSION["last_booking_ids"] = $bookingIds;
unset($_SESSION["pending_booking"]);

header("Location: receipt.php");
exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment - MovieTime</title>
  <link rel="stylesheet" href="movie.css">
  <style>
    body{background:#000;color:#fff;font-family:Arial,sans-serif}
    .wrap{max-width:1000px;margin:30px auto;padding:20px}
    .box{
      display:grid;
      grid-template-columns:320px 1fr;
      gap:20px;
      background:#111;
      border:1px solid #222;
      border-radius:16px;
      padding:20px;
    }
    .poster img{
      width:100%;
      height:100%;
      max-height:460px;
      object-fit:cover;
      border-radius:12px;
      background:#222;
    }
    .details h1{margin-top:0}
    .details p{color:#ccc;margin:8px 0}
    .pay-box{
      margin-top:20px;
      background:#151515;
      border:1px solid #222;
      border-radius:14px;
      padding:20px;
    }
    .pay-box select{
      width:100%;
      padding:12px;
      border:none;
      border-radius:8px;
      background:#222;
      color:#fff;
      margin-top:10px;
    }
    .btn{
      margin-top:20px;
      background:#f84464;
      color:#fff;
      border:none;
      border-radius:8px;
      padding:12px 20px;
      cursor:pointer;
      font-weight:700;
      text-decoration:none;
      display:inline-block;
    }
    .err{
      background:#331218;
      color:#ff9ab0;
      border:1px solid #5c2431;
      padding:12px;
      border-radius:10px;
      margin-bottom:15px;
    }
    .muted{color:#aaa}
    @media (max-width:768px){
      .box{grid-template-columns:1fr}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="box">
      <div class="poster">
        <img src="<?= htmlspecialchars($movieImage) ?>" alt="<?= htmlspecialchars($movieTitle) ?>">
      </div>

      <div class="details">
        <h1><?= htmlspecialchars($movieTitle) ?></h1>
        <p>Seats: <?= htmlspecialchars(implode(", ", $selectedSeats)) ?></p>
        <p>Date: <?= htmlspecialchars($show_date ?: "N/A") ?></p>
        <p>Time: <?= htmlspecialchars($show_time ?: "N/A") ?></p>
        <p>Ticket Price (per seat): ₹<?= number_format($ticketPrice, 2) ?></p>
        <p><strong>Total Amount: ₹<?= number_format($totalAmount, 2) ?></strong></p>
        <p class="muted">Selected seats: <?= count($selectedSeats) ?></p>

        <div class="pay-box">
          <?php if ($error): ?>
            <div class="err"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST">
            <label>Select Payment Method</label>
            <select name="payment_method" required>
              <option value="">Choose one</option>
              <option value="UPI">UPI</option>
              <option value="Card">Credit/Debit Card</option>
              <option value="Net Banking">Net Banking</option>
              <option value="Cash">Cash</option>
            </select>

            <button type="submit" class="btn">Pay Now</button>
            <a href="show.php" class="btn" style="background:#222;">Cancel</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>