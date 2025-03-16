<?php
   session_start();
   include("setting/koneksi.php");
   
   $error = "";

   // Generate CAPTCHA jika belum ada
   if (!isset($_SESSION['captcha'])) {
       $_SESSION['captcha'] = rand(10, 99) . " + " . rand(1, 9);
       $_SESSION['captcha_result'] = eval("return " . str_replace(" ", "", $_SESSION['captcha']) . ";");
   }
   
   if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $myusername = mysqli_real_escape_string($db, $_POST['username']);
      $mypassword = $_POST['password'];
      $captcha_input = $_POST['captcha'];
      
      // Periksa CAPTCHA
      if ($captcha_input != $_SESSION['captcha_result']) {
          $error = "CAPTCHA salah, coba lagi.";
      } else {
          $sql = "SELECT a.*, r.nama_role, COALESCE(s.status, m.status) as user_status 
                  FROM t_account a
                  JOIN p_role r ON a.id_p_role = r.id_p_role
                  LEFT JOIN t_staff s ON a.id_t_account = s.id_t_account 
                  LEFT JOIN t_anggota m ON a.id_t_anggota = m.id_t_anggota
                  WHERE a.username = ?";
          
          $stmt = mysqli_prepare($db, $sql);
          mysqli_stmt_bind_param($stmt, "s", $myusername);
          mysqli_stmt_execute($stmt);
          $result = mysqli_stmt_get_result($stmt);
          
          if (mysqli_num_rows($result) == 1) {
              $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
              if ($row['user_status'] == 'Tidak Aktif') {
                  $error = "Akun Anda telah dinonaktifkan. Silahkan hubungi administrator.";
              } elseif (password_verify($mypassword, $row['password'])) {
                  $_SESSION['login_user'] = $myusername;
                  $_SESSION['role'] = $row['id_p_role'];
                  
                  header("location: " . ($row['id_p_role'] == 3 ? "form/anggota/dashboard.php" : "form/dashboard.php"));
                  exit();
              } else {
                  $error = "Username atau Password salah.";
              }
          } else {
              $error = "Username atau Password salah.";
          }
      }
      
      // Reset CAPTCHA setelah pengiriman form
      $_SESSION['captcha'] = rand(10, 99) . " + " . rand(1, 9);
      $_SESSION['captcha_result'] = eval("return " . str_replace(" ", "", $_SESSION['captcha']) . ";");
   }
?>
<!DOCTYPE html>
<html lang="id">
<head>
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
                        <input type="text" class="form-control" placeholder="Username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" placeholder="Password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">CAPTCHA: <?php echo $_SESSION['captcha']; ?> = ?</label>
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
