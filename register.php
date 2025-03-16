<?php
require_once 'setting/koneksi.php';

$error = "";
$success = "";

session_start();

if (!isset($_SESSION['captcha_code'])) {
    $_SESSION['captcha_code'] = rand(1000, 9999);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = !empty($_POST['username']) ? mysqli_real_escape_string($db, $_POST['username']) : null;
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $no_telp = mysqli_real_escape_string($db, $_POST['no_telp']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $alamat = mysqli_real_escape_string($db, $_POST['alamat']);
    $jenis_kelamin = mysqli_real_escape_string($db, $_POST['jenis_kelamin']);
    $tgl_lahir = $_POST['tgl_lahir'];
    $captcha_input = $_POST['captcha'];
    
    if ($captcha_input != $_SESSION['captcha_code']) {
        $error = "Kode CAPTCHA salah.";
    } elseif (!is_null($password) && strlen($password) < 8) {
        $error = "Password minimal 8 karakter";
    } else {
        mysqli_begin_transaction($db);
        try {
            $today = date('Ymd');
            $query = "SELECT MAX(SUBSTRING(no_anggota, 12)) as max_num FROM t_anggota WHERE SUBSTRING(no_anggota, 4, 8) = '$today'";
            $result = mysqli_query($db, $query);
            $row = mysqli_fetch_assoc($result);
            $next_num = str_pad(($row['max_num'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
            $no_anggota = "AGT" . $today . $next_num;
            
            $sql_anggota = "INSERT INTO t_anggota (no_anggota, nama, tgl_daftar, tgl_lahir, jenis_kelamin, no_telp, email, alamat, status, create_date, create_by) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, 'Aktif', CURDATE(), ?)";
            $stmt_anggota = mysqli_prepare($db, $sql_anggota);
            mysqli_stmt_bind_param($stmt_anggota, "ssssssss", $no_anggota, $nama, $tgl_lahir, $jenis_kelamin, $no_telp, $email, $alamat, $username);
            mysqli_stmt_execute($stmt_anggota);
            $id_t_anggota = mysqli_insert_id($db);
            
            if (!is_null($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql_account = "INSERT INTO t_account (id_p_role, id_t_anggota, username, password, create_date, create_by) VALUES (3, ?, ?, ?, NOW(), ?)";
                $stmt_account = mysqli_prepare($db, $sql_account);
                mysqli_stmt_bind_param($stmt_account, "isss", $id_t_anggota, $username, $hashed_password, $username);
                mysqli_stmt_execute($stmt_account);
            }
            
            mysqli_commit($db);
            $success = "Registrasi berhasil! Silahkan login.";
            header("refresh:2;url=login.php");
        } catch (Exception $e) {
            mysqli_rollback($db);
            $error = "Gagal mendaftar: " . $e->getMessage();
        }
    }
    $_SESSION['captcha_code'] = rand(1000, 9999);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Rumah Baca</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
        }
        .register-form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            margin-top: 50px;
        }
        .captcha-box {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            background: #f8f9fa;
            padding: 10px;
            display: inline-block;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="register-form">
                    <h2 class="text-center mb-4">Register Anggota</h2>
                    
                    <?php if($error != ""): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if($success != ""): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>No. Telepon</label>
                            <input type="tel" name="no_telp" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Captcha</label><br>
                            <span class="captcha-box"><?php echo $_SESSION['captcha_code']; ?></span>
                            <input type="text" name="captcha" class="form-control mt-2" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                    <div class="text-center mt-3">
                        Sudah punya akun? <a href="login.php">Login di sini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
