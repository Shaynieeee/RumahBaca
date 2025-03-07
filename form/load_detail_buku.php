<?php
session_start();
require_once '../setting/koneksi.php';

$id_peminjaman = isset($_POST['id_peminjaman']) ? (int)$_POST['id_peminjaman'] : 0;

$sql = "SELECT dp.*, b.nama_buku, b.penulis, b.harga 
        FROM t_detil_pinjam dp 
        JOIN t_buku b ON dp.id_t_buku = b.id_t_buku 
        WHERE dp.id_t_peminjaman = ? 
        ORDER BY dp.create_date DESC";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_peminjaman);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$no = 1;
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>".$no++."</td>";
    echo "<td>".$row['nama_buku']."</td>";
    echo "<td>".$row['penulis']."</td>";
    echo "<td>".$row['qty']."</td>";
    echo "<td>".$row['status']."</td>";
    echo "<td>Rp. ".number_format($row['harga'],0,',','.')."</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>
            <button type='button' class='btn btn-danger btn-xs' onclick='hapusDetailBuku(".$row['id_t_detil_pinjam'].")'>
                <i class='fa fa-trash'></i> Hapus
            </button>
          </td>";
    echo "</tr>";
}

if($no == 1) {
    echo "<tr><td colspan='9' class='text-center'>Belum ada buku yang dipinjam</td></tr>";
} 