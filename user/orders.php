<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT bookings.*, movies.title, movies.image
    FROM bookings
    JOIN movies ON bookings.movie_id = movies.id
    WHERE bookings.user_id = ?
    ORDER BY bookings.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$pageTitle = "My Orders - MovieTime";
include(__DIR__ . "/../includes/site_header_user.php");
?>

<style>
.wrap{max-width:1100px;margin:30px auto;padding:20px}
.booking-card{
  display:flex;
  gap:20px;
  background:#111;
  border:1px solid #222;
  border-radius:16px;
  padding:18px;
  margin-bottom:18px;
  align-items:center;
  flex-wrap:wrap;
}
.booking-card img{
  width:180px;
  height:260px;
  object-fit:cover;
  border-radius:12px;
  background:#111;
}
.booking-info h2{margin:0 0 10px}
.booking-info p{margin:6px 0;color:#ccc}
.page-btn{
  display:inline-block;
  background:#f84464;
  color:#fff;
  text-decoration:none;
  padding:10px 14px;
  border-radius:10px;
  font-weight:700;
  margin-bottom:18px;
}
</style>

<div class="wrap">
  <h1>My Bookings</h1>

  <a class="page-btn" href="profile.php">My Profile</a>

  <?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
      <div class="booking-card">
        <img src="<?= htmlspecialchars(!empty($row["image"]) ? $row["image"] : '../assets/image.png') ?>" alt="<?= htmlspecialchars($row["title"] ?? 'Movie') ?>">
        <div class="booking-info">
          <h2><?= htmlspecialchars($row["title"]) ?></h2>
          <p>Seat: <?= htmlspecialchars($row["seat_number"]) ?></p>
          <p>Date: <?= htmlspecialchars($row["show_date"] ?: "N/A") ?></p>
          <p>Time: <?= htmlspecialchars($row["show_time"] ?: "N/A") ?></p>
          <p>Booked On: <?= htmlspecialchars($row["created_at"]) ?></p>
          <p>Payment: <?= htmlspecialchars($row["payment_status"] ?? "Pending") ?></p>
          <p>Method: <?= htmlspecialchars($row["payment_method"] ?? "N/A") ?></p>
          <p>Amount: ₹<?= number_format((float)($row["total_amount"] ?? 0), 2) ?></p>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No bookings found.</p>
  <?php endif; ?>
</div>

<?php include(__DIR__ . "/../includes/site_footer_user.php"); ?>