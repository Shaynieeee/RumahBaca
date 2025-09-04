<?php
session_start();
include "../../setting/koneksi.php";

if (!isset($scopes) || !(in_array('buku', $scopes))) {
	header("Location: ../../landing.php");
	exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_buku = $_POST['id_buku'];
    $batas_baca = (int)$_POST['batas_baca_guest'];
    
    $query = "UPDATE t_buku SET batas_baca_guest = ? WHERE id_t_buku = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "ii", $batas_baca, $id_buku);
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Batas baca guest berhasil diupdate";
    } else {
        $_SESSION['error'] = "Gagal mengupdate batas baca guest";
    }
}

header("Location: detail-buku.php?id=" . $id_buku);
exit;
?> 