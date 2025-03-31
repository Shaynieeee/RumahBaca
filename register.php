<?php
require_once 'setting/koneksi.php';
require 'vendor/autoload.php'; // Pastikan PHPMailer sudah diinstal

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = "";
$success = "";

session_start();

if (!isset($_SESSION['captcha_code'])) {
    $_SESSION['captcha_code'] = rand(1000, 9999);
}

// Fungsi untuk generate OTP
function generateOTP() {
    return rand(100000, 999999);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_otp'])) {
        // Proses verifikasi OTP
        $input_otp = $_POST['otp'];
        $session_otp = $_SESSION['otp'];
        $user_data = $_SESSION['temp_user_data'];

        if ($input_otp == $session_otp) {
            // OTP valid, lanjutkan dengan registrasi
            mysqli_begin_transaction($db);
            try {
                // Generate Nomor Anggota
                $today = date('Ymd');
                $query = "SELECT MAX(SUBSTRING(no_anggota, 12)) as max_num FROM t_anggota WHERE SUBSTRING(no_anggota, 4, 8) = '$today'";
                $result = mysqli_query($db, $query);
                $row = mysqli_fetch_assoc($result);
                $next_num = str_pad(($row['max_num'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
                $no_anggota = "AGT" . $today . $next_num;

                // Insert ke tabel t_anggota
                $sql_anggota = "INSERT INTO t_anggota (no_anggota, nama, tgl_daftar, tgl_lahir, jenis_kelamin, no_telp, email, alamat, status, create_date, create_by) 
                                VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, 'Aktif', CURDATE(), ?)";
                $stmt_anggota = mysqli_prepare($db, $sql_anggota);
                mysqli_stmt_bind_param($stmt_anggota, "ssssssss", 
                    $no_anggota,
                    $user_data['nama'],
                    $user_data['tgl_lahir'],
                    $user_data['jenis_kelamin'],
                    $user_data['no_telp'],
                    $user_data['email'],
                    $user_data['alamat'],
                    $user_data['username']
                );
                mysqli_stmt_execute($stmt_anggota);
                $id_t_anggota = mysqli_insert_id($db);

                // Insert ke tabel t_account
                $hashed_password = password_hash($user_data['password'], PASSWORD_DEFAULT);
                $sql_account = "INSERT INTO t_account (id_p_role, id_t_anggota, username, password, email, create_date, create_by) 
                               VALUES (3, ?, ?, ?, ?, CURDATE(), ?)";
                $stmt_account = mysqli_prepare($db, $sql_account);
                mysqli_stmt_bind_param($stmt_account, "issss", 
                    $id_t_anggota,
                    $user_data['username'],
                    $hashed_password,
                    $user_data['email'],
                    $user_data['username']
                );
                mysqli_stmt_execute($stmt_account);
                $id_t_account = mysqli_insert_id($db);

                // Update t_anggota dengan id_t_account
                $sql_update = "UPDATE t_anggota SET id_t_account = ? WHERE id_t_anggota = ?";
                $stmt_update = mysqli_prepare($db, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "ii", $id_t_account, $id_t_anggota);
                mysqli_stmt_execute($stmt_update);

                mysqli_commit($db);
                unset($_SESSION['otp']);
                unset($_SESSION['temp_user_data']);
                
                $success = "Registrasi berhasil! Silakan login.";
                header("refresh:2;url=login.php");
                
            } catch (Exception $e) {
                mysqli_rollback($db);
                $error = "Gagal mendaftar: " . $e->getMessage();
            }
        } else {
            $error = "Kode OTP tidak valid. Silakan coba lagi.";
        }
    } else {
        // Proses form registrasi awal
        $username = mysqli_real_escape_string($db, $_POST['username']);
        $password = $_POST['password'];
        $nama = mysqli_real_escape_string($db, $_POST['nama']);
        $tgl_lahir = $_POST['tgl_lahir'];
        $jenis_kelamin = mysqli_real_escape_string($db, $_POST['jenis_kelamin']);
        $no_telp = mysqli_real_escape_string($db, $_POST['no_telp']);
        $email = mysqli_real_escape_string($db, $_POST['email']);
        $alamat = mysqli_real_escape_string($db, $_POST['alamat']);

        // Validasi input
        if (strlen($password) < 8) {
            $error = "Password minimal 8 karakter.";
        } else {
            // Generate dan kirim OTP
            $otp = generateOTP();
            $_SESSION['otp'] = $otp;
            $_SESSION['temp_user_data'] = [
                'username' => $username,
                'password' => $password,
                'nama' => $nama,
                'tgl_lahir' => $tgl_lahir,
                'jenis_kelamin' => $jenis_kelamin,
                'no_telp' => $no_telp,
                'email' => $email,
                'alamat' => $alamat
            ];

            // Kirim OTP melalui email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'pelindo.subregjawa@gmail.com'; // Ganti dengan email Gmail Anda
                $mail->Password = 'vcpjhbtmbryvcikp'; // Ganti dengan App Password Gmail Anda
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('your-email@gmail.com', 'Perpustakaan');
                $mail->addAddress($email, $nama);
                $mail->isHTML(true);
                $mail->Subject = 'Kode OTP Registrasi Perpustakaan';
                $mail->Body = "Halo $nama,<br><br>
                              Berikut adalah kode OTP untuk registrasi akun Anda:<br>
                              <h2>$otp</h2><br>
                              Kode ini akan kadaluarsa dalam 5 menit.<br><br>
                              Jika Anda tidak merasa mendaftar di Perpustakaan kami, abaikan email ini.";

                $mail->send();
                $success = "Kode OTP telah dikirim ke email Anda.";
            } catch (Exception $e) {
                $error = "Gagal mengirim OTP: " . $mail->ErrorInfo;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Perpustakaan</title>
    
    <!-- Multiple favicon sizes -->
    <link rel="icon" type="image/png" sizes="32x32" href="public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="public/assets/pelindo-logo.png">
    
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
        .otp-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .otp-modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }
        .alert {
            margin-bottom: 15px;
            border-radius: 8px;
            font-size: 14px;
            padding: 12px 15px;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Register</h2>
                        
                        <!-- <?php if($error != ""): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if($success != ""): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?> -->

                        <form method="post" id="registerForm">
                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" required>
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
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted">Minimal 8 karakter</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                        <div class="text-center mt-3">
                            Sudah punya akun? <a href="login.php">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal OTP -->
    <div id="otpModal" class="otp-modal">
        <div class="otp-modal-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="m-0">Verifikasi OTP</h4>
                <button type="button" class="btn-close" onclick="document.getElementById('otpModal').style.display='none'"></button>
            </div>
            
            <?php if(isset($_SESSION['otp'])): ?>
                <?php if($error != "" && strpos($error, 'OTP') !== false): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success != "" && strpos($success, 'OTP') !== false): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <p class="text-center">Masukkan kode OTP yang telah dikirim ke email Anda</p>
            <form method="post">
                <div class="mb-3">
                    <input type="text" name="otp" class="form-control form-control-lg text-center" 
                           maxlength="6" required placeholder="Masukkan kode OTP">
                </div>
                <input type="hidden" name="verify_otp" value="1">
                <button type="submit" class="btn btn-primary w-100">Verifikasi</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if(isset($_SESSION['otp']) || (isset($error) && strpos($error, 'OTP') !== false)): ?>
        document.getElementById('otpModal').style.display = 'block';
        <?php endif; ?>

        // Tampilkan pesan dalam modal
        <?php if($success != "" && strpos($success, 'OTP') !== false): ?>
        setTimeout(function() {
            document.getElementById('otpModal').style.display = 'block';
        }, 100);
        <?php endif; ?>
    </script>
</body>
</html>
