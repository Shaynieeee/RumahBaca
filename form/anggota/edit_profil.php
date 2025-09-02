<?php
session_start();
require_once '../../setting/koneksi.php';

// Ambil scopes user
$scopes = [];
if (isset($_SESSION['login_user'])) {
    $u = mysqli_real_escape_string($db, $_SESSION['login_user']);
    $sql_s = "SELECT s.name FROM t_account a
              JOIN t_role_scope rs ON a.id_p_role = rs.role_id
              JOIN t_scope s ON rs.scope_id = s.id
              WHERE a.username = '$u'";
    $rs = mysqli_query($db, $sql_s);
    if($rs){
        while($r = mysqli_fetch_assoc($rs)) $scopes[] = strtolower(trim($r['name']));
    }
}

// akses berdasarkan scope
if (!isset($_SESSION['login_user']) || !in_array('profil-member', $scopes)) {
    header("location: ../../login.php");
    exit();
}


// Cegah Session Fixation
session_regenerate_id(true);

$error = "";
$success = "";

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

// Proses update profil
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $no_telp = mysqli_real_escape_string($db, $_POST['no_telp']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $alamat = mysqli_real_escape_string($db, $_POST['alamat']);
    $jenis_kelamin = mysqli_real_escape_string($db, $_POST['jenis_kelamin']);
    $tgl_lahir = $_POST['tgl_lahir'];
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    
    if (!is_null($password) && strlen($password) < 8) {
        $error = "Password minimal 8 karakter";
    } else {
        mysqli_begin_transaction($db);
        try {
            // Update data anggota
            $sql_update = "UPDATE t_anggota SET 
                          nama = ?, 
                          no_telp = ?, 
                          email = ?, 
                          alamat = ?, 
                          jenis_kelamin = ?, 
                          tgl_lahir = ?,
                          update_date = NOW(),
                          update_by = ?
                          WHERE id_t_anggota = ?";
            
            $stmt_update = mysqli_prepare($db, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "sssssssi", 
                $nama, $no_telp, $email, $alamat, $jenis_kelamin, 
                $tgl_lahir, $username, $anggota['id_t_anggota']);
            mysqli_stmt_execute($stmt_update);
            
            // Update password jika diisi
            if (!is_null($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql_password = "UPDATE t_account SET password = ? WHERE id_t_anggota = ?";
                $stmt_password = mysqli_prepare($db, $sql_password);
                mysqli_stmt_bind_param($stmt_password, "si", $hashed_password, $anggota['id_t_anggota']);
                mysqli_stmt_execute($stmt_password);
            }
            
            mysqli_commit($db);
            $success = "Profil berhasil diperbarui!";
            
            // Refresh data anggota
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $anggota = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            mysqli_rollback($db);
            $error = "Gagal memperbarui profil: " . $e->getMessage();
        }
    }
}

include("header_anggota.php");
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-edit mr-2"></i> Edit Profil</h4>
                </div>
                <div class="card-body">
                    <?php if($error != ""): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if($success != ""): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if ($anggota): ?>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" required 
                                       value="<?php echo htmlspecialchars($anggota['nama']); ?>">
                            </div>
                            <div class="mb-3">
                                <label>Tanggal Lahir</label>
                                <input type="date" name="tgl_lahir" class="form-control" required
                                       value="<?php echo htmlspecialchars($anggota['tgl_lahir']); ?>">
                            </div>
                            <div class="mb-3">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-control" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki" <?php echo ($anggota['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?php echo ($anggota['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>No. Telepon</label>
                                <input type="tel" name="no_telp" class="form-control" required
                                       value="<?php echo htmlspecialchars($anggota['no_telp']); ?>">
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?php echo htmlspecialchars($anggota['email']); ?>">
                            </div>
                            <div class="mb-3">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($anggota['alamat']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="profil.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
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

<?php include("footer.php"); ?> 