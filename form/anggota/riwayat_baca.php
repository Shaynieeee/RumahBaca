<?php
include("header_anggota.php");

$username = $_SESSION['login_user'];

// Dapatkan id_anggota
$sql_anggota = "SELECT id_t_anggota FROM t_account WHERE username = ?";
$stmt = mysqli_prepare($db, $sql_anggota);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$anggota = mysqli_fetch_assoc($result);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Riwayat Baca</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Buku</th>
                                    <th>Tanggal</th>
                                    <th>Mulai Baca</th>
                                    <th>Selesai Baca</th>
                                    <th>Durasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT r.*, b.nama_buku 
                                        FROM t_riwayat_baca r
                                        INNER JOIN t_buku b ON r.id_t_buku = b.id_t_buku
                                        WHERE r.id_t_anggota = ?
                                        ORDER BY r.tanggal_baca DESC, r.waktu_mulai DESC";
                                
                                $stmt = mysqli_prepare($db, $sql);
                                mysqli_stmt_bind_param($stmt, "i", $anggota['id_t_anggota']);
                                mysqli_stmt_execute($stmt);
                                $result = mysqli_stmt_get_result($stmt);
                                
                                $no = 1;
                                while($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_buku']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_baca'])); ?></td>
                                        <td><?php echo $row['waktu_mulai']; ?></td>
                                        <td>
                                            <?php 
                                            echo $row['waktu_selesai'] 
                                                ? $row['waktu_selesai']
                                                : '<span class="badge bg-warning text-dark">Masih membaca</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            echo $row['durasi'] 
                                                ? $row['durasi']
                                                : '-';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                
                                if(mysqli_num_rows($result) == 0) {
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada riwayat baca</td>
                                    </tr>
                                    <?php
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

<?php include("footer.php"); ?>

<style>
.card {
    margin-top: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
.table th {
    background-color: #f8f9fa;
}
.badge {
    padding: 5px 10px;
    border-radius: 4px;
}
.bg-warning {
    background-color: #ffc107;
}
</style> 