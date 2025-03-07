<?php
require_once '../setting/koneksi.php';
require_once '../setting/session.php';

$error = "";
$success = "";

// Inisialisasi variabel
$id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : '';
$tampil_data = array(
    'nama' => '',
    'tgl_lahir' => '',
    'jenis_kelamin' => '',
    'no_telp' => '',
    'alamat' => '',
    'status' => '',
    'keterangan' => '',
    'username' => ''
);

// Jika mode edit
if(!empty($id)) {
    $sql = "SELECT * FROM t_anggota WHERE id_t_anggota = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tampil_data = mysqli_fetch_array($result, MYSQLI_ASSOC);
}

// Generate no_anggota (AGT + 8 digit random number)
function generateUniqueCode($db) {
    do {
        // Generate 8 digit random number
        $random = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $no_anggota = "AGT" . $random;
        
        // Check if number already exists
        $query = "SELECT no_anggota FROM t_anggota WHERE no_anggota = '$no_anggota'";
        $result = mysqli_query($db, $query);
        $exists = mysqli_num_rows($result) > 0;
    } while($exists);
    
    return $no_anggota;
}

if(isset($_POST['btnsubmit'])) {
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $tgl_lahir = mysqli_real_escape_string($db, $_POST['tgl_lahir']);
    $jenis_kelamin = mysqli_real_escape_string($db, $_POST['jenis_kelamin']);
    $no_telp = mysqli_real_escape_string($db, $_POST['no_telp']);
    $alamat = mysqli_real_escape_string($db, $_POST['alamat']);
    $status = mysqli_real_escape_string($db, $_POST['status']);
    $keterangan = mysqli_real_escape_string($db, $_POST['keterangan']);
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $user = $_SESSION['login_user'];
    
    try {
        mysqli_begin_transaction($db);
        
        if(empty($id)) {
            // Mode tambah
            if(empty($password)) {
                $error = "Password harus diisi untuk anggota baru";
                throw new Exception($error);
            }
            
            if(strlen($password) < 8) {
                $error = "Password minimal 8 karakter";
                throw new Exception($error);
            }

            $no_anggota = generateUniqueCode($db);
            
            // Insert anggota
            $query = "INSERT INTO t_anggota (no_anggota, nama, tgl_daftar, tgl_lahir, 
                     jenis_kelamin, no_telp, alamat, status, keterangan, 
                     create_by, create_date) 
                     VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, CURDATE())";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "sssssssss", 
                                 $no_anggota,    // 1
                                 $nama,          // 2
                                 $tgl_lahir,     // 3
                                 $jenis_kelamin, // 4
                                 $no_telp,       // 5
                                 $alamat,        // 6
                                 $status,        // 7
                                 $keterangan,    // 8
                                 $user);         // 9
            mysqli_stmt_execute($stmt);
            $anggota_id = mysqli_insert_id($db);
            
            // Insert account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $account_query = "INSERT INTO t_account (username, password, id_p_role, 
                            id_t_anggota, create_date, create_by) 
                            VALUES (?, ?, 3, ?, CURDATE(), ?)";
            $stmt_account = mysqli_prepare($db, $account_query);
            mysqli_stmt_bind_param($stmt_account, "ssis", $username, $hashed_password, 
                                 $anggota_id, $user);
            mysqli_stmt_execute($stmt_account);
            
        } else {
            // Mode edit
            $query = "UPDATE t_anggota SET nama=?, tgl_lahir=?, jenis_kelamin=?, 
                     no_telp=?, alamat=?, status=?, keterangan=?, 
                     update_by=?, update_date=CURDATE() WHERE id_t_anggota=?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "ssssssssi", $nama, $tgl_lahir, $jenis_kelamin, 
                                 $no_telp, $alamat, $status, $keterangan, $user, $id);
            mysqli_stmt_execute($stmt);
            
            if(!empty($password)) {
                if(strlen($password) < 8) {
                    $error = "Password minimal 8 karakter";
                    throw new Exception($error);
                }
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_account = "UPDATE t_account SET password=?, update_by=?, 
                                 update_date=CURDATE() WHERE id_t_anggota=?";
                $stmt_account = mysqli_prepare($db, $update_account);
                mysqli_stmt_bind_param($stmt_account, "ssi", $hashed_password, $user, $id);
                mysqli_stmt_execute($stmt_account);
            }
        }
        
        mysqli_commit($db);
        $success = "Data berhasil disimpan";
        header("Location: data_anggota.php");
        exit();
        
    } catch(Exception $e) {
        mysqli_rollback($db);
        $error = $e->getMessage();
    }
}

include("header.php");
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><?php echo empty($id) ? 'Input' : 'Edit'; ?> Anggota</h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12">
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form class="form-horizontal" method="post">
                <div class="form-group">
                    <label class="control-label col-sm-2">Nama</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="nama" 
                               value="<?php echo isset($tampil_data['nama']) ? htmlspecialchars($tampil_data['nama']) : ''; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Tanggal Lahir</label>
                    <div class="col-sm-4">
                        <input type="date" class="form-control" name="tgl_lahir"
                               value="<?php echo isset($tampil_data['tgl_lahir']) ? htmlspecialchars($tampil_data['tgl_lahir']) : ''; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Jenis Kelamin</label>
                    <div class="col-sm-4">
                        <select name="jenis_kelamin" class="form-control" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" <?php echo (isset($tampil_data['jenis_kelamin']) && $tampil_data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo (isset($tampil_data['jenis_kelamin']) && $tampil_data['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">No. Telepon</label>
                    <div class="col-sm-4">
                        <input type="tel" class="form-control" name="no_telp" 
                               value="<?php echo isset($tampil_data['no_telp']) ? htmlspecialchars($tampil_data['no_telp']) : ''; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Alamat</label>
                    <div class="col-sm-4">
                        <textarea class="form-control" name="alamat" 
                                  rows="3" required><?php echo isset($tampil_data['alamat']) ? htmlspecialchars($tampil_data['alamat']) : ''; ?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Status</label>
                    <div class="col-sm-4">
                        <select class="form-control" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="Aktif" <?php echo (isset($tampil_data['status']) && $tampil_data['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Tidak Aktif" <?php echo (isset($tampil_data['status']) && $tampil_data['status'] == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Keterangan</label>
                    <div class="col-sm-4">
                        <textarea class="form-control" name="keterangan" 
                                  rows="3"><?php echo isset($tampil_data['keterangan']) ? htmlspecialchars($tampil_data['keterangan']) : ''; ?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Username</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="username" 
                               value="<?php echo isset($tampil_data['username']) ? htmlspecialchars($tampil_data['username']) : ''; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Password</label>
                    <div class="col-sm-4">
                        <input type="password" class="form-control" name="password" 
                               <?php echo empty($id) ? 'required' : ''; ?> 
                               minlength="8">
                        <?php if(!empty($id)): ?>
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        <?php else: ?>
                            <small class="text-muted">Minimal 8 karakter</small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                        <button type="submit" name="btnsubmit" class="btn btn-primary">Simpan</button>
                        <a href="data_anggota.php" class="btn btn-default">Kembali</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// include "../template/footer.php"
?>