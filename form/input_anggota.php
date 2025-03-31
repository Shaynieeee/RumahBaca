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
    'status' => 'Aktif',
    'keterangan' => '',
    'username' => '',
    'email' => '',
    'id_t_account' => 0
);

// Jika mode edit
if(!empty($id)) {
    $sql = "SELECT a.*, ac.username, ac.email, ac.id_t_account 
            FROM t_anggota a 
            LEFT JOIN t_account ac ON a.id_t_account = ac.id_t_account 
            WHERE a.id_t_anggota = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if($row = mysqli_fetch_assoc($result)) {
        $tampil_data = array_merge($tampil_data, $row);
    }
}

if(isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $tgl_lahir = mysqli_real_escape_string($db, $_POST['tgl_lahir']);
    $jenis_kelamin = mysqli_real_escape_string($db, $_POST['jenis_kelamin']);
    $no_telp = mysqli_real_escape_string($db, $_POST['no_telp']);
    $alamat = mysqli_real_escape_string($db, $_POST['alamat']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $status = mysqli_real_escape_string($db, $_POST['status']);
    $keterangan = mysqli_real_escape_string($db, $_POST['keterangan']);
    $create_by = substr($_SESSION['login_user'], 0, 3);
    
    mysqli_begin_transaction($db);
    
    try {
        if(empty($id)) {
            // Mode tambah baru
            // Generate Nomor Anggota
            $today = date('Ymd');
            $query = "SELECT MAX(SUBSTRING(no_anggota, 12)) as max_num 
                      FROM t_anggota 
                      WHERE SUBSTRING(no_anggota, 4, 8) = '$today'";
            $result = mysqli_query($db, $query);
            $row = mysqli_fetch_assoc($result);
            $next_num = str_pad(($row['max_num'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
            $no_anggota = "AGT" . $today . $next_num;
            
            // Insert ke t_anggota dulu
            $sql_anggota = "INSERT INTO t_anggota (no_anggota, nama, tgl_daftar, tgl_lahir, 
                                                  jenis_kelamin, no_telp, alamat, status, keterangan, 
                                                  create_by, create_date, email) 
                           VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)";
            $stmt = mysqli_prepare($db, $sql_anggota);
            
            mysqli_stmt_bind_param($stmt, "ssssssssss", 
                $no_anggota, 
                $nama, 
                $tgl_lahir, 
                $jenis_kelamin, 
                $no_telp, 
                $alamat, 
                $status, 
                $keterangan, 
                $create_by,
                $email
            );
            
            if(!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating member: " . mysqli_error($db));
            }
            
            $id_t_anggota = mysqli_insert_id($db);
            
            // Insert ke t_account
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_account = "INSERT INTO t_account (id_p_role, id_t_anggota, username, password, create_date, create_by, email) 
                           VALUES (3, ?, ?, ?, NOW(), ?, ?)";
            $stmt = mysqli_prepare($db, $sql_account);
            mysqli_stmt_bind_param($stmt, "issss", $id_t_anggota, $username, $hash_password, $create_by, $email);
            
            if(!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating account: " . mysqli_error($db));
            }
            
            $id_t_account = mysqli_insert_id($db);
            
            // Update t_anggota dengan id_t_account
            $sql_update = "UPDATE t_anggota SET id_t_account = ? WHERE id_t_anggota = ?";
            $stmt = mysqli_prepare($db, $sql_update);
            mysqli_stmt_bind_param($stmt, "ii", $id_t_account, $id_t_anggota);
            
            if(!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating member with account ID: " . mysqli_error($db));
            }
        } else {
            // Mode edit
            // Update t_anggota
            $sql_anggota = "UPDATE t_anggota SET 
                           nama = ?, tgl_lahir = ?, jenis_kelamin = ?, 
                           no_telp = ?, alamat = ?, status = ?, 
                           keterangan = ?, update_by = ?, 
                           update_date = CURDATE(), email = ? 
                           WHERE id_t_anggota = ?";
            $stmt = mysqli_prepare($db, $sql_anggota);
            mysqli_stmt_bind_param($stmt, "sssssssssi", 
                $nama, $tgl_lahir, $jenis_kelamin, $no_telp, 
                $alamat, $status, $keterangan, 
                $create_by, $email, $id
            );
            
            if(!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating member: " . mysqli_error($db));
            }
            
            // Update t_account jika ada password baru atau email/username berubah
            $sql_account = "UPDATE t_account SET 
                           username = ?, email = ?, 
                           update_by = ?, update_date = NOW()";
            
            $params = array($username, $email, $create_by);
            $types = "sss";
            
            if(!empty($password)) {
                $sql_account .= ", password = ?";
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $params[] = $hash_password;
                $types .= "s";
            }
            
            $sql_account .= " WHERE id_t_account = ?";
            $params[] = $tampil_data['id_t_account'];
            $types .= "i";
            
            $stmt = mysqli_prepare($db, $sql_account);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            
            if(!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating account: " . mysqli_error($db));
            }
        }
        
        mysqli_commit($db);
        echo "<script>
                alert('Data berhasil disimpan!');
                window.location='data_anggota.php';
              </script>";
        exit;
        
    } catch (Exception $e) {
        mysqli_rollback($db);
        $error = $e->getMessage();
    }
}

include("header.php");
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><?php echo empty($id) ? 'Tambah' : 'Edit'; ?> Anggota</h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12">
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form class="form-horizontal" method="post">
                <div class="form-group">
                    <label class="control-label col-sm-2">Nama</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="nama" 
                               value="<?php echo htmlspecialchars($tampil_data['nama']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Email</label>
                    <div class="col-sm-4">
                        <input type="email" class="form-control" name="email" 
                               value="<?php echo htmlspecialchars($tampil_data['email']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Username</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="username" 
                               value="<?php echo htmlspecialchars($tampil_data['username']); ?>" required>
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
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Tanggal Lahir</label>
                    <div class="col-sm-4">
                        <input type="date" class="form-control" name="tgl_lahir"
                               value="<?php echo htmlspecialchars($tampil_data['tgl_lahir']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Jenis Kelamin</label>
                    <div class="col-sm-4">
                        <select name="jenis_kelamin" class="form-control" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" <?php echo ($tampil_data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo ($tampil_data['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">No. Telepon</label>
                    <div class="col-sm-4">
                        <input type="tel" class="form-control" name="no_telp" 
                               value="<?php echo htmlspecialchars($tampil_data['no_telp']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Alamat</label>
                    <div class="col-sm-4">
                        <textarea class="form-control" name="alamat" rows="3" required><?php echo htmlspecialchars($tampil_data['alamat']); ?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Status</label>
                    <div class="col-sm-4">
                        <select class="form-control" name="status" required>
                            <option value="Aktif" <?php echo ($tampil_data['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Tidak Aktif" <?php echo ($tampil_data['status'] == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Keterangan</label>
                    <div class="col-sm-4">
                        <textarea class="form-control" name="keterangan" rows="3"><?php echo htmlspecialchars($tampil_data['keterangan']); ?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-4">
                        <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
                        <a href="data_anggota.php" class="btn btn-default">Kembali</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


