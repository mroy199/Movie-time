<?php
session_start();
include("config/db.php");
require_once("config/razorpay.php");
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/includes/send_mail.php";

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

if (!isset($_SESSION["pending_booking"], $_SESSION["razorpay_checkout"])) {
    header("Location: show.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$pending = $_SESSION["pending_booking"];
$checkout = $_SESSION["razorpay_checkout"];

$movie_id = (int)($pending["movie_id"] ?? 0);
$selectedSeats = $pending["selected_seats"] ?? [];
$show_date = trim($pending["show_date"] ?? "");
$show_time = trim($pending["show_time"] ?? "");

$razorpay_payment_id = trim($_POST["razorpay_payment_id"] ?? "");
$razorpay_order_id = trim($_POST["razorpay_order_id"] ?? "");
$razorpay_signature = trim($_POST["razorpay_signature"] ?? "");

if (
    $movie_id <= 0 ||
    empty($selectedSeats) ||
    $show_date === "" ||
    $show_time === "" ||
    $razorpay_payment_id === "" ||
    $razorpay_order_id === "" ||
    $razorpay_signature === ""
) {
    die("Invalid payment verification request.");
}

$serverOrderId = $checkout["order_id"] ?? "";
if ($serverOrderId === "" || $razorpay_order_id !== $serverOrderId) {
    die("Order mismatch.");
}

$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();

if (!$movie) {
    die("Movie not found.");
}

$ticketPrice = isset($movie["price"]) && (float)$movie["price"] > 0 ? (float)$movie["price"] : 200;

$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

try {
    $attributes = [
        'razorpay_order_id' => $serverOrderId,
        'razorpay_payment_id' => $razorpay_payment_id,
        'razorpay_signature' => $razorpay_signature
    ];

    $api->utility->verifyPaymentSignature($attributes);
} catch (SignatureVerificationError $e) {
    die("Payment signature verification failed.");
} catch (Exception $e) {
    die("Payment verification error.");
}

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
        die("Seat $seat is already booked.");
    }
}

$bookingIds = [];

foreach ($selectedSeats as $seat) {
    $seat = trim($seat);

    $insert = $conn->prepare("
        INSERT INTO bookings
        (user_id, movie_id, seat_number, show_date, show_time, total_amount, payment_status, payment_method, razorpay_order_id, razorpay_payment_id)
        VALUES (?, ?, ?, ?, ?, ?, 'Paid', 'Razorpay', ?, ?)
    ");
    $insert->bind_param(
        "iisssdss",
        $user_id,
        $movie_id,
        $seat,
        $show_date,
        $show_time,
        $ticketPrice,
        $serverOrderId,
        $razorpay_payment_id
    );
    $insert->execute();

    $bookingIds[] = $conn->insert_id;
}

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
            $totalAmount += (float)$r["total_amount"];
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
                <p><b>Payment ID:</b> {$razorpay_payment_id}</p>
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

$_SESSION["last_booking_ids"] = $bookingIds;
unset($_SESSION["pending_booking"], $_SESSION["razorpay_checkout"]);

header("Location: receipt.php");
exit;