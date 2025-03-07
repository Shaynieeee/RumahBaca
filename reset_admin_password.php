<?php
// Password yang ingin digunakan
$new_password = "admin123";

// Generate hash password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

echo "Password Hash: " . $hashed_password;
?>