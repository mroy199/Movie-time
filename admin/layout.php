<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Panel - MovieTime</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
*{
  margin:0;
  padding:0;
  box-sizing:border-box;
  font-family:'Poppins', sans-serif;
}

body{
  display:flex;
  background:#0b1120;
  color:#fff;
}

/* SIDEBAR */
.sidebar{
  width:250px;
  height:100vh;
  background:linear-gradient(180deg,#020617,#0f172a);
  padding:20px;
  position:fixed;
  border-right:1px solid #1e293b;
}

.sidebar h2{
  color:#f43f5e;
  margin-bottom:30px;
  font-weight:600;
}

.sidebar a{
  display:flex;
  align-items:center;
  gap:10px;
  color:#94a3b8;
  text-decoration:none;
  padding:12px;
  border-radius:10px;
  margin-bottom:10px;
  transition:0.3s;
}

.sidebar a:hover{
  background:#f43f5e;
  color:#fff;
  transform:translateX(5px);
}

/* MAIN */
.main{
  margin-left:250px;
  width:100%;
  padding:25px;
}

/* HEADER */
.header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:25px;
}

.header h1{
  font-size:26px;
  font-weight:600;
}

.btn{
  background:#f43f5e;
  padding:10px 16px;
  border-radius:8px;
  color:#fff;
  text-decoration:none;
  font-weight:500;
  transition:0.3s;
}

.btn:hover{
  background:#e11d48;
}

/* CARDS */
.cards{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:20px;
  margin-bottom:25px;
}

.card{
  background:#111827;
  padding:20px;
  border-radius:14px;
  box-shadow:0 10px 20px rgba(0,0,0,0.3);
  transition:0.3s;
}

.card:hover{
  transform:translateY(-5px);
}

.card h3{
  color:#9ca3af;
  margin-bottom:10px;
}

.card h1{
  color:#f43f5e;
  font-size:32px;
}

/* TABLE */
table{
  width:100%;
  border-collapse:collapse;
  background:#111827;
  border-radius:12px;
  overflow:hidden;
}

th,td{
  padding:14px;
  text-align:left;
}

th{
  background:#1f2937;
  color:#f43f5e;
}

tr{
  border-bottom:1px solid #1f2937;
}

tr:hover{
  background:#1e293b;
}

/* BADGE */
.badge{
  background:#f43f5e;
  padding:4px 8px;
  border-radius:6px;
  font-size:12px;
}

/* IMAGE */
img{
  width:60px;
  border-radius:6px;
}

/* ACTION BUTTONS */
.actions a{
  padding:6px 10px;
  border-radius:6px;
  margin-right:5px;
  text-decoration:none;
  color:#fff;
}

.edit{background:#2563eb}
.delete{background:#dc2626}

/* FORM */
input, textarea, select{
  width:100%;
  padding:10px;
  margin:6px 0;
  border:none;
  border-radius:8px;
  background:#1f2937;
  color:#fff;
}

button{
  background:#f43f5e;
  color:#fff;
  padding:10px;
  border:none;
  border-radius:8px;
  cursor:pointer;
}

button:hover{
  background:#e11d48;
}

/* SCROLLBAR */
::-webkit-scrollbar{
  width:6px;
}
::-webkit-scrollbar-thumb{
  background:#f43f5e;
  border-radius:10px;
}
</style>

</head>

<body>

<div class="sidebar">

  <h2>🎬 MovieTime</h2>

  <a href="index.php">📊 Dashboard</a>
  <a href="movies.php">🎬 Movies</a>
  <a href="users.php">👤 Users</a>
  <a href="bookings.php">🎟 Bookings</a>
  <a href="contact.php">📩 Contact</a>
  <a href="../auth/logout.php">🚪 Logout</a>
<a href="../show.php" class="go-site-btn">Go to Site</a>
</div>

<div class="main">