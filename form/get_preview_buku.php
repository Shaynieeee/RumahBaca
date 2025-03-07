<?php
require_once '../setting/koneksi.php';

header('Content-Type: application/json');

try {
    if(isset($_POST['id_buku'])) {
        $id_buku = $_POST['id_buku'];
        
        error_log("Received request for book ID: " . $id_buku);
        
        $sql = "SELECT id_t_buku, nama_buku, penulis, penerbit, tahun_terbit, jenis, 
                sinopsis, gambar 
                FROM t_buku 
                WHERE id_t_buku = ?";
                
        $stmt = mysqli_prepare($db, $sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($db));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id_buku);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            throw new Exception("Get result failed: " . mysqli_error($db));
        }
        
        if($row = mysqli_fetch_assoc($result)) {
            error_log("Data found: " . print_r($row, true));
            echo json_encode($row);
        } else {
            echo json_encode(['error' => 'Buku tidak ditemukan']);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['error' => 'ID buku tidak diterima']);
    }
} catch (Exception $e) {
    error_log("Error in get_preview_buku.php: " . $e->getMessage());
    echo json_encode(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
}
?> 