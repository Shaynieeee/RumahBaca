<?php
session_start();
require_once '../setting/koneksi.php';

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

$where = " WHERE 1=1 ";

// Inisialisasi variabel pencarian
$txtNama = isset($_GET['txtNama']) ? $_GET['txtNama'] : '';
$txtAlamat = isset($_GET['txtAlamat']) ? $_GET['txtAlamat'] : '';
$txtStatus = isset($_GET['txtStatus']) ? $_GET['txtStatus'] : '';

// Filter pencarian
if($txtNama != '') {
	$where .= " AND nama LIKE '%".$txtNama."%' ";
}
if($txtAlamat != '') {
	$where .= " AND alamat LIKE '%".$txtAlamat."%' ";
}
if($txtStatus != '' && $txtStatus != 'Semua') {
	$where .= " AND status = '".$txtStatus."' ";
}

include("header.php");	
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"></h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
   <div class="row">
		<div class="col-lg-8 col-md-6">
		<form method="GET">
		<table>
		  <tr>
			<td>Nama Staff&nbsp;</td>
			<td><input type="text" class="form-control"  name="txtNama" value="<?php echo $txtNama; ?>"></td>
			<td>&nbsp;Status&nbsp;</td>
			<td>
				<select class="form-control" name="txtStatus">
						<option value="Semua">Semua</option>
						<option value="Aktif" <?php echo ($txtStatus == "Aktif") ? "selected" : ""; ?>>Aktif</option>
                        <option value="Tidak Aktif" <?php echo ($txtStatus == "Tidak Aktif") ? "selected" : ""; ?>>Tidak Aktif</option>
				</select>
			</td>
		  </tr>
		  <tr>
			<td>Alamat&nbsp;</td>
			<td><input type="text" class="form-control"  name="txtAlamat" value="<?php echo $txtAlamat; ?>"></td>
		  </tr>
		  <tr style="height:50px">
			<td></td>
			<td valign="middle"><button type="submit" class="btn btn-small btn-primary btn-block" name="btncari">Cari</button></td>
			<td></td>
			<td></td>
		  </tr>
		</table>
		</form>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-lg-12">
		<table width="100%" class="table table-striped table-bordered table-hover">
			<tr style="background: #0067b0; color:#fff;">
				<th>No</th>
				<th>Nama Staff</th>
				<th>Alamat</th>
				<th>Status</th>
				<th>Action</th>
			</tr>
		  <?php
			$hal = isset($_GET['hal']) ? $_GET['hal'] : 1;
			$page = ($hal > 1) ? $hal : 1;
			$max_results = 10;
			$from = (($page * $max_results) - $max_results);
			
			$sql = "SELECT * FROM t_staff $where LIMIT $from, $max_results";
			//echo $sql;
			$result = mysqli_query($db,$sql);
			$jum_data = mysqli_num_rows($result);
			
			$no = 1;
			while($tampil = mysqli_fetch_array($result,MYSQLI_ASSOC))
			{
				?>
					<tr>
						<td><?php echo $no;?></td>
						<td><?php echo $tampil['nama']; ?></td>
						<td><?php echo $tampil['alamat']; ?></td>
						<td><?php echo $tampil['status']; ?></td>
						<td><a href="input_staff.php?id=<?php echo $tampil['id_t_staff'];?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a></td>
					</tr>
					
				<?php
				$no++;
			}
				?>
		  </table>
		  <br>
		  <?php
				$total_sql = "SELECT COUNT(*) AS NUM FROM t_staff";
				$total_results = mysqli_query($db,$total_sql);
				$row = mysqli_fetch_array($total_results,MYSQLI_ASSOC);
				$total_pages = ceil($row['NUM'] / $max_results);
				//echo $jum;
				
				//jumlah data setelah filter
				if($jum_data == 0){
					echo "Data tidak ditemukan";
				}
				
				echo "<center> Halaman <br>";
				
				for($i=1; $i<=$total_pages; $i++){
						if ($hal == $i){
							echo "<strong>$i</strong> ";
							}else{
							echo "<a href='?hal=$i'>$i</a> ";
							}
						}
						
				echo "</center>";
				?>
		  </div>
	</div>
</div>
<?php 
// include "../template/footer.php";
?>