<?php
// Cek apakah konstanta sudah didefinisikan
if (!defined('DB_SERVER')) define('DB_SERVER', 'localhost');
if (!defined('DB_USERNAME')) define('DB_USERNAME', 'root');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', '');
if (!defined('DB_DATABASE')) define('DB_DATABASE', 'perpustakaan');

// Koneksi database pertama
$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Konstanta tambahan
if (!defined('PREVIEW_LENGTH')) define('PREVIEW_LENGTH', 300);
if (!defined('ALLOWED_BOOK_TYPES')) define('ALLOWED_BOOK_TYPES', ['pdf', 'epub', 'doc', 'docx']);
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__) . '/');

// Koneksi database kedua (bisa digabung dengan yang pertama)
if (!isset($conn)) {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
}

// Base URL
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/Perpustakaan');

// Cek koneksi pertama
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set karakter encoding
mysqli_set_charset($db, "utf8");
mysqli_set_charset($conn, "utf8");
?>