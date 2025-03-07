<?php
require_once 'setting/koneksi.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = $_POST['password'];
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $no_telp = mysqli_real_escape_string($db, $_POST['no_telp']);
    $alamat = mysqli_real_escape_string($db, $_POST['alamat']);
    $jenis_kelamin = mysqli_real_escape_string($db, $_POST['jenis_kelamin']);
    $tgl_lahir = $_POST['tgl_lahir'];
    
    // Validasi
    if (strlen($password) < 8) {
        $error = "Password minimal 8 karakter";
    } else {
        // Begin transaction
        mysqli_begin_transaction($db);
        try {
            // Generate no_anggota (AGT + YYYYMMDD + 3 digit)
            $today = date('Ymd');
            $query = "SELECT MAX(SUBSTRING(no_anggota, 12)) as max_num 
                     FROM t_anggota 
                     WHERE SUBSTRING(no_anggota, 4, 8) = '$today'";
            $result = mysqli_query($db, $query);
            $row = mysqli_fetch_assoc($result);
            $next_num = str_pad(($row['max_num'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
            $no_anggota = "AGT" . $today . $next_num;
            
            // Insert ke t_anggota
            $sql_anggota = "INSERT INTO t_anggota (no_anggota, nama, tgl_daftar, tgl_lahir, 
                           jenis_kelamin, no_telp, alamat, status, create_date, create_by) 
                           VALUES (?, ?, CURDATE(), ?, ?, ?, ?, 'Aktif', CURDATE(), ?)";
            $stmt_anggota = mysqli_prepare($db, $sql_anggota);
            mysqli_stmt_bind_param($stmt_anggota, "sssssss", 
                                 $no_anggota, $nama, $tgl_lahir, $jenis_kelamin, 
                                 $no_telp, $alamat, $username);
            mysqli_stmt_execute($stmt_anggota);
            $id_t_anggota = mysqli_insert_id($db);
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert ke t_account
            $sql_account = "INSERT INTO t_account (id_p_role, id_t_anggota, username, password, 
                          create_date, create_by) 
                          VALUES (3, ?, ?, ?, NOW(), ?)";
            $stmt_account = mysqli_prepare($db, $sql_account);
            mysqli_stmt_bind_param($stmt_account, "isss", 
                                 $id_t_anggota, $username, $hashed_password, $username);
            mysqli_stmt_execute($stmt_account);
            
            // Commit transaction
            mysqli_commit($db);
            $success = "Registrasi berhasil! Silahkan login.";
            
            // Redirect ke login setelah 2 detik
            header("refresh:2;url=login.php");
            
        } catch (Exception $e) {
            // Rollback jika terjadi error
            mysqli_rollback($db);
            $error = "Gagal mendaftar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Rumah Baca</title>
    
          <!-- Multiple favicon sizes -->
          <link rel="icon" type="image/png" sizes="32x32" href="public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="public/assets/pelindo-logo.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-image: linear-gradient(135deg, #1d99ff, #fff);
        }
        .register-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="register-form">
                    <h2 class="text-center mb-4">Register Anggota</h2>
                    
                    <?php if($error != ""): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if($success != ""): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required autocomplete="off">
                        </div>
                        
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" 
                                   required minlength="8"  autocomplete="off">
                            <small class="text-muted">Minimal 8 karakter</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required  autocomplete="off">
                        </div>
                        
                        <div class="form-group">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control" required autocomplete="off">
                        </div>
                        
                        <div class="form-group">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>No. Telepon</label>
                            <input type="tel" name="no_telp" class="form-control" required autocomplete="off">
                        </div>
                        
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        Sudah punya akun? <a href="login.php">Login di sini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>