<?php
$conn = new mysqli("localhost", "root", "root", "movie_booking");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>