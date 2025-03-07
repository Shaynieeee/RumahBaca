<?php
require_once '../setting/koneksi.php';
require_once '../setting/session.php';

if(isset($_POST['no_anggota'])) {
    $no_anggota = mysqli_real_escape_string($db, $_POST['no_anggota']);
    
    // Ambil id_anggota dari no_anggota
    $sql_anggota = "SELECT id_t_anggota FROM t_anggota WHERE no_anggota = ?";
    $stmt_anggota = mysqli_prepare($db, $sql_anggota);
    mysqli_stmt_bind_param($stmt_anggota, "s", $no_anggota);
    mysqli_stmt_execute($stmt_anggota);
    $result_anggota = mysqli_stmt_get_result($stmt_anggota);
    $anggota = mysqli_fetch_assoc($result_anggota);
    
    if($anggota) {
        $sql_riwayat = "SELECT dp.id_t_detil_pinjam, dp.qty, 
                               b.nama_buku, b.penulis, b.harga,
                               p.id_t_peminjaman, p.no_peminjaman, p.tgl_pinjam 
                        FROM t_peminjaman p
                        JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
                        JOIN t_buku b ON dp.id_t_buku = b.id_t_buku 
                        WHERE p.id_t_anggota = ? 
                        ORDER BY p.create_date DESC, dp.id_t_detil_pinjam DESC";
        $stmt_riwayat = mysqli_prepare($db, $sql_riwayat);
        mysqli_stmt_bind_param($stmt_riwayat, "i", $anggota['id_t_anggota']);
        mysqli_stmt_execute($stmt_riwayat);
        $result_riwayat = mysqli_stmt_get_result($stmt_riwayat);
        
        $no = 1;
        while($row = mysqli_fetch_assoc($result_riwayat)) {
            echo "<tr>";
            echo "<td>" . $no++ . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_buku']) . "</td>";
            echo "<td>" . htmlspecialchars($row['penulis']) . "</td>";
            echo "<td>" . $row['qty'] . "</td>";
            echo "<td>Rp " . number_format($row['harga'],0,',','.') . "</td>";
            echo "<td>";
            echo "<button type='button' class='btn btn-danger btn-sm' ";
            echo "onclick='hapusDetailBuku(" . $row['id_t_detil_pinjam'] . ")'>";
            echo "<i class='fa fa-trash'></i> Hapus</button>";
            echo "</td>";
            echo "</tr>";
        }
        
        if(mysqli_num_rows($result_riwayat) == 0) {
            echo "<tr><td colspan='6' class='text-center'>Belum ada riwayat peminjaman</td></tr>";
        }
    }
}
?> 