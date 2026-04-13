<?php
require_once __DIR__ . "/../config/db.php";
session_start();

$error = "";

if(isset($_POST['login'])){
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
  $stmt->bind_param("s",$email);
  $stmt->execute();
  $result = $stmt->get_result();

  if($result->num_rows > 0){
    $user = $result->fetch_assoc();

    if(password_verify($password,$user['password'])){
      $_SESSION['user'] = $user;

      // redirect based on role
      if($user['role'] === 'admin'){
        header("Location: ../admin/index.php");
      } else {
        header("Location: ../index.php");
      }
      exit;
    } else {
      $error = "Wrong password";
    }
  } else {
    $error = "User not found";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
body{
  display:flex;
  justify-content:center;
  align-items:center;
  height:100vh;
  background:#020617;
  color:#fff;
  font-family:Poppins;
}
.card{
  background:#111827;
  padding:30px;
  border-radius:10px;
  width:300px;
}
input{
  width:100%;
  padding:10px;
  margin:10px 0;
  border:none;
  border-radius:6px;
}
button{
  width:100%;
  padding:10px;
  background:#f43f5e;
  border:none;
  border-radius:6px;
  color:#fff;
}
.error{color:red}
</style>
</head>

<body>

<div class="card">
<h2>Login</h2>

<?php if($error): ?>
<p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button name="login">Login</button>
</form>

</div>

</body>
</html>