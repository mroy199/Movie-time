<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

$id = (int)$_GET["id"];

$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();

if (!$user) die("User not found");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["fullname"];
    $email = $_POST["email"];
    $role = $_POST["role"];

    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $role, $id);
    $stmt->execute();

    header("Location: users.php");
}
?>

<h2>Edit User</h2>

<form method="POST">
<input type="text" name="fullname" value="<?= $user["fullname"] ?>"><br><br>
<input type="email" name="email" value="<?= $user["email"] ?>"><br><br>

<select name="role">
<option value="user" <?= $user["role"]=="user"?"selected":"" ?>>User</option>
<option value="admin" <?= $user["role"]=="admin"?"selected":"" ?>>Admin</option>
</select><br><br>

<button>Update</button>
</form>