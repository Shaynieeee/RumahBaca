<?php
require_once '../setting/session.php';
include("header.php");	

	$usersession = $_SESSION['login_user'];
	
	// Debug session
	echo "<!-- Username: " . $usersession . " -->";
	
	$sql = "SELECT id_p_role, id_t_account FROM t_account WHERE username = '$usersession'";
	$result = mysqli_query($db,$sql);
	if (!$result) {
		printf("Error in first query: %s\n", mysqli_error($db));
		exit();
	}
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$idnya = $row['id_t_account'];
	$roleid = $row['id_p_role'];
	
	// Debug role dan id
	echo "<!-- Role ID: " . $roleid . " -->";
	echo "<!-- ID: " . $idnya . " -->";
	
	if($roleid==1){
		//admin
		$sql_profile ="SELECT 'Admin' as nama, C.nama_role, A.create_date AS tgl_register, 
						IFNULL(A.update_date,'-') AS last_change_pass,
						'-' AS last_change_profile
						FROM t_account A 
						JOIN P_ROLE C ON A.id_p_role = C.id_p_role
						WHERE A.username = '$usersession'";
	}else if($roleid==2){
		// staff
		$sql_profile = "SELECT 
						s.nama, 
						p.nama_role, 
						a.create_date AS tgl_register,
						IFNULL(a.update_date,'-') AS last_change_pass,
						IFNULL(s.update_date,'-') AS last_change_profile
						FROM t_account a
						INNER JOIN t_staff s ON a.id_t_account = s.id_t_account
						INNER JOIN p_role p ON a.id_p_role = p.id_p_role
						WHERE a.username = '$usersession'";
		$url_edit = "edit_staff.php?id=" . $idnya;
	}else{
		// anggota
		$sql_profile = "SELECT B.no_anggota, B.nama, C.nama_role, A.create_date AS tgl_register, 
						IFNULL(A.update_date,'-') AS last_change_pass,
						IFNULL(B.update_date,'-') AS last_change_profile
						FROM t_account A 
						JOIN t_anggota B ON A.id_t_account = B.id_t_account 
						JOIN P_ROLE C ON A.id_p_role = C.id_p_role
						WHERE A.username = '$usersession'";
		$url_edit = "input_anggota.php?id=$idnya";
	}
	
	// Debug query
	echo "<!-- Query: " . $sql_profile . " -->";
	
	$result = mysqli_query($db,$sql_profile);
	if (!$result) {
		printf("Error in profile query: %s\n", mysqli_error($db));
		exit();
	}
	$tampil = mysqli_fetch_array($result,MYSQLI_ASSOC);
	if (!$tampil) {
		echo "<!-- No data found in query result -->";
		printf("Error: No data found for user %s\n", $usersession);
		exit();
	}
	
	// Debug hasil query
	echo "<!-- Data: ";
	print_r($tampil);
	echo " -->";

?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Profile</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<div class="row">
		<div class="col-lg-12">
			<div class="row">
				<label class="col-sm-2 col-form-label">Nama</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['nama']; ?></p>
				</div>
			</div>

			<?php if($roleid==3){ ?>
			<div class="row">
				<label class="col-sm-2 col-form-label">No Anggota</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['no_anggota']; ?></p>
				</div>
			</div>
			<?php } else { ?>
			<div class="row">
				<label class="col-sm-2 col-form-label">Role</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['nama_role']; ?></p>
				</div>
			</div>
			<?php } ?>
			
			<div class="row">
				<label class="col-sm-2 col-form-label">Tanggal Register</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['tgl_register']; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Last Changed Password</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['last_change_pass']; ?></p>
				</div>
			</div>

			<div class="row">
				<label class="col-sm-2 col-form-label">Last Changed Profile</label>
				<div class="col-sm-6">
					<p><?php echo $tampil['last_change_profile']; ?></p>
				</div>
			</div>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-8">
			<?php if($roleid==1){ ?>
				<a href="input_admin.php" class="btn btn-primary">Ubah Profile</a>
				<a href="ganti_password.php" class="btn btn-primary">Ubah Password</a>
			<?php } else if($roleid==2){ ?>
				<a href="<?php echo $url_edit;?>" class="btn btn-primary">Ubah Profile</a>
				<a href="ganti_password.php" class="btn btn-primary">Ubah Password</a>
			<?php } else { ?>
				<a href="<?php echo $url_edit;?>" class="btn btn-primary">Ubah Profile</a>
				<a href="ganti_password.php" class="btn btn-primary">Ubah Password</a>
			<?php } ?>
		</div>
	</div>
</div>
<!-- <?php
include("footer.php");
?> -->