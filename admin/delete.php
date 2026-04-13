<?php
require_once __DIR__ . "/../config/db.php";

$type = $_GET['type'];
$id = (int)$_GET['id'];

if($type === 'movie'){
    $conn->query("DELETE FROM movies WHERE id=$id");
}

header("Location: index.php");