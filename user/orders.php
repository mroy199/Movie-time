<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$user_name = $_SESSION["name"] ?? "User";
$profile_photo = $_SESSION["profile_photo"] ?? "";

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$photoPath = !empty($profile_photo) ? "../" . $profile_photo : "../assets/image.png";

$sql = "
    SELECT 
        b.id,
        b.seat_number,
        b.show_date,
        b.show_time,
        b.total_amount,
        b.payment_status,
        b.payment_method,
        b.created_at,
        m.title,
        m.image,
        m.language,
        m.genre,
        m.rating
    FROM bookings b
    JOIN movies m ON b.movie_id = m.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

$paidCount = 0;
foreach ($bookings as $b) {
    if (strtolower($b["payment_status"] ?? "") === "paid") {
        $paidCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings | MovieTime</title>
  <style>
    :root{
      --bg:#09090d;
      --card:#12131a;
      --card2:#171923;
      --border:rgba(255,255,255,.08);
      --text:#ffffff;
      --muted:#a8adbb;
      --pink:#f84464;
      --pink2:#ff5b7d;
      --green:#22c55e;
      --yellow:#facc15;
      --shadow:0 20px 45px rgba(0,0,0,.45);
    }

    *{box-sizing:border-box}

    body{
      margin:0;
      font-family:Inter, Arial, sans-serif;
      background:
        radial-gradient(900px 500px at 10% 0%, rgba(248,68,100,.14), transparent 60%),
        radial-gradient(900px 600px at 100% 0%, rgba(91,110,255,.10), transparent 60%),
        var(--bg);
      color:var(--text);
    }

    .topbar{
      position:sticky;
      top:0;
      z-index:100;
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding:16px 28px;
      background:rgba(8,8,12,.85);
      backdrop-filter:blur(12px);
      border-bottom:1px solid var(--border);
    }

    .brand{
      display:flex;
      align-items:center;
      gap:12px;
      text-decoration:none;
      color:#fff;
      font-weight:800;
      font-size:24px;
    }

    .brand img{
      width:38px;
      height:38px;
      border-radius:12px;
      object-fit:cover;
    }

    .top-actions{
      display:flex;
      align-items:center;
      gap:12px;
      flex-wrap:wrap;
    }

    .profile-chip{
      display:flex;
      align-items:center;
      gap:10px;
      background:rgba(255,255,255,.04);
      border:1px solid var(--border);
      border-radius:999px;
      padding:8px 14px 8px 8px;
    }

    .profile-chip img{
      width:40px;
      height:40px;
      border-radius:50%;
      object-fit:cover;
      border:2px solid rgba(248,68,100,.45);
    }

    .profile-chip span{
      color:#fff;
      font-weight:600;
      font-size:14px;
    }

    .btn{
      border:none;
      outline:none;
      text-decoration:none;
      background:linear-gradient(90deg, var(--pink), var(--pink2));
      color:#fff;
      padding:11px 18px;
      border-radius:12px;
      font-weight:700;
      cursor:pointer;
      transition:.18s ease;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      box-shadow:0 10px 25px rgba(248,68,100,.22);
      white-space:nowrap;
    }

    .btn:hover{
      transform:translateY(-1px);
      filter:brightness(1.03);
    }

    .btn.secondary{
      background:#1b1d27;
      border:1px solid var(--border);
      box-shadow:none;
    }

    .wrap{
      max-width:1240px;
      margin:0 auto;
      padding:34px 22px 60px;
    }

    .hero{
      display:flex;
      justify-content:space-between;
      align-items:end;
      gap:20px;
      margin-bottom:26px;
      flex-wrap:wrap;
    }

    .hero h1{
      margin:0;
      font-size:42px;
      line-height:1.05;
      letter-spacing:-1px;
    }

    .hero p{
      margin:10px 0 0;
      color:var(--muted);
      font-size:16px;
    }

    .stats{
      display:flex;
      gap:14px;
      flex-wrap:wrap;
    }

    .stat{
      min-width:150px;
      background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
      border:1px solid var(--border);
      border-radius:18px;
      padding:16px 18px;
      box-shadow:var(--shadow);
    }

    .stat .label{
      color:var(--muted);
      font-size:13px;
      margin-bottom:8px;
    }

    .stat .value{
      font-size:28px;
      font-weight:800;
    }

    .booking-grid{
      display:grid;
      gap:18px;
    }

    .booking-card{
      display:grid;
      grid-template-columns:220px 1fr auto;
      gap:22px;
      background:linear-gradient(180deg, rgba(18,19,26,.96), rgba(12,13,18,.96));
      border:1px solid var(--border);
      border-radius:24px;
      padding:20px;
      box-shadow:var(--shadow);
      align-items:center;
    }

    .poster{
      width:100%;
      height:300px;
      border-radius:18px;
      object-fit:cover;
      background:#1f2230;
    }

    .content h2{
      margin:0 0 10px;
      font-size:34px;
      line-height:1.1;
      letter-spacing:-.5px;
    }

    .meta-top{
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      margin-bottom:14px;
    }

    .pill{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:8px 12px;
      border-radius:999px;
      background:rgba(255,255,255,.05);
      border:1px solid var(--border);
      color:#dfe3ee;
      font-size:13px;
      font-weight:600;
    }

    .details{
      display:grid;
      grid-template-columns:repeat(2, minmax(180px, 1fr));
      gap:12px 18px;
      margin-top:16px;
    }

    .detail{
      background:rgba(255,255,255,.03);
      border:1px solid rgba(255,255,255,.05);
      border-radius:16px;
      padding:14px 16px;
    }

    .detail .label{
      color:var(--muted);
      font-size:12px;
      margin-bottom:6px;
    }

    .detail .value{
      font-size:16px;
      font-weight:700;
      color:#fff;
    }

    .right{
      display:flex;
      flex-direction:column;
      align-items:flex-end;
      justify-content:space-between;
      gap:14px;
      min-width:180px;
      height:100%;
    }

    .status{
      padding:10px 14px;
      border-radius:999px;
      font-size:13px;
      font-weight:800;
      text-transform:uppercase;
      letter-spacing:.4px;
      border:1px solid;
    }

    .status.paid{
      background:rgba(34,197,94,.14);
      color:#9af0b8;
      border-color:rgba(34,197,94,.32);
    }

    .status.pending{
      background:rgba(250,204,21,.12);
      color:#fde68a;
      border-color:rgba(250,204,21,.28);
    }

    .amount-box{
      text-align:right;
      background:rgba(255,255,255,.03);
      border:1px solid var(--border);
      border-radius:18px;
      padding:16px 18px;
      width:100%;
    }

    .amount-box .label{
      color:var(--muted);
      font-size:13px;
      margin-bottom:6px;
    }

    .amount-box .price{
      font-size:30px;
      font-weight:900;
      color:#fff;
    }

    .card-actions{
      display:flex;
      flex-direction:column;
      gap:10px;
      width:100%;
    }

    .empty{
      text-align:center;
      padding:70px 24px;
      border-radius:24px;
      border:1px solid var(--border);
      background:linear-gradient(180deg, rgba(18,19,26,.96), rgba(12,13,18,.96));
      box-shadow:var(--shadow);
    }

    .empty h3{
      margin:0 0 10px;
      font-size:28px;
    }

    .empty p{
      margin:0 0 22px;
      color:var(--muted);
    }

    @media (max-width: 1100px){
      .booking-card{
        grid-template-columns:180px 1fr;
      }
      .right{
        grid-column:1 / -1;
        align-items:stretch;
        min-width:unset;
        height:auto;
      }
      .amount-box{
        text-align:left;
      }
      .card-actions{
        flex-direction:row;
        flex-wrap:wrap;
      }
    }

    @media (max-width: 760px){
      .topbar{
        padding:14px 16px;
      }
      .wrap{
        padding:24px 14px 50px;
      }
      .hero h1{
        font-size:32px;
      }
      .booking-card{
        grid-template-columns:1fr;
      }
      .poster{
        height:280px;
      }
      .content h2{
        font-size:28px;
      }
      .details{
        grid-template-columns:1fr;
      }
      .top-actions{
        width:100%;
        justify-content:flex-end;
      }
      .profile-chip span{
        display:none;
      }
    }
  </style>
</head>
<body>

  <div class="topbar">
    <a class="brand" href="../show.php">
      <img src="../assets/image.png" alt="MovieTime">
      <span>MovieTime</span>
    </a>

    <div class="top-actions">
      <div class="profile-chip">
        <img src="<?= clean($photoPath) ?>" alt="Profile">
        <span><?= clean($user_name) ?></span>
      </div>

      <a href="../show.php" class="btn secondary">🏠 Home</a>
      <a href="profile.php" class="btn secondary">👤 Edit Profile</a>
      <a href="../auth/logout.php" class="btn">🚪 Logout</a>
    </div>
  </div>

  <div class="wrap">
    <div class="hero">
      <div>
        <h1>My Bookings</h1>
        <p>Track your booked movies, seats, payment status, and show timings.</p>
      </div>

      <div class="stats">
        <div class="stat">
          <div class="label">Total Bookings</div>
          <div class="value"><?= count($bookings) ?></div>
        </div>

        <div class="stat">
          <div class="label">Paid Tickets</div>
          <div class="value"><?= $paidCount ?></div>
        </div>
      </div>
    </div>

    <?php if (!empty($bookings)): ?>
      <div class="booking-grid">
        <?php foreach ($bookings as $booking): ?>
          <?php
            $movieImage = !empty($booking["image"]) ? $booking["image"] : "../assets/image.png";
            $status = strtolower($booking["payment_status"] ?? "pending");
          ?>
          <div class="booking-card">
            <img class="poster" src="<?= clean($movieImage) ?>" alt="<?= clean($booking["title"] ?? "Movie") ?>">

            <div class="content">
              <h2><?= clean($booking["title"] ?? "Untitled Movie") ?></h2>

              <div class="meta-top">
                <?php if (!empty($booking["rating"])): ?>
                  <div class="pill">⭐ <?= clean($booking["rating"]) ?></div>
                <?php endif; ?>

                <?php if (!empty($booking["genre"])): ?>
                  <div class="pill"><?= clean($booking["genre"]) ?></div>
                <?php endif; ?>

                <?php if (!empty($booking["language"])): ?>
                  <div class="pill"><?= clean($booking["language"]) ?></div>
                <?php endif; ?>
              </div>

              <div class="details">
                <div class="detail">
                  <div class="label">Seat Number</div>
                  <div class="value"><?= clean($booking["seat_number"]) ?></div>
                </div>

                <div class="detail">
                  <div class="label">Show Date</div>
                  <div class="value"><?= clean($booking["show_date"]) ?></div>
                </div>

                <div class="detail">
                  <div class="label">Show Time</div>
                  <div class="value"><?= clean($booking["show_time"]) ?></div>
                </div>

                <div class="detail">
                  <div class="label">Booked On</div>
                  <div class="value"><?= clean($booking["created_at"]) ?></div>
                </div>

                <div class="detail">
                  <div class="label">Payment Method</div>
                  <div class="value"><?= clean($booking["payment_method"]) ?></div>
                </div>

                <div class="detail">
                  <div class="label">Booking ID</div>
                  <div class="value">#<?= clean($booking["id"]) ?></div>
                </div>
              </div>
            </div>

            <div class="right">
              <div class="status <?= $status === 'paid' ? 'paid' : 'pending' ?>">
                <?= clean($booking["payment_status"] ?: "Pending") ?>
              </div>

              <div class="amount-box">
                <div class="label">Total Amount</div>
                <div class="price">₹<?= number_format((float)$booking["total_amount"], 2) ?></div>
              </div>

              <div class="card-actions">
                <a href="../show.php" class="btn secondary">🏠 Home</a>
                <a href="../show.php" class="btn secondary">🎬 Book Again</a>
                <a href="../receipt.php?booking_id=<?= (int)$booking["id"] ?>" class="btn">🎟 View Ticket</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty">
        <h3>No bookings yet</h3>
        <p>You have not booked any movie tickets yet.</p>
        <a href="../show.php" class="btn">Browse Movies</a>
      </div>
    <?php endif; ?>
  </div>

</body>
</html>