<?php
session_start();
require_once '../../setting/koneksi.php';

if(!isset($_SESSION['login_user'])) {
    exit();
}

$username = $_SESSION['login_user'];
$id_buku = isset($_GET['id_buku']) ? (int)$_GET['id_buku'] : 0;

if($id_buku > 0) {
    // Dapatkan id_anggota
    $sql_anggota = "SELECT id_t_anggota FROM t_account WHERE username = ?";
    $stmt = mysqli_prepare($db, $sql_anggota);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $anggota = mysqli_fetch_assoc($result);
    
    // Update riwayat baca terakhir yang belum selesai
    $sql = "UPDATE t_riwayat_baca 
            SET waktu_selesai = CURRENT_TIME(),
                durasi = TIMEDIFF(CURRENT_TIME(), waktu_mulai)
            WHERE id_t_buku = ? 
            AND id_t_anggota = ? 
            AND tanggal_baca = CURRENT_DATE()
            AND waktu_selesai IS NULL
            ORDER BY waktu_mulai DESC
            LIMIT 1";
            
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_buku, $anggota['id_t_anggota']);
    mysqli_stmt_execute($stmt);
}
?> 