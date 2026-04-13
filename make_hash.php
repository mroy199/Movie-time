<?php
echo "123456 => " . password_hash("123456", PASSWORD_BCRYPT);
echo "<br><br>";
echo "Bokul@123 => " . password_hash("Bokul@123", PASSWORD_BCRYPT);
?>