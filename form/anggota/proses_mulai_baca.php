<?php
session_start();
require_once '../../setting/koneksi.php';

if(!isset($_SESSION['login_user'])) {
    header("Location: ../../login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['login_user'];
    
    // Dapatkan id_anggota
    $sql_anggota = "SELECT id_t_anggota FROM t_account WHERE username = ?";
    $stmt = mysqli_prepare($db, $sql_anggota);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $anggota = mysqli_fetch_assoc($result);
    
    $id_anggota = $anggota['id_t_anggota'];
    $id_buku = (int)$_POST['id_buku'];
    
    // Simpan riwayat baca
    $sql = "INSERT INTO t_riwayat_baca 
            (id_t_buku, id_t_anggota, tanggal_baca, waktu_mulai) 
            VALUES (?, ?, CURRENT_DATE(), CURRENT_TIME())";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_buku, $id_anggota);
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Mulai membaca buku";
    } else {
        $_SESSION['error'] = "Gagal memulai membaca";
    }
    
    header("Location: detail-buku.php?id=" . $id_buku);
    exit();
}
?> 