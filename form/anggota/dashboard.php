<?php
session_start();
require_once '../../setting/koneksi.php';

// Cek apakah user sudah login
if(!isset($_SESSION['login_user']) || $_SESSION['role'] != 3) {
    header("location: ../../login.php");
    exit();
}

// Ambil data anggota yang login
$username = $_SESSION['login_user'];
$sql = "SELECT a.*, acc.username 
        FROM t_anggota a 
        JOIN t_account acc ON acc.id_t_anggota = a.id_t_anggota 
        WHERE acc.username = ?";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$anggota = mysqli_fetch_assoc($result);

// Ambil data buku terbaru
$sql_buku = "SELECT * FROM t_buku ORDER BY create_date DESC LIMIT 4";
$result_buku = mysqli_query($db, $sql_buku);

include("header_anggota.php");
?>

<div id="page-wrapper">
    <!-- Hero Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">

            <!-- Buku Terbaru -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-book"></i> Buku Terbaru</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <?php
                                if ($result_buku && mysqli_num_rows($result_buku) > 0) {
                                    while($row = mysqli_fetch_assoc($result_buku)) {
                                ?>
                                <div class="col-md-3 mb-4">
                                    <div class="card h-100">
                                        <img src="../../image/buku/<?php echo htmlspecialchars($row['gambar'] ?? 'default.jpg'); ?>" 
                                             class="card-img-top" alt="<?php echo htmlspecialchars($row['nama_buku'] ?? 'Tidak ada judul'); ?>"
                                             style="height: 250px; object-fit: cover;">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title text-truncate"><?php echo htmlspecialchars($row['nama_buku'] ?? 'Tidak ada judul'); ?></h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="fa fa-user"></i> <?php echo htmlspecialchars($row['penulis'] ?? 'Tidak ada penulis'); ?><br>
                                                    <i class="fa fa-calendar"></i> <?php echo htmlspecialchars($row['tahun_terbit'] ?? 'Tidak ada tahun'); ?>
                                                </small>
                                            </p>
                                            <div class="btn-group mt-auto w-100">
                                                <a href="detail-buku.php?id=<?php echo $row['id_t_buku']; ?>" 
                                                   class="btn btn-info btn-sm">
                                                    <i class="fa fa-info-circle"></i> Detail
                                                </a>    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                    }
                                } else {
                                    echo "<p class='text-center'>Tidak ada buku terbaru</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include "footer.php"; ?> 