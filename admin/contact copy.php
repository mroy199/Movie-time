<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

/* DELETE */
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $conn->query("DELETE FROM contact_messages WHERE id=$id");
    header("Location: contact.php");
    exit;
}

/* FETCH */
$result = $conn->query("SELECT * FROM contact_messages ORDER BY id DESC");
?>

<h2>Contact Messages</h2>

<table border="1" cellpadding="10">
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Message</th>
<th>Date</th>
<th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row["id"] ?></td>
<td><?= htmlspecialchars($row["name"]) ?></td>
<td><?= htmlspecialchars($row["email"]) ?></td>
<td><?= htmlspecialchars($row["message"]) ?></td>
<td><?= $row["created_at"] ?></td>
<td>
<a href="?delete=<?= $row["id"] ?>" onclick="return confirm('Delete?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>

</table>

<a href="index.php">Back</a>