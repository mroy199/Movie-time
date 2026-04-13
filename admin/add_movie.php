<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");

    if ($title === "") {
        $error = "Title is required.";
    } else {

        $stmt = $conn->prepare("INSERT INTO movies (title) VALUES (?)");
        $stmt->bind_param("s", $title);

        if ($stmt->execute()) {
            $success = "Movie added successfully!";
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Movie</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="main">
<h1>Add Movie</h1>

<?php if($success): ?>
<div class="success"><?= $success ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="error"><?= $error ?></div>
<?php endif; ?>

<form method="POST">
<label>Movie Title</label>
<input type="text" name="title" required>

<br><br>
<button class="btn">Add Movie</button>
</form>

<br>
<a href="movies.php" class="btn">← Back</a>
</div>

</body>
</html>