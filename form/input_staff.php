<?php
session_start();
require_once '../setting/koneksi.php';
require_once '../setting/session.php';

// Cek login
if(!isset($_SESSION['login_user'])){
    header("location:../index.php");
    exit;
}

// Cek role untuk akses halaman staff
$usersession = $_SESSION['login_user'];
$sql = "SELECT id_p_role FROM t_account WHERE username = '$usersession'";
$result = mysqli_query($db, $sql);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

if($row['id_p_role'] != 1) {
    header("location:dashboard.php");
    exit;
}

$id_st = isset($_GET['id']) ? mysqli_real_escape_string($db,$_GET['id']) : null;

// Cek role user
$sql_cek = "SELECT id_p_role, id_t_account FROM t_account WHERE username = '$usersession'";
$result_cek = mysqli_query($db,$sql_cek);
$row_cek = mysqli_fetch_array($result_cek,MYSQLI_ASSOC);
$id_akun = $row_cek['id_t_account'];

// Redirect jika bukan admin atau staff
if($row_cek['id_p_role'] == 3){
    header('location:dashboard.php');
    exit();
}

if ($id_st != null){
    $judul = "Edit Staff";
    
    // Query sesuai role
    if($row_cek['id_p_role'] == 1){
        $sql_data = "SELECT * FROM t_staff WHERE id_t_staff = $id_st";
    } else if($row_cek['id_p_role'] == 2){
        $sql_data = "SELECT * FROM t_staff WHERE id_t_account = $id_akun";
    }
    
    $result_data = mysqli_query($db,$sql_data);
    $tampil_data = mysqli_fetch_array($result_data,MYSQLI_ASSOC);
} else {
    $judul = "";
    $tampil_data = array('nama' => '', 'alamat' => '', 'status' => '');
}

if(isset($_POST['btnsubmit'])) {
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $alamat = mysqli_real_escape_string($db, $_POST['alamat']);
    $statusnya = mysqli_real_escape_string($db, $_POST['statusnya']);
    
    if ($id_st != ''){
        // Update data
        if($row_cek['id_p_role'] == 1){
            $query = "UPDATE t_staff 
                     SET nama = '$nama', 
                         alamat = '$alamat',
                         status = '$statusnya',
                         update_date = CURDATE(),
                         update_by = '$usersession' 
                     WHERE id_t_staff = $id_st";
        } else if($row_cek['id_p_role'] == 2){
            $query = "UPDATE t_staff 
                     SET nama = '$nama', 
                         alamat = '$alamat',
                         status = '$statusnya',
                         update_date = CURDATE(),
                         update_by = '$usersession' 
                     WHERE id_t_account = $id_akun";
        }
    } else {
        // Insert data baru
        $nmuser = mysqli_real_escape_string($db, $_POST['user']);
        $passnya = $_POST['pass'];
        
        // Gunakan password_hash untuk enkripsi yang lebih aman
        $hashed_password = password_hash($passnya, PASSWORD_DEFAULT);
        
        // Begin transaction
        mysqli_begin_transaction($db);
        
        try {
            // Insert ke t_account
            $sql_akun = "INSERT INTO t_account(id_p_role, username, password, create_date, create_by)
                        VALUES(2, '$nmuser', '$hashed_password', CURDATE(), '$usersession')";
            mysqli_query($db, $sql_akun);
            
            $id_now = mysqli_insert_id($db);
            
            // Insert ke t_staff
            $query = "INSERT INTO t_staff(id_t_account, nama, alamat, status, create_date, create_by) 
                     VALUES($id_now, '$nama', '$alamat', '$statusnya', CURDATE(), '$usersession')";
            mysqli_query($db, $query);
            
            // Commit transaction
            mysqli_commit($db);
            
            header('location:data_staff.php');
            exit();
            
        } catch (Exception $e) {
            // Rollback jika terjadi error
            mysqli_rollback($db);
            $error = "Error: " . $e->getMessage();
        }
    }
    
    if (isset($query) && mysqli_query($db, $query)) {
        if($row_cek['id_p_role'] == 1){
            header('location:data_staff.php');
        } else if($row_cek['id_p_role'] == 2){
            header('location:dashboard.php');
        }
        exit();
    }
}

include("header.php");
?>

<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><?php echo $judul;?></h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
	
	<?php if(isset($error)): ?>
	<div class="alert alert-danger">
		<?php echo $error; ?>
	</div>
	<?php endif; ?>
	
	<div class="row">
		<div class="col-lg-6">
			<form class="form-horizontal" method="post">
				<div class="form-group">
					<label class="control-label col-sm-4">Nama Staff</label>
					<div class="col-sm-8">
					<input type="text" maxlength="25" class="form-control" name="nama" value="<?php echo $tampil_data['nama'];?>" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Tanggal Daftar</label>
					<div class="col-sm-8">
					<p class="form-control-static"><?php echo date("Y-m-d");?></p>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Alamat</label>
					<div class="col-sm-8">
					<textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($tampil_data['alamat']); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Status</label>
					<div class="col-sm-8">
						<select name="statusnya" class="form-control">
							<?php
							// Inisialisasi variabel
							$aktif = '';
							$tidak = '';
							
							// Set selected berdasarkan data yang ada
							if(isset($tampil_data['status'])) {
								if($tampil_data['status'] == "Aktif") {
									$aktif = "selected";
								} else if($tampil_data['status'] == "Tidak Aktif") {
									$tidak = "selected";
								}
							}
							?>
							<option value="Aktif" <?php echo $aktif; ?>>Aktif</option>
							<option value="Tidak Aktif" <?php echo $tidak; ?>>Tidak Aktif</option>
						</select>
					</div>
				</div>
				<?php
				if ($id_st == null){
				?>
				<div class="form-group">
					<label class="control-label col-sm-4">Username</label>
					<div class="col-sm-8">
					<input type="text" maxlength="25" class="form-control" name="user" required  />
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4">Password</label>
					<div class="col-sm-8">
					<input type="password" maxlength="10" class="form-control" name="pass" required  />
					</div>
				</div>
				<?php
				}
				?>
				<div class="form-group" align="right">
				<div class="col-sm-4">
				<!-- sengaja dikosongin :D-->
				</div>
				<div class="col-sm-8">
					<?php
					if($row_cek['id_p_role']==1){
					?>
					<a href="data_staff.php" class="btn btn-primary">Batal</a>
					<?php
					}else if($row_cek['id_p_role']==2){
					?>
					<a href="dashboard.php" class="btn btn-primary">Batal</a>
					<?php
					}
					?>
					<button type="submit" name="btnsubmit" class="btn btn-primary">Simpan</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php 
// include "../template/footer.php";
?>