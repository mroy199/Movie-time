<?php
if (!isset($_SESSION)) session_start();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Panel</title>
<link rel="stylesheet" href="/movie.css">
<style>
body{background:#000;color:#fff;margin:0}
.wrap{max-width:1200px;margin:30px auto;padding:0 15px}
.top{display:flex;justify-content:space-between;margin-bottom:20px}
.btn{background:#f84464;color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
<div class="top">
<h2>Admin Panel</h2>
<div>
<a class="btn" href="index.php">Dashboard</a>
<a class="btn" href="../auth/logout.php">Logout</a>
</div>
</div>