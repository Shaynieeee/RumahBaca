<?php
session_start();
require_once '../setting/koneksi.php';

if(!isset($_SESSION['login_user'])) {
    die("Akses ditolak");
}

$id_balasan = $_POST['id_balasan'];
$jenis = $_POST['jenis'];
$username = $_SESSION['login_user'];

// Cek apakah user sudah like/dislike sebelumnya
$sql = "SELECT * FROM t_rating_balasan_like 
        WHERE id_balasan = ? AND username = ?";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "is", $id_balasan, $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) > 0) {
    // Update jenis like/dislike
    $sql = "UPDATE t_rating_balasan_like SET jenis = ? 
            WHERE id_balasan = ? AND username = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "sis", $jenis, $id_balasan, $username);
} else {
    // Insert baru
    $sql = "INSERT INTO t_rating_balasan_like (id_balasan, username, jenis) 
            VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $id_balasan, $username, $jenis);
}
mysqli_stmt_execute($stmt);

// Ambil jumlah like dan dislike terbaru
$sql = "SELECT 
        (SELECT COUNT(*) FROM t_rating_balasan_like WHERE id_balasan = ? AND jenis = 'like') as likes,
        (SELECT COUNT(*) FROM t_rating_balasan_like WHERE id_balasan = ? AND jenis = 'dislike') as dislikes";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id_balasan, $id_balasan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

echo json_encode($row);
?> 