<?php
require_once __DIR__ . "/config/db.php";

$email = "manobroymr0303@gmail.com";
$password = "12345678";

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $user = $result->fetch_assoc();

    if(password_verify($password,$user['password'])){
        echo "LOGIN SUCCESS ✅";
    } else {
        echo "WRONG PASSWORD ❌";
    }
} else {
    echo "USER NOT FOUND ❌";
}