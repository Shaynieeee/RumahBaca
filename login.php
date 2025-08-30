<?php
session_start();
include("setting/koneksi.php");

$error = "";
$old_username = "";

// generate captcha baru setiap kali halaman dimuat (GET)
// atau akan diganti ulang setelah POST jika terjadi error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['captcha_text'] = (string) rand(1000, 9999);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $mypassword = $_POST['password'] ?? '';
    $captcha_input = isset($_POST['captcha']) ? trim($_POST['captcha']) : '';

    // pastikan ada captcha session
    if (!isset($_SESSION['captcha_text'])) {
        $_SESSION['captcha_text'] = (string) rand(1000, 9999);
    }

    // validasi CAPTCHA dulu
    if ($captcha_input !== (string) $_SESSION['captcha_text']) {
        $error = "Kode captcha salah.";
        // buat captcha baru untuk ditampilkan kembali
        $_SESSION['captcha_text'] = (string) rand(1000, 9999);
    } else {
        // cek username di DB
        $sql = "SELECT a.*, r.nama_role FROM t_account a JOIN p_role r ON a.id_p_role = r.id_p_role WHERE a.username = ?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "s", $old_username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if (!$res || mysqli_num_rows($res) === 0) {
            $error = "Username tidak ditemukan.";
            $_SESSION['captcha_text'] = (string) rand(1000, 9999);
        } else {
            $row = mysqli_fetch_assoc($res);
            // cek password
            if (!password_verify($mypassword, $row['password'])) {
                $error = "Password yang Anda masukkan salah.";
                $_SESSION['captcha_text'] = (string) rand(1000, 9999);
            } else {
                // login sukses -> set session dan redirect sesuai role
                $_SESSION['login_user'] = $old_username;
                $_SESSION['role'] = $row['id_p_role'];
                $_SESSION['id_t_account'] = $row['id_t_account'];
                $_SESSION['id_t_anggota'] = $row['id_t_anggota'] ?? null;

                if ($row['id_p_role'] == 3) {
                    header("Location: form/anggota/dashboard.php");
                    exit();
                } else {
                    header("Location: form/dashboard.php");
                    exit();
                }
            }
        }
    }
}

// Cek jika ada error dari redirect
if (isset($_GET['error']) && $_GET['error'] == 'inactive') {
    $error = "Akun Anda telah dinonaktifkan. Silahkan hubungi administrator.";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>

    <!-- Multiple favicon sizes -->
    <link rel="icon" type="image/png" sizes="32x32" href="public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="public/assets/pelindo-logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Rumah Baca</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg" style="width: 25rem; border-radius: 15px;">
            <div class="card-body">
                <h3 class="text-center mb-3">Silahkan Login</h3>
                <?php if ($error != ""): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <input type="text" class="form-control" placeholder="Username" name="username"
                            value="<?php echo htmlspecialchars($old_username); ?>" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" placeholder="Password" name="password" required>
                    </div>
                    <div class="mb-3 text-center">
                        <label class="form-label">CAPTCHA:</label>
                        <div class="p-2 mb-2"
                            style="display: inline-block; font-size: 24px; font-weight: bold; color: #fff; background-color: #007bff; border-radius: 5px;">
                            <?php echo $_SESSION['captcha_text']; ?>
                        </div>
                        <input type="number" class="form-control" name="captcha" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="text-center mt-3">
                    <p>Belum punya akun? <a href="register.php">Daftar</a></p>
                </div>
                <div class="text-center mt-2">
                    <a href="public/landing.php" class="btn btn-outline-secondary btn-sm">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>