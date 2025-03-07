<?php
session_start();
require_once '../setting/koneksi.php';

if(isset($_POST['id'])) {
    $id_detail = $_POST['id'];
    
    mysqli_begin_transaction($db);
    
    try {
        // 1. Ambil data qty dan id_buku sebelum dihapus
        $sql_detail = "SELECT id_t_buku, qty FROM t_detil_pinjam WHERE id_t_detil_pinjam = ?";
        $stmt = mysqli_prepare($db, $sql_detail);
        mysqli_stmt_bind_param($stmt, "i", $id_detail);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $detail = mysqli_fetch_assoc($result);
        
        // 2. Kembalikan stok buku
        $sql_update = "UPDATE t_buku SET stok = stok + ? WHERE id_t_buku = ?";
        $stmt = mysqli_prepare($db, $sql_update);
        mysqli_stmt_bind_param($stmt, "ii", $detail['qty'], $detail['id_t_buku']);
        mysqli_stmt_execute($stmt);
        
        // 3. Hapus detail peminjaman
        $sql_delete = "DELETE FROM t_detil_pinjam WHERE id_t_detil_pinjam = ?";
        $stmt = mysqli_prepare($db, $sql_delete);
        mysqli_stmt_bind_param($stmt, "i", $id_detail);
        mysqli_stmt_execute($stmt);
        
        mysqli_commit($db);
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        mysqli_rollback($db);
        echo json_encode(['success' => false]);
    }
}
?> 