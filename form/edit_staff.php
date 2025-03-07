<?php
session_start();
require_once '../setting/koneksi.php';

// Cek login
if(!isset($_SESSION['login_user'])){
    header("location:../index.php");
    exit;
}

// Cek role staff
$usersession = $_SESSION['login_user'];
$sql = "SELECT id_p_role FROM t_account WHERE username = '$usersession'";
$result = mysqli_query($db, $sql);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

if($row['id_p_role'] != 2) {
    header("location:dashboard.php");
    exit;
}

include("header.php");

// Ambil ID staff
$id = $_GET['id'];

// Ambil data staff
$sql = "SELECT s.*, a.username 
        FROM t_staff s 
        JOIN t_account a ON s.id_t_account = a.id_t_account 
        WHERE s.id_t_account = '$id'";
$result = mysqli_query($db, $sql);
$data = mysqli_fetch_array($result, MYSQLI_ASSOC);

// Proses update
if(isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $alamat = mysqli_real_escape_string($db, $_POST['alamat']);
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $update_date = date('Y-m-d H:i:s');
    

    // Update t_staff
    $sql_staff = "UPDATE t_staff SET 
                  nama = '$nama',
                  alamat = '$alamat',
                  update_date = '$update_date'
                  WHERE id_t_account = '$id'";
                  
    // Update t_account
    $sql_account = "UPDATE t_account SET 
                    username = '$username',
                    update_date = '$update_date'
                    WHERE id_t_account = '$id'";
    
    if(mysqli_query($db, $sql_staff) && mysqli_query($db, $sql_account)) {
        $_SESSION['login_user'] = $username; // Update session jika username berubah
        echo "<script>
                alert('Data berhasil diupdate!');
                window.location.href='profile.php';
              </script>";
    } else {
        echo "<script>alert('Gagal mengupdate data!');</script>";
    }
}
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Edit Profile Staff</h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <form class="form-horizontal" method="post" action="">
                <div class="form-group">
                    <label class="control-label col-sm-4">Nama</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="nama" value="<?php echo $data['nama']; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-sm-4">Username</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="username" value="<?php echo $data['username']; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-sm-4">Alamat</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" name="alamat" rows="3"><?php echo $data['alamat']; ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-8">
                        <button type="submit" name="submit" class="btn btn-primary">Update Profile</button>
                        <a href="profile.php" class="btn btn-default">Kembali</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include("footer.php"); ?> 