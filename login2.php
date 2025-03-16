<?php
   include("setting/koneksi.php");
   $error = "";
   session_start();
   
   if($_SERVER["REQUEST_METHOD"] == "POST") {
      // username and password sent from form 
      $myusername = mysqli_real_escape_string($db,$_POST['username']);
      $mypassword = $_POST['password']; // Password dari form
      
      // Query dimodifikasi untuk mengecek status dari t_staff dan t_anggota
      $sql = "SELECT a.*, r.nama_role,
              COALESCE(s.status, m.status) as user_status 
              FROM t_account a
              JOIN p_role r ON a.id_p_role = r.id_p_role
              LEFT JOIN t_staff s ON a.id_t_account = s.id_t_account 
              LEFT JOIN t_anggota m ON a.id_t_anggota = m.id_t_anggota
              WHERE a.username = ?";
              
      $stmt = mysqli_prepare($db, $sql);
      mysqli_stmt_bind_param($stmt, "s", $myusername);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      
      if(mysqli_num_rows($result) == 1) {
         $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
         
         // Cek status user
         if($row['user_status'] == 'Tidak Aktif') {
            $error = "Akun Anda telah dinonaktifkan. Silahkan hubungi administrator.";
         }
         // Jika status aktif, lanjut verifikasi password
         else if(password_verify($mypassword, $row['password'])) {
            $_SESSION['login_user'] = $myusername;
            $_SESSION['role'] = $row['id_p_role'];
            
            // Redirect berdasarkan role
            if ($row['id_p_role'] == 3) {
                header("location: form/anggota/dashboard.php");
            } else {
                header("location: form/dashboard.php");
            }
            exit();
         } else {
            $error = "Username atau Password salah";
         }
      } else {
         $error = "Username atau Password salah";
      }
   }
?>
<html>
   
   <head>
          <!-- Multiple favicon sizes -->
    <link rel="icon" type="image/png" sizes="32x32" href="public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="public/assets/pelindo-logo.png">
    
		<title>Login | Rumah Baca</title>
		<link href="template/css/bootstrap.min.css" rel="stylesheet" />
		<link href="template/css/sb-admin-2.css" rel="stylesheet" />
		<script src="template/js/bootstrap.min.js"></script>
		<link rel="stylesheet" type="text/css" href="template/alert.css">
   </head>
   <body>
   <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default" style="border-radius: 20px;">
					<div class="panel-heading" style="border-radius: 20px 20px 0 0;">
                        <h3 class="panel-title">Silahkan Login</h3>
                    </div>
                    <div class="panel-body">
						<form method = "post">
						<fieldset>
							<?php
							if ($error != ""){
							?>
							<div class="form-group">
									<div class="alert">
									<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
									<?php echo $error; ?>
									</div>
							</div>
							<?php
								}
							?>
			
						<br>
						<div class="form-group">
							<input type="text" class="form-control" placeholder="username, contoh : ARY" name="username" required autocomplete="off">
						</div>
						<div class="form-group">
							<input type="password" class="form-control" placeholder="Enter Password" name="password" required>
						</div>	
						<div class="form-group">
							<button type="submit" class="btn btn-md btn-primary btn-block">Login</button>
						</div>
						<div class="text-center">
							<p>Belum punya akun? <a href="register.php">Register</a></p>
						</div>
                  <div class="text-center">
                    <a href="public/landing.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                  </div>
						</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	 </div>
   </body>
</html>