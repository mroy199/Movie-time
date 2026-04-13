<?php
session_start();
include("config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

if (empty($_SESSION["last_booking_ids"])) {
    header("Location: show.php");
    exit;
}

$bookingIds = $_SESSION["last_booking_ids"];
$placeholders = implode(",", array_fill(0, count($bookingIds), "?"));
$types = str_repeat("i", count($bookingIds));

$sql = "
    SELECT bookings.*, movies.title, movies.image
    FROM bookings
    JOIN movies ON bookings.movie_id = movies.id
    WHERE bookings.id IN ($placeholders)
    ORDER BY bookings.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$bookingIds);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

if (!$rows) {
    die("Receipt not found.");
}

$movie = $rows[0];
$movieTitle = $movie["title"] ?? "Untitled Movie";
$movieImage = !empty($movie["image"]) ? $movie["image"] : "./assets/image.png";
$seatNumbers = array_column($rows, "seat_number");
$total = array_sum(array_map(fn($r) => (float)($r["total_amount"] ?? 0), $rows));
$bookingCode = "MT-" . str_pad((string)$movie["id"], 4, "0", STR_PAD_LEFT) . "-" . date("His");

unset($_SESSION["last_booking_ids"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt - MovieTime</title>
  <link rel="stylesheet" href="movie.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <style>
    body{background:#000;color:#fff;font-family:Arial,sans-serif}
    .wrap{max-width:900px;margin:30px auto;padding:20px}
    .topbar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      flex-wrap:wrap;
      margin-bottom:20px;
    }
    .btn{
      display:inline-block;
      background:#f84464;
      color:#fff;
      text-decoration:none;
      padding:12px 18px;
      border-radius:8px;
      font-weight:700;
      border:none;
      cursor:pointer;
      margin-right:8px;
    }
    .btn.secondary{
      background:#222;
    }
    .ticket{
      background:#fff;
      color:#111;
      border-radius:18px;
      overflow:hidden;
      display:grid;
      grid-template-columns:220px 1fr;
      box-shadow:0 15px 35px rgba(0,0,0,.4);
    }
    .ticket-left img{
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      background:#eee;
    }
    .ticket-right{
      padding:24px;
    }
    .ticket-right h1{
      margin-top:0;
      color:#f84464;
      font-size:30px;
    }
    .ticket-right p{
      margin:8px 0;
      line-height:1.6;
    }
    .status{
      display:inline-block;
      margin-top:12px;
      padding:8px 14px;
      border-radius:999px;
      background:#12361f;
      color:#7dffa6;
      font-weight:700;
    }
    .code{
      display:inline-block;
      margin-top:12px;
      margin-left:8px;
      padding:8px 14px;
      border-radius:999px;
      background:#f4f4f4;
      color:#111;
      font-weight:700;
      border:1px solid #ddd;
    }
    .line{
      margin:14px 0;
      border-top:2px dashed #ccc;
    }
    @media (max-width:768px){
      .ticket{grid-template-columns:1fr}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="topbar">
      <h2 style="margin:0;">Booking Receipt</h2>
      <div>
        <button class="btn" onclick="downloadPDF()">Download PDF</button>
        <a href="user/orders.php" class="btn secondary">My Orders</a>
        <a href="show.php" class="btn secondary">Go Site</a>
      </div>
    </div>

    <div class="ticket" id="receiptArea">
      <div class="ticket-left">
        <img src="<?= htmlspecialchars($movieImage) ?>" alt="<?= htmlspecialchars($movieTitle) ?>">
      </div>

      <div class="ticket-right">
        <h1>Booking Confirmed</h1>
        <p><strong>Movie:</strong> <?= htmlspecialchars($movieTitle) ?></p>
        <p><strong>Seats:</strong> <?= htmlspecialchars(implode(", ", $seatNumbers)) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($movie["show_date"] ?? "N/A") ?></p>
        <p><strong>Time:</strong> <?= htmlspecialchars($movie["show_time"] ?? "N/A") ?></p>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($movie["payment_method"] ?? "N/A") ?></p>
        <p><strong>Total Paid:</strong> ₹<?= number_format($total, 2) ?></p>
        <p><strong>Booked On:</strong> <?= htmlspecialchars($movie["created_at"] ?? "N/A") ?></p>

        <div class="line"></div>

        <span class="status">PAID</span>
        <span class="code"><?= htmlspecialchars($bookingCode) ?></span>
      </div>
    </div>
  </div>

  <script>
    function downloadPDF() {
      const element = document.getElementById("receiptArea");
      const opt = {
        margin: 0.3,
        filename: 'MovieTime_Receipt.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
      };
      html2pdf().set(opt).from(element).save();
    }
  </script>
</body>
</html>