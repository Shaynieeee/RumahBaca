<?php
date_default_timezone_set('Asia/Jakarta');

session_start();
require_once '../../setting/koneksi.php';

if(!isset($_SESSION['login_user'])) {
    exit();
}

// Ambil halaman terakhir dari request
$halaman_terakhir = isset($_POST['halaman_terakhir']) ? (int)$_POST['halaman_terakhir'] : 1;
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
                durasi = TIMEDIFF(CURRENT_TIME(), waktu_mulai),
                halaman_terakhir = ?
            WHERE id_t_buku = ? 
            AND id_t_anggota = ? 
            AND tanggal_baca = CURRENT_DATE()
            AND waktu_selesai IS NULL
            ORDER BY waktu_mulai DESC
            LIMIT 1";
            
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iii", 
        $halaman_terakhir,
        $id_buku, 
        $anggota['id_t_anggota']
    );
    
    if(!mysqli_stmt_execute($stmt)) {
        error_log("Error updating reading history: " . mysqli_error($db));
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'halaman_terakhir' => $halaman_terakhir
]);
?> 