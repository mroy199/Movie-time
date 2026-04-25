<?php
// SAFE SESSION START
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================= CHECK LOGIN =================
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
}

// ================= REQUIRE LOGIN =================
if (!function_exists('require_login')) {
    function require_login() {
        if (!is_logged_in()) {
            header("Location: /movieTime/auth/login.php");
            exit;
        }
    }
}

// ================= REQUIRE ADMIN =================
if (!function_exists('require_admin')) {
    function require_admin() {
        if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
            header("Location: /movieTime/auth/login.php");
            exit;
        }
    }
}

// ================= CURRENT USER =================
if (!function_exists('current_user')) {
    function current_user() {
        return [
            "id" => $_SESSION['user_id'] ?? null,
            "name" => $_SESSION['name'] ?? "",
            "email" => $_SESSION['email'] ?? "",
            "role" => $_SESSION['role'] ?? "",
            "photo" => $_SESSION['profile_photo'] ?? ""
        ];
    }
}