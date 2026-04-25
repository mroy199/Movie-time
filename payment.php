<?php
session_start();
include("config/db.php");
require_once("config/razorpay.php");
require_once __DIR__ . "/vendor/autoload.php";

require_once("config/razorpay.php");

use Razorpay\Api\Api;

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
$show_date = trim($pending["show_date"] ?? "");
$show_time = trim($pending["show_time"] ?? "");

if ($movie_id <= 0 || empty($selectedSeats) || $show_date === "" || $show_time === "") {
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

$userStmt = $conn->prepare("SELECT fullname, email, mobile FROM users WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

$movieTitle = $movie["title"] ?? "Untitled Movie";
$movieImage = !empty($movie["image"]) ? $movie["image"] : "./assets/image.png";

$ticketPrice = isset($movie["price"]) && (float)$movie["price"] > 0 ? (float)$movie["price"] : 200;
$totalAmount = count($selectedSeats) * $ticketPrice;
$totalAmountPaise = (int) round($totalAmount * 100);

$error = "";
$razorpayOrderId = "";

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
    try {
        $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

        $orderData = [
            'receipt'         => 'movietime_' . time() . '_' . $user_id,
            'amount'          => $totalAmountPaise,
            'currency'        => 'INR',
            'payment_capture' => 1
        ];

        $razorpayOrder = $api->order->create($orderData);
        $razorpayOrderId = $razorpayOrder['id'];

        $_SESSION["razorpay_checkout"] = [
            "order_id" => $razorpayOrderId,
            "amount" => $totalAmount,
            "movie_id" => $movie_id,
            "selected_seats" => $selectedSeats,
            "show_date" => $show_date,
            "show_time" => $show_time
        ];
    } catch (Exception $e) {
        $error = "Unable to create payment order. " . $e->getMessage();
    }
}

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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
    .btn.secondary{background:#222}
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
        <img src="<?= clean($movieImage) ?>" alt="<?= clean($movieTitle) ?>">
      </div>

      <div class="details">
        <h1><?= clean($movieTitle) ?></h1>
        <p>Seats: <?= clean(implode(", ", $selectedSeats)) ?></p>
        <p>Date: <?= clean($show_date) ?></p>
        <p>Time: <?= clean($show_time) ?></p>
        <p>Ticket Price (per seat): ₹<?= number_format($ticketPrice, 2) ?></p>
        <p><strong>Total Amount: ₹<?= number_format($totalAmount, 2) ?></strong></p>
        <p class="muted">Selected seats: <?= count($selectedSeats) ?></p>

        <div class="pay-box">
          <?php if ($error): ?>
            <div class="err"><?= clean($error) ?></div>
          <?php else: ?>
            <p>Click below to pay securely with Razorpay.</p>

            <button type="button" class="btn" id="rzp-button">Pay Now</button>
            <a href="show.php" class="btn secondary">Cancel</a>

            <form id="razorpay-success-form" action="razorpay_verify.php" method="POST" style="display:none;">
              <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
              <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
              <input type="hidden" name="razorpay_signature" id="razorpay_signature">
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (!$error && $razorpayOrderId !== ""): ?>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
      const options = {
        key: "<?= clean(RAZORPAY_KEY_ID) ?>",
        amount: "<?= (int)$totalAmountPaise ?>",
        currency: "INR",
        name: "MovieTime",
        description: "Movie Ticket Booking",
        image: "./assets/image.png",
        order_id: "<?= clean($razorpayOrderId) ?>",
        handler: function (response) {
          document.getElementById("razorpay_payment_id").value = response.razorpay_payment_id;
          document.getElementById("razorpay_order_id").value = response.razorpay_order_id;
          document.getElementById("razorpay_signature").value = response.razorpay_signature;
          document.getElementById("razorpay-success-form").submit();
        },
        prefill: {
          name: "<?= clean($user["fullname"] ?? "") ?>",
          email: "<?= clean($user["email"] ?? "") ?>",
          contact: "<?= clean($user["mobile"] ?? "") ?>"
        },
        theme: {
          color: "#f84464"
        }
      };

      const rzp = new Razorpay(options);

      rzp.on('payment.failed', function (response) {
        alert("Payment failed: " + response.error.description);
      });

      document.getElementById("rzp-button").onclick = function (e) {
        rzp.open();
        e.preventDefault();
      };
    </script>
  <?php endif; ?>
</body>
</html>