<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/* ================= LOGIN CHECK ================= */
if (!function_exists('require_login')) {
  function require_login(){
    if(!isset($_SESSION['user'])){
      header("Location: /movietime/auth/login.php");
      exit;
    }
  }
}

/* ================= ADMIN CHECK ================= */
if (!function_exists('require_admin')) {
  function require_admin(){
    require_login();

    if($_SESSION['user']['role'] !== 'admin'){
      die("⛔ Access Denied (Admin Only)");
    }
  }
}

/* ================= GET USER ================= */
if (!function_exists('current_user')) {
  function current_user(){
    return $_SESSION['user'] ?? null;
  }
}