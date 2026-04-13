<form method="POST">
  <input type="text" name="name" placeholder="Name" required>
  <input type="email" name="email" placeholder="Email" required>
  <textarea name="message" placeholder="Message"></textarea>
  <button type="submit">Send</button>
</form>

<?php
if ($_POST) {
  $stmt = $conn->prepare("INSERT INTO contact_messages (name,email,message) VALUES (?,?,?)");
  $stmt->bind_param("sss", $_POST["name"], $_POST["email"], $_POST["message"]);
  $stmt->execute();
}
?>