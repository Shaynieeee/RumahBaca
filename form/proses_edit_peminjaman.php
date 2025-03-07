<?php
require_once '../setting/koneksi.php';
require_once '../setting/session.php';

if(isset($_POST['submit'])) {
    $id_peminjaman = $_POST['id_peminjaman'];
    $id_detil = $_POST['id_detil'];
    $kondisi = $_POST['kondisi'];
    $keterangan = $_POST['keterangan'];
    $status = $_POST['status'];
    
    mysqli_begin_transaction($db);
    
    try {
        // Update status peminjaman
        $sql_update_pinjam = "UPDATE t_peminjaman SET 
                             status = ?,
                             tgl_kembali = " . ($status == 'Sudah Kembali' ? 'CURDATE()' : 'NULL') . "
                             WHERE id_t_peminjaman = ?";
        $stmt_pinjam = mysqli_prepare($db, $sql_update_pinjam);
        mysqli_stmt_bind_param($stmt_pinjam, "si", $status, $id_peminjaman);
        mysqli_stmt_execute($stmt_pinjam);

        // Update setiap detail peminjaman
        for($i = 0; $i < count($id_detil); $i++) {
            // Ambil harga buku untuk menghitung denda
            $sql_get_buku = "SELECT b.harga, dp.qty, dp.id_t_buku 
                            FROM t_detil_pinjam dp 
                            JOIN t_buku b ON dp.id_t_buku = b.id_t_buku 
                            WHERE dp.id_t_detil_pinjam = ?";
            $stmt_get = mysqli_prepare($db, $sql_get_buku);
            mysqli_stmt_bind_param($stmt_get, "i", $id_detil[$i]);
            mysqli_stmt_execute($stmt_get);
            $result = mysqli_stmt_get_result($stmt_get);
            $buku = mysqli_fetch_assoc($result);

            // Hitung denda berdasarkan kondisi dan status
            $denda = 0;
            $kondisi_final = $kondisi[$i];
            $keterangan_final = $keterangan[$i];

            if($status == 'Sudah Kembali') {
                if($kondisi[$i] == 'Rusak') {
                    $denda = round($buku['harga'] * 0.3);
                } elseif($kondisi[$i] == 'Hilang') {
                    $denda = $buku['harga'];
                }
            } else {
                // Jika status Dipinjam atau Belum Kembali
                $kondisi_final = 'Baik'; // Default kondisi
                $keterangan_final = '-';
            }

            // Update detail peminjaman
            $sql_update_detail = "UPDATE t_detil_pinjam SET 
                                kondisi = ?,
                                denda = ?,
                                keterangan = ?,
                                update_date = CURDATE(),
                                update_by = ?
                                WHERE id_t_detil_pinjam = ?";
            $stmt_detail = mysqli_prepare($db, $sql_update_detail);
            mysqli_stmt_bind_param($stmt_detail, "sissi", 
                                 $kondisi_final,
                                 $denda,
                                 $keterangan_final,
                                 $_SESSION['login_user'],
                                 $id_detil[$i]);
            mysqli_stmt_execute($stmt_detail);

            // Jika status Sudah Kembali, update stok buku
            if($status == 'Sudah Kembali' && $kondisi_final != 'Hilang') {
                $sql_update_stok = "UPDATE t_buku SET stok = stok + ? WHERE id_t_buku = ?";
                $stmt_stok = mysqli_prepare($db, $sql_update_stok);
                mysqli_stmt_bind_param($stmt_stok, "ii", $buku['qty'], $buku['id_t_buku']);
                mysqli_stmt_execute($stmt_stok);
            }
        }

        mysqli_commit($db);
        echo "<script>alert('Data berhasil diupdate!'); window.location='data_peminjaman.php';</script>";
    } catch (Exception $e) {
        mysqli_rollback($db);
        echo "<script>alert('Gagal mengupdate data: " . mysqli_error($db) . "'); window.history.back();</script>";
    }
} else {
    header("Location: data_peminjaman.php");
}
?> 