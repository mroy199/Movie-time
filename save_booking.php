<?php
require_once __DIR__ . "/config/db.php";
session_start();

// user_id can come from your login system later.
// For now, we treat not-logged-in as Guest (create a guest user once).
$user_id = $_SESSION["user_id"] ?? null;

$movieTitle = $_POST["movie"] ?? "";
$seats      = $_POST["seats"] ?? "";
$amount     = floatval($_POST["amount"] ?? 0);
$status     = $_POST["status"] ?? "SUCCESS";

// Find movie_id from title
$stmt = $conn->prepare("SELECT id FROM movies WHERE title=? LIMIT 1");
$stmt->bind_param("s", $movieTitle);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$movie_id = $row ? (int)$row["id"] : 0;

if ($movie_id === 0) {
  http_response_code(400);
  echo "Movie not found in DB";
  exit();
}

// If guest, create or reuse one guest user
if (!$user_id) {
  $guestEmail = "guest@movietime.local";
  $q = $conn->query("SELECT id FROM users WHERE email='$guestEmail' LIMIT 1");
  if ($q->num_rows) $user_id = (int)$q->fetch_assoc()["id"];
  else {
    $p = password_hash("guest", PASSWORD_BCRYPT);
    $conn->query("INSERT INTO users (name,email,password,role) VALUES ('Guest','$guestEmail','$p','guest')");
    $user_id = $conn->insert_id;
  }
}

$ins = $conn->prepare("INSERT INTO bookings (user_id,movie_id,seats,total_amount,payment_status) VALUES (?,?,?,?,?)");
$ins->bind_param("iisds", $user_id, $movie_id, $seats, $amount, $status);
$ins->execute();

echo "OK";