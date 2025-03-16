<?php
session_start();
require_once 'setting/koneksi.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['captcha'])) {
        $_SESSION['captcha'] = rand(1000, 9999);
    }

    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : NULL;
    $username = isset($_POST['username']) ? mysqli_real_escape_string($db, $_POST['username']) : NULL;
    $password = isset($_POST['password']) ? $_POST['password'] : NULL;
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : NULL;
    $nama = isset($_POST['nama']) ? mysqli_real_escape_string($db, $_POST['nama']) : NULL;
    $alamat = isset($_POST['alamat']) ? mysqli_real_escape_string($db, $_POST['alamat']) : NULL;
    $no_telp = isset($_POST['no_telp']) ? mysqli_real_escape_string($db, $_POST['no_telp']) : NULL;
    $captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';

    if (!isset($_SESSION['captcha']) || empty($_SESSION['captcha']) || $captcha !== $_SESSION['captcha']) {
        $error = "Captcha salah! Silakan coba lagi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email tidak valid";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter";
    } else {
        mysqli_begin_transaction($db);
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql_account = "INSERT INTO t_account (email, username, password, nama, alamat, no_telp, create_date, create_by) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
            $stmt_account = mysqli_prepare($db, $sql_account);
            mysqli_stmt_bind_param($stmt_account, "sssssss", $email, $username, $hashed_password, $nama, $alamat, $no_telp, $username);
            mysqli_stmt_execute($stmt_account);
            mysqli_commit($db);
            
            $success = "Registrasi berhasil! <a href='login.php'>Login di sini</a>.";
            unset($_SESSION['captcha']); // Hapus captcha setelah sukses
        } catch (Exception $e) {
            mysqli_rollback($db);
            $error = "Gagal mendaftar: " . $e->getMessage();
        }
    }

    // Regenerate captcha after submission
    $_SESSION['captcha'] = rand(1000, 9999);
}

// Generate Captcha for first load
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(1000, 9999);
}
$captcha_code = $_SESSION['captcha'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .register-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 700px;
        }
        .btn-custom {
            background-color: #2a5298;
            color: white;
        }
        .btn-custom:hover {
            background-color: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2 class="text-center">Register</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"> <?php echo $success; ?> </div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telp" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Captcha: <b><?php echo $captcha_code; ?></b></label>
                <input type="text" name="captcha" class="form-control">
            </div>
            <button type="submit" class="btn btn-custom w-100">Register</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary">Kembali ke Home</a>
        </div>
    </div>
</body>
</html>