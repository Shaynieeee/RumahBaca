<?php
include("header.php");
require_once '../setting/koneksi.php';

// Tambahkan di bagian atas file setelah koneksi database
$sql_denda = "SELECT * FROM t_pengaturan_denda";
$result_denda = mysqli_query($db, $sql_denda);
$pengaturan_denda = [];
while($row = mysqli_fetch_assoc($result_denda)) {
    $pengaturan_denda[$row['jenis_denda']] = $row['nilai_denda'];
}

// Perbaiki query untuk mengambil data peminjaman terlambat
$sql = "SELECT p.id_t_peminjaman, p.tgl_pinjam, p.tgl_kembali, p.status,
        a.no_anggota, a.nama as nama_anggota, a.email,
        b.nama_buku,
        dp.qty,
        DATEDIFF(CURDATE(), p.tgl_kembali) as hari_terlambat
        FROM t_peminjaman p
        JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota 
        JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
        JOIN t_buku b ON dp.id_t_buku = b.id_t_buku
        WHERE p.status IN ('Belum Kembali', 'Dipinjam') 
        AND p.tgl_kembali < CURDATE()
        ORDER BY p.tgl_kembali ASC";

$result = mysqli_query($db, $sql);

// Debug untuk melihat jumlah data
$count = mysqli_num_rows($result);
echo "<!-- Debug: Jumlah data: " . $count . " -->";

// Tambahkan fungsi untuk update status anggota
function updateStatusAnggota($db, $id_anggota, $status, $keterangan) {
    $update_by = substr($_SESSION['login_user'], 0, 3);
    
    $sql = "UPDATE t_anggota 
            SET status = ?, 
                keterangan = ?,
                update_by = ?,
                update_date = CURDATE() 
            WHERE id_t_anggota = ?";
            
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $status, $keterangan, $update_by, $id_anggota);
    
    return mysqli_stmt_execute($stmt);
}

// Tambahkan endpoint untuk handle AJAX request
if(isset($_POST['action']) && $_POST['action'] == 'nonaktifkan') {
    $id_anggota = $_POST['id_anggota'];
    $keterangan = "Dinonaktifkan sistem karena terlambat mengembalikan buku";
    
    if(updateStatusAnggota($db, $id_anggota, 'Tidak Aktif', $keterangan)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
    }
    exit;
}

// Tambahkan fungsi untuk menghitung denda
function hitungDenda($hari_terlambat) {
    global $db;
    $sql_denda = "SELECT nilai_denda FROM t_pengaturan_denda WHERE jenis_denda = 'terlambat'";
    $result_denda = mysqli_query($db, $sql_denda);
    $row_denda = mysqli_fetch_assoc($result_denda);
    $denda_per_hari = isset($row_denda['nilai_denda']) ? $row_denda['nilai_denda'] : 2000;
    
    return $hari_terlambat * $denda_per_hari;
}

// Tambahkan fungsi untuk menonaktifkan akun
if(isset($_GET['nonaktif']) && isset($_GET['id_anggota'])) {
    $id_anggota = mysqli_real_escape_string($db, $_GET['id_anggota']);
    
    // Update status anggota menjadi tidak aktif
    $sql_update = "UPDATE t_anggota SET 
                   status = 'Tidak Aktif',
                   update_date = CURRENT_DATE,
                   update_by = ?
                   WHERE id_t_anggota = ?";
    
    $stmt = mysqli_prepare($db, $sql_update);
    mysqli_stmt_bind_param($stmt, "si", $_SESSION['login_user'], $id_anggota);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Akun anggota berhasil dinonaktifkan');</script>";
        echo "<script>window.location.href='peminjaman_terlambat.php';</script>";
    } else {
        echo "<script>alert('Gagal menonaktifkan akun anggota: " . mysqli_error($db) . "');</script>";
    }
}
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Daftar Peminjaman Terlambat</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <i class="fa fa-warning"></i> Peminjaman Yang Belum Dikembalikan
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Anggota</th>
                                    <th>Nama Anggota</th>
                                    <th>Judul Buku</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Batas Kembali</th>
                                    <th>Keterlambatan</th>
                                    <th>Denda</th>
                                    <th>Email</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($count > 0) {
                                    $no = 1;
                                    while($row = mysqli_fetch_assoc($result)) {
                                        $total_denda = hitungDenda($row['hari_terlambat']);
                                        
                                        echo "<tr>";
                                        echo "<td>" . $no++ . "</td>";
                                        echo "<td>" . $row['no_anggota'] . "</td>";
                                        echo "<td>" . $row['nama_anggota'] . "</td>";
                                        echo "<td>" . $row['nama_buku'] . "</td>";
                                        echo "<td>" . $row['qty'] . "</td>";
                                        echo "<td>" . date('d-m-Y', strtotime($row['tgl_pinjam'])) . "</td>";
                                        echo "<td>" . date('d-m-Y', strtotime($row['tgl_kembali'])) . "</td>";
                                        echo "<td>" . $row['hari_terlambat'] . " hari</td>";
                                        echo "<td>Rp " . number_format($total_denda, 0, ',', '.') . "</td>";
                                        echo "<td>" . $row['email'] . "</td>";
                                        echo "<td>";
                                        echo "<a href='kirim_notifikasi_email.php?id=" . $row['id_t_peminjaman'] . "' class='btn btn-warning btn-sm mb-2'>";
                                        echo "<i class='fa fa-envelope'></i> Kirim Pengingat";
                                        echo "</a>";
                                        echo "<br>";
                                        echo "<a href='peminjaman_terlambat.php?nonaktif=1&id_anggota=" . $row['id_t_peminjaman'] . "' 
                                              class='btn btn-danger btn-sm' 
                                              onclick=\"return confirm('Apakah Anda yakin ingin menonaktifkan akun anggota ini?');\">";
                                        echo "<i class='fa fa-ban'></i> Nonaktifkan Akun";
                                        echo "</a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='11' class='text-center'>Tidak ada data peminjaman yang terlambat</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Debug query
if($count == 0) {
    $debug_sql = "SELECT COUNT(*) as total FROM t_peminjaman WHERE status IN ('Belum Kembali', 'Dipinjam')";
    $debug_result = mysqli_query($db, $debug_sql);
    $debug_row = mysqli_fetch_assoc($debug_result);
    echo "<!-- Debug: Total peminjaman aktif: " . $debug_row['total'] . " -->";
}
?>

<script>
$(document).ready(function() {
    $('#dataTables-example').DataTable({
        responsive: true
    });
});

function nonaktifkanAkun(id_anggota, nama) {
    if(confirm('Apakah Anda yakin ingin menonaktifkan akun ' + nama + '?')) {
        $.ajax({
            url: 'peminjaman_terlambat.php',
            type: 'POST',
            data: {
                action: 'nonaktifkan',
                id_anggota: id_anggota
            },
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success') {
                    // alert('Akun berhasil dinonaktifkan');
                    location.reload();
                } else {
                    // alert('Gagal menonaktifkan akun: ' + response.message);
                }
            },
            error: function() {
                // alert('Terjadi kesalahan sistem');
            }
        });
    }
}
</script>

<style>
.label-danger {
    padding: 5px 10px;
    font-size: 12px;
}

.btn-sm {
    margin: 2px;
}

.alert-custom {
    background-color: #f2dede;
    border: none;
    padding: 15px;
    margin-bottom: 20px;
    text-align: center;
}

.alert-custom span {
    color: #a94442;
}

.alert-custom .btn-danger {
    margin-left: 10px;
    padding: 5px 10px;
    font-size: 12px;
}

.alert-custom .fa {
    margin-right: 5px;
}
</style>

