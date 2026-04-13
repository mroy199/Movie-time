<?php
require_once "config/db.php";

$email = "mroy199@rku.ac.in";
$newPassword = "123456";
$hash = password_hash($newPassword, PASSWORD_BCRYPT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hash, $email);

if ($stmt->execute()) {
    echo "Password reset done for: " . htmlspecialchars($email);
} else {
    echo "Reset failed";
}
?>