<?php
session_start();
require_once '../../setting/koneksi.php';

// Cegah Session Fixation
session_regenerate_id(true);

// Cek login dan peran
if (!isset($_SESSION['login_user']) || $_SESSION['role'] != 3) {
    header("location: ../../login.php");
    exit();
}

// Ambil data anggota dengan prepared statement
$username = $_SESSION['login_user'];
$sql = "SELECT a.*, acc.id_p_role FROM t_anggota a 
        JOIN t_account acc ON a.id_t_anggota = acc.id_t_anggota 
        WHERE acc.username = ?";

if ($stmt = mysqli_prepare($db, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $anggota = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    die("Terjadi kesalahan saat mengambil data.");
}

include("header_anggota.php");
?>

<div class="container py-4 ">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card" style="margin-top: -21px;">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-circle mr-2"></i> Profil Anggota</h4>
                </div>
                <div class="card-body">
                    <?php if ($anggota): ?>
                        <!-- Notifikasi status akun -->
                        <?php if ($anggota['status'] != 'Aktif'): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Akun Anda tidak aktif. Hubungi admin untuk aktivasi.
                            </div>
                        <?php endif; ?>

                        <!-- Gambar Profil
                        <div class="text-center mb-3">
                            <?php
                            $fotoProfil = '../../uploads/' . htmlspecialchars($anggota['foto_profil']);
                            if (empty($anggota['foto_profil']) || !file_exists($fotoProfil)) {
                                $fotoProfil = '../../assets/alternate-profile.png';
                            }
                            ?>
                            <img src="<?php echo $fotoProfil; ?>" 
                                 class="rounded-circle img-fluid" width="150" height="150" alt="Foto Profil">
                        </div> -->

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
                                <th>Tanggal Pendaftaran</th>
                                <td><?php echo isset($anggota['tgl_daftar']) ? date('d F Y', strtotime($anggota['tgl_daftar'])) : 'Tidak tersedia'; ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <?php 
                                $statusClass = ($anggota['status'] == 'Aktif') ? 'success' : 'danger';
                                ?>
                                <td>
                                    <span class="badge badge-<?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($anggota['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Tombol Edit Profil dan Cetak Kartu Anggota -->
                        <div class="d-flex justify-content-between mt-3">
                            <a href="edit_profil.php" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Profil
                            </a>
                            <a href="cetak_kartu.php?id=<?php echo urlencode($anggota['id_t_anggota']); ?>" target="_blank" class="btn btn-success">
                                <i class="fas fa-id-card"></i> Cetak Kartu Anggota
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle mr-2"></i> Data anggota tidak ditemukan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tombol Logout -->
<div class="text-center mb-3">
    <a href="../../logout.php" class="btn btn-danger">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<?php include("footer.php"); ?>
