<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../setting/koneksi.php';

// Cek login
if(!isset($_SESSION['login_user']) || $_SESSION['role'] != 3) {
    header("location: ../../login.php");
    exit();
}

// Ambil data anggota
$username = $_SESSION['login_user'];
$sql_anggota = "SELECT id_t_anggota FROM t_account WHERE username = ?";
$stmt = mysqli_prepare($db, $sql_anggota);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$anggota = mysqli_fetch_assoc($result);

// Query untuk riwayat baca
$sql = "SELECT rb.*, b.nama_buku, b.total_halaman,
        TIME_FORMAT(rb.waktu_mulai, '%H:%i:%s') as formatted_waktu_mulai,
        TIME_FORMAT(rb.waktu_selesai, '%H:%i:%s') as formatted_waktu_selesai,
        TIME_FORMAT(TIMEDIFF(rb.waktu_selesai, rb.waktu_mulai), '%H:%i:%s') as formatted_durasi,
        rb.halaman_terakhir
        FROM t_riwayat_baca rb
        INNER JOIN t_buku b ON rb.id_t_buku = b.id_t_buku
        WHERE rb.id_t_anggota = ?
        ORDER BY rb.tanggal_baca DESC, rb.waktu_mulai DESC";

$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $anggota['id_t_anggota']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include("header_anggota.php");
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Riwayat Baca</h1>
    
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Buku</th>
                            <th>Tanggal</th>
                            <th>Mulai Baca</th>
                            <th>Selesai Baca</th>
                            <th>Halaman Terakhir</th>
                            <th>Durasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['nama_buku']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_baca'])); ?></td>
                            <td><?php echo $row['formatted_waktu_mulai']; ?></td>
                            <td>
                                <?php 
                                if($row['waktu_selesai']) {
                                    echo $row['formatted_waktu_selesai'];
                                } else {
                                    echo '<span class="badge badge-warning">Masih membaca</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if($row['halaman_terakhir']) {
                                    echo $row['halaman_terakhir'] . ' dari ' . $row['total_halaman'] . ' halaman';
                                } else {
                                    echo '0 dari ' . $row['total_halaman'] . ' halaman';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if($row['waktu_selesai']) {
                                    echo $row['formatted_durasi'];
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    padding: 0.5em 0.75em;
    font-size: 0.75em;
}
.badge-warning {
    background-color: #ffc107;
    color: #212529;
}
</style>

<?php include("footer.php"); ?> 