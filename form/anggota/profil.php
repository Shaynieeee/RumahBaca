<?php
session_start();
require_once '../../setting/koneksi.php';

// Cek login
if(!isset($_SESSION['login_user']) || $_SESSION['role'] != 3) {
    header("location: ../../login.php");
    exit();
}

// Ambil data anggota
$username = $_SESSION['login_user'];
$sql = "SELECT a.* FROM t_anggota a 
        JOIN t_account acc ON a.id_t_anggota = acc.id_t_anggota 
        WHERE acc.username = ?";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$anggota = mysqli_fetch_assoc($result);

include("header_anggota.php");
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-circle mr-2"></i>Profil Anggota</h4>
                </div>
                <div class="card-body">
                    <?php if ($anggota): ?>
                    <table class="table table-hover">
                        <tr>
                            <th width="200">No. Anggota</th>
                            <td><?php echo htmlspecialchars($anggota['no_anggota']); ?></td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td><?php echo htmlspecialchars($anggota['nama']); ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Daftar</th>
                            <td><?php echo date('d F Y', strtotime($anggota['tgl_daftar'])); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge badge-<?php echo ($anggota['status'] == 'Aktif') ? 'success' : 'danger'; ?>">
                                    <?php echo htmlspecialchars($anggota['status']); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i>Data anggota tidak ditemukan
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?> 