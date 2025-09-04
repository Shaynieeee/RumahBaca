<?php
session_start();
// Cek apakah yang mengakses adalah admin
if (!isset($scopes) || !(in_array('staff', $scopes))) {
	header("Location: ../../landing.php");
	exit;
}

require_once '../setting/koneksi.php';

if(isset($_POST['btn-signup'])) {
    $uname = strip_tags($_POST['username']);
    $upass = strip_tags($_POST['password']);
    $nama = strip_tags($_POST['nama']);
    $email = strip_tags($_POST['email']);
    $no_hp = strip_tags($_POST['no_hp']);
    $role = strip_tags($_POST['role']); // 1 untuk admin, 2 untuk staff
    
    // Escape string
    $uname = $db->real_escape_string($uname);
    $upass = $db->real_escape_string($upass);
    $nama = $db->real_escape_string($nama);
    $email = $db->real_escape_string($email);
    $no_hp = $db->real_escape_string($no_hp);
    
    // Hash password
    $hashed_password = password_hash($upass, PASSWORD_DEFAULT);
    
    // Cek username sudah ada atau belum
    $check_account = $db->query("SELECT username FROM t_account WHERE username='$uname'");
    $count = $check_account->num_rows;
    
    if ($count == 0) {
        // Begin transaction
        $db->begin_transaction();
        
        try {
            // Insert ke t_account
            $query_account = "INSERT INTO t_account(id_p_role, username, password, create_date, create_by) 
                            VALUES($role, '$uname', '$hashed_password', CURDATE(), '{$_SESSION['login_user']}')";
            $db->query($query_account);
            
            // Ambil id_t_account yang baru dibuat
            $id_account = $db->insert_id;
            
            // Insert ke t_petugas
            $query_petugas = "INSERT INTO t_petugas(id_t_account, nama, email, no_hp, create_date, create_by) 
                            VALUES($id_account, '$nama', '$email', '$no_hp', CURDATE(), '{$_SESSION['login_user']}')";
            $db->query($query_petugas);
            
            // Commit transaction
            $db->commit();
            $success = "Akun petugas berhasil dibuat!";
            
        } catch (Exception $e) {
            // Rollback jika terjadi error
            $db->rollback();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $error = "Username sudah dipakai!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrasi Petugas | Perpustakaan</title>
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Registrasi Petugas Baru</h3>
                    </div>
                    <div class="panel-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success">
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <fieldset>
                                <div class="form-group">
                                    <label>Role</label>
                                    <select name="role" class="form-control" required>
                                        <option value="">Pilih Role</option>
                                        <option value="1">Admin</option>
                                        <option value="2">Staff</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" class="form-control" 
                                           name="username" required>
                                </div>

                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" class="form-control" 
                                           name="password" required>
                                </div>

                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input type="text" class="form-control" 
                                           name="nama" required>
                                </div>

                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" 
                                           name="email" required>
                                </div>

                                <div class="form-group">
                                    <label>No. HP</label>
                                    <input type="text" class="form-control" 
                                           name="no_hp" required>
                                </div>

                                <button type="submit" name="btn-signup" 
                                        class="btn btn-lg btn-success btn-block">
                                    Buat Akun Petugas
                                </button>
                                
                                <a href="dashboard.php" class="btn btn-lg btn-default btn-block">
                                    Kembali ke Dashboard
                                </a>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>
    <script src="../dist/js/sb-admin-2.js"></script>
</body>
</html> 