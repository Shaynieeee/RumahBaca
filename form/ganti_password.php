<?php
require_once '../setting/koneksi.php';
include("header.php");	
$error = "";

if(isset($_POST['btnsubmit'])) {
	$temp_lama = $_POST['password_lama'];
	$temp_baru = $_POST['password_baru'];
	$temp_confirm = $_POST['password_conf'];
	
	$lama = $db->real_escape_string($temp_lama);
	$baru = $db->real_escape_string($temp_baru);
	$confirm = $db->real_escape_string($temp_confirm);
 
	$usersession = $_SESSION['login_user'];
	
	// Ambil password dari database
	$sql_check = "SELECT username, password FROM t_account WHERE username='$usersession'";
	$result_check = $db->query($sql_check);
	$user_data = $result_check->fetch_assoc();
	
	if($baru != $confirm){
		$error = "password baru tidak sama !";
	} else if(!password_verify($lama, $user_data['password'])) {
		$error = "password lama tidak sesuai !";
	} else {
		// Hash password baru dengan bcrypt
		$hashed_baru = password_hash($baru, PASSWORD_BCRYPT);
		
		$query_update = "UPDATE t_account SET 
						password = '$hashed_baru', 
						update_date = NOW(), 
						update_by = '$usersession' 
						WHERE username = '$usersession'";
						
		if ($db->query($query_update)) {
			// Destroy session
			session_destroy();
			
			echo "<script>
					alert('Password berhasil diubah! Silahkan login ulang.');
					window.location.href='../index.php?pesan=password_changed';
				  </script>";
			exit;
		} else {
			$error = "Gagal mengupdate password! " . $db->error;
		}
	}
}
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Ganti Password</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-6">
			<div class="panel-body">
						<form method="post">
							<?php
							if ($error != ""){
							?>
								<div class="alert alert-danger">
								<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
									<?php echo $error; ?>
								</div>
							<?php
							}
							?>
							<br>
							<div class="form-group row">
								<label class="control-label col-sm-4">Password Lama</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" name="password_lama" required  /><br>
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-sm-4">Password Baru</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="password_baru" placeholder="Password Baru" name="password_baru" required  />
								</div>
							</div>
							<div class="form-group row">
								<label class="control-label col-sm-4">Konfirm Password Baru</label>
								<div class="col-sm-8">
									<input type="password" class="form-control" id="password_conf" placeholder="Konfirm Password" name="password_conf" required  />
								</div>
							</div>
							<div class="form-group row" align="right">
								<div class="col-sm-4">
								<!-- sengaja dikosongin :D-->
								</div>
								<div class="col-sm-8">
									<a href="profile.php" class="btn btn-default">Batal</a>
									<button type="submit" name="btnsubmit" class="btn btn-primary">Ganti Password</button>
								</div>
							</div>
						</form>
					</div>
		</div>
	</div>
</div>
<?php 
include "footer.php";

// Debug di bagian bawah
if(isset($_POST['btnsubmit'])) {
	echo "<!-- 
	Debug Info:
	Username: $usersession
	Password Lama (hashed): " . password_hash($lama, PASSWORD_BCRYPT) . "
	Password Baru (hashed): $hashed_baru
	Password Confirm: $confirm
	Error: $error
	-->";
}
?>