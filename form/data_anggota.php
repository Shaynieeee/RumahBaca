<?php
include("header.php");	

$aktif = "";
$tidak = "";
$where = " WHERE 1=1 ";

// Perbaikan filter pencarian
if(isset($_GET['txtNama']) && !empty($_GET['txtNama'])) {
	$nama = mysqli_real_escape_string($db, $_GET['txtNama']);
	$where .= " AND nama LIKE '%$nama%' ";
}

if(isset($_GET['txtTgl']) && !empty($_GET['txtTgl'])) {
	$tgl = mysqli_real_escape_string($db, $_GET['txtTgl']);
	$where .= " AND tgl_daftar = '$tgl' ";
}

if(isset($_GET['txtAlamat']) && !empty($_GET['txtAlamat'])) {
	$alamat = mysqli_real_escape_string($db, $_GET['txtAlamat']);
	$where .= " AND alamat LIKE '%$alamat%' ";
}

if(isset($_GET['txtStatus']) && $_GET['txtStatus'] != 'Semua') {
	$status = mysqli_real_escape_string($db, $_GET['txtStatus']);
	$where .= " AND status = '$status' ";
}

// Debug query
error_log("Query: SELECT * FROM t_anggota $where");

?>
<div id="page-wrapper">
	<div class="">
                <div class="col-lg-12">
                    <h1 class="page-header"></h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
   <div class="row">
		<div class="col-lg-12">
		<form method="GET">
			<div class="form-group row">
				<label class="col-sm-2 col-form-label">Nama Anggota</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="txtNama" value="<?php echo $_GET['txtNama'] ?? ''; ?>">
				</div>
				<label class="col-sm-2 col-form-label">Tanggal Daftar</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  id="txtTgl" name="txtTgl" value="<?php echo $_GET['txtTgl'] ?? ''; ?>">
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 col-form-label">Alamat</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="txtAlamat" value="<?php echo $_GET['txtAlamat'] ?? ''; ?>">
				</div>
				<label class="col-sm-2 col-form-label">Status</label>
				<div class="col-sm-4">
					<?php
					$selected_status = $_GET['txtStatus'] ?? '';
					?>
					<select class="form-control" name="txtStatus">
						<option value="Semua">Semua</option>
						<option value="Aktif" <?php echo ($selected_status == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
						<option value="Tidak Aktif" <?php echo ($selected_status == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
					</select>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-sm-6">
					<button type="submit" class="btn btn-small btn-primary btn-block">Cari</button>
				</div>
			</div>
		</form>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-lg-12">
		<table width="100%" class="table table-striped table-bordered table-hover">
			<tr style="background: #0067b0; color:#fff;">
				<th>No</th>
				<th>No Anggota</th>
				<th>Nama Anggota</th>
				<th>Tanggal Daftar</th>
				<th>Tanggal Lahir</th>
				<th>Alamat</th>
				<th>Status</th>
				<th>Keterangan</th>
				<th colspan="2">Action</th>
			</tr>
		  <?php
			$hal=1;
	
			if (!isset($_GET['hal'])) {
				$page=1;
			}else{
				$page= $_GET['hal'];
			}
			
			$max_results = 10;
			$from = (($page * $max_results) - $max_results);
			
			$sql = "SELECT * FROM t_anggota $where LIMIT $from, $max_results";
			//echo $sql;
			$result = mysqli_query($db,$sql);
			$jum_data = mysqli_num_rows($result);
			
			$no = 1;
			while($tampil = mysqli_fetch_array($result,MYSQLI_ASSOC))
			{
				?>
					<tr>
						<td><?php echo $no;?></td>
						<td><?php echo $tampil['no_anggota']; ?></td>
						<td><?php echo $tampil['nama']; ?></td>
						<td><?php echo $tampil['tgl_daftar']; ?></td>
						<td><?php echo $tampil['tgl_lahir']; ?></td>
						<td><?php echo $tampil['alamat']; ?></td>
						<td><?php echo $tampil['status']; ?></td>
						<td><?php echo $tampil['keterangan']; ?></td>
						<td><a href="input_anggota.php?id=<?php echo $tampil['id_t_anggota'];?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a></td>
						<td><a href="history_peminjaman.php?id=<?php echo $tampil['id_t_anggota'];?>" class="btn btn-warning btn-sm"><i class="fas fa-history"></i></a></td>
					</tr>
					
				<?php
				$no++;
			}
				?>
		  </table>
		  <br>
		  <?php
				$total_sql = "SELECT COUNT(*) AS NUM FROM t_anggota";
				$total_results = mysqli_query($db,$total_sql);
				$row = mysqli_fetch_array($total_results,MYSQLI_ASSOC);
				$jum = $row['NUM'];
				$total_pages= ceil($jum / $max_results);
				//echo $jum;
				
				//jumlah data setelah filter
				if($jum_data == 0){
					echo "Data tidak ditemukan";
				}
				
				echo "<center> Halaman <br>";
				
				if ($hal > 1){
					$prev= ($page - 1);
					}
					
				for($i=1; $i<=$total_pages; $i++){
						if (($hal)== $i){
							echo "<a href=$_SERVER[PHP_SELF]?hal=$i> $i</a>";
							}else{
							echo "<a href=$_SERVER[PHP_SELF]?hal=$i> $i</a>";
							}
						}
						
				if($hal < $total_pages){
					$next=($page + 1);
					}
				
				echo "</center>";
				?>
		  </div>
	</div>
</div>
<?php 
// include "../template/footer.php";
?>
<script type="text/javascript">
		$(document).ready(function(e) {
		$("#txtTgl").datepicker({dateFormat: "yy-mm-dd"});
	});
</script>