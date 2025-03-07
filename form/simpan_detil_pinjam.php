<?php
session_start();
require_once '../setting/koneksi.php';

if(!isset($_SESSION['login_user'])) {
    header("Location: ../login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_peminjaman = $_POST['id_peminjaman'];
    $id_buku = $_POST['idbuku'];
    $qty = $_POST['qty'];
    $user = $_SESSION['login_user'];
    
    // Begin transaction
    mysqli_begin_transaction($db);
    
    try {
        // Insert detail peminjaman
        $query = "INSERT INTO t_detil_pinjam(id_t_peminjaman, id_t_buku, qty) 
                  VALUES($id_peminjaman, $id_buku, $qty)";
        mysqli_query($db, $query);
        
        // Update stok buku
        $update = "UPDATE t_buku SET stok = stok - $qty WHERE id_t_buku = $id_buku";
        mysqli_query($db, $update);
        
        // Commit transaction
        mysqli_commit($db);
        
        header("Location: input_peminjaman.php?id=".$id_peminjaman);
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($db);
        echo "Error: " . $e->getMessage();
    }
}
?>