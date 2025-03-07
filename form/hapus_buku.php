<?php
require_once '../setting/koneksi.php';

try {
    mysqli_begin_transaction($db);
    
    $id_buku = $_GET['id'];
    
    // 1. Hapus data dari t_riwayat_baca
    $sql_riwayat = "DELETE FROM t_riwayat_baca WHERE id_t_buku = ?";
    $stmt_riwayat = mysqli_prepare($db, $sql_riwayat);
    mysqli_stmt_bind_param($stmt_riwayat, "i", $id_buku);
    mysqli_stmt_execute($stmt_riwayat);
    
    // 2. Hapus data dari t_rating_like yang terkait dengan rating buku ini
    $sql_rating_like = "DELETE t_rating_like FROM t_rating_like 
                       INNER JOIN t_rating_buku ON t_rating_like.id_rating = t_rating_buku.id_rating 
                       WHERE t_rating_buku.id_t_buku = ?";
    $stmt_rating_like = mysqli_prepare($db, $sql_rating_like);
    mysqli_stmt_bind_param($stmt_rating_like, "i", $id_buku);
    mysqli_stmt_execute($stmt_rating_like);
    
    // 3. Hapus data dari t_rating_buku
    $sql_rating = "DELETE FROM t_rating_buku WHERE id_t_buku = ?";
    $stmt_rating = mysqli_prepare($db, $sql_rating);
    mysqli_stmt_bind_param($stmt_rating, "i", $id_buku);
    mysqli_stmt_execute($stmt_rating);
    
    // 4. Baru hapus data buku
    $sql_buku = "DELETE FROM t_buku WHERE id_t_buku = ?";
    $stmt_buku = mysqli_prepare($db, $sql_buku);
    mysqli_stmt_bind_param($stmt_buku, "i", $id_buku);
    mysqli_stmt_execute($stmt_buku);
    
    mysqli_commit($db);
    header("Location: data_buku.php?message=deleted");
    exit();
    
} catch(Exception $e) {
    mysqli_rollback($db);
    header("Location: data_buku.php?message=error&error=" . urlencode($e->getMessage()));
    exit();
}
?>