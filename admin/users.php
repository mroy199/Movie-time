<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_admin();

// ================= ADD USER =================
if(isset($_POST['add_user'])){
  $name = $_POST['fullname'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role = $_POST['role'];

  $stmt = $conn->prepare("INSERT INTO users (fullname,email,password,role) VALUES (?,?,?,?)");
  $stmt->bind_param("ssss",$name,$email,$password,$role);
  $stmt->execute();

  header("Location: users.php");
  exit;
}

// ================= DELETE =================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Main admin protection
    if ($id === 1) {
        header("Location: users.php?error=protected");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: users.php?success=deleted");
    exit;
}

// ================= FETCH =================
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<?php include "layout.php"; ?>

<div class="header">
  <h1>👤 Users Management</h1>
</div>

<!-- ADD USER -->
<div class="card">
<h3>Add User</h3>

<form method="POST">
<input type="text" name="fullname" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>

<select name="role">
<option value="user">User</option>
<option value="admin">Admin</option>
</select>

<button name="add_user">Add User</button>
</form>
</div>

<!-- USERS LIST -->
<div class="card">
<h3>All Users</h3>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Action</th>
</tr>

<?php while($u = $users->fetch_assoc()): ?>
<tr>
<td><?= $u["id"] ?></td>
<td><?= htmlspecialchars($u["fullname"]) ?></td>
<td><?= htmlspecialchars($u["email"]) ?></td>
<td><span class="badge"><?= $u["role"] ?></span></td>

<td class="actions">
<a class="edit" href="edit_user.php?id=<?= $u["id"] ?>">Edit</a>
<a class="delete" href="?delete=<?= $u["id"] ?>" onclick="return confirm('Delete this user?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div> <!-- main -->
</body>
</html>