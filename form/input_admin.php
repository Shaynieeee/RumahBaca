<?php
session_start();
require_once '../setting/koneksi.php';

// Cek login dan role admin
if(!isset($_SESSION['login_user'])){
    header("location:../index.php");
    exit;
}

$usersession = $_SESSION['login_user'];
$sql = "SELECT id_p_role FROM t_account WHERE username = '$usersession'";
$result = mysqli_query($db, $sql);
$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

if($row['id_p_role'] != 1) {
    header("location:dashboard.php");
    exit;
}

include("header.php");

// Ambil data admin
$sql = "SELECT * FROM t_account WHERE username = 'adm'";
$result = mysqli_query($db, $sql);
$data = mysqli_fetch_array($result, MYSQLI_ASSOC);

// Proses update
if(isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $update_date = date('Y-m-d H:i:s');
    
    $sql = "UPDATE t_account SET 
            username = '$username',
            email = '$email',
            update_date = '$update_date'
            WHERE username = 'adm'";
            
    if(mysqli_query($db, $sql)) {
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
            <h1 class="page-header">Edit Profile Admin</h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <form class="form-horizontal" method="post" action="">
                <div class="form-group">
                    <label class="control-label col-sm-4">Username</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="username" value="<?php echo $data['username']; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-sm-4">Email</label>
                    <div class="col-sm-8">
                        <input type="email" class="form-control" name="email" value="<?php echo $data['email']; ?>">
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