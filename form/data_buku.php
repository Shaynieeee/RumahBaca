<?php
	require_once '../setting/session.php';
	include("header.php");	
	
	$usersession = $_SESSION['login_user'];
	
	$sql = "select id_p_role, id_t_account from t_account where username = '$usersession' ";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$idnya = $row['id_t_account'];
	
	$where = " WHERE 1=1 ";
	if(isset($_GET['txtNama']) && !empty($_GET['txtNama'])){
		$where .= " AND nama_buku LIKE '%".$_GET['txtNama']."%' ";
	}
	
	if(isset($_GET['txtTahun']) && !empty($_GET['txtTahun'])){
		$where .= " AND tahun_terbit = '".$_GET['txtTahun']."' ";
	}
					
	if(isset($_GET['txtPenulis'])){
		$where .= " AND penulis LIKE '%".$_GET['txtPenulis']."%' ";
	}
						
	if(isset($_GET['txtJenis']) && !empty($_GET['txtJenis'])){
		$where .= " AND jenis LIKE '%".$_GET['txtJenis']."%' ";
	} 
							
	if(isset($_GET['txtPenerbit'])){
		$where .= " AND penerbit LIKE '%".$_GET['txtPenerbit']."%' ";
	} 
	
?>
<div id="page-wrapper">
	<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Data Buku</h1>
                </div>
                <!-- /.col-lg-12 -->
	</div>
    <div class="row">
		<div class="col-lg-12">
		<form method="GET">
			<div class="form-group row">
				<label class="col-sm-2 col-form-label">Judul Buku</label>
				<div class="col-sm-4">
					<input type="text" class="form-control" name="txtNama" value="<?php echo $_GET['txtNama'] ?? ''; ?>">
				</div>
				<label class="col-sm-2 col-form-label">Tahun Terbit</label>
				<div class="col-sm-4">
					<input type="text" class="form-control" name="txtTahun" value="<?php echo $_GET['txtTahun'] ?? ''; ?>">
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 col-form-label">Kategori Buku</label>
				<div class="col-sm-4">
					<select name="txtJenis" class="form-control">
						<option value="">Semua</option>
						<?php
						$kategori = array(
							'Umum' => '',
							'Komputer' => '',
							'Novel' => '',
							'Pengembangan Diri' => '',
							'Komik' => ''
						);

						// Set selected untuk kategori yang dipilih
						if(isset($_GET['txtJenis'])) {
							$selected_kategori = $_GET['txtJenis'];
							if(array_key_exists($selected_kategori, $kategori)) {
								$kategori[$selected_kategori] = 'selected';
							}
						}

						foreach($kategori as $jenis => $selected) {
							echo "<option value='$jenis' $selected>$jenis</option>";
						}
						?>
					</select>
				</div>
				<div class="col-sm-6">
					<button type="submit" class="btn btn-primary btn-block">Cari</button>
				</div>
			</div>
		</form>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-lg-12">
		<table width="100%" class="table table-striped table-bordered table-hover">
			<tr>
				<th>No</th>
				<th>Cover</th>
				<th>Judul Buku</th>
				<th>Kategori Buku</th>
				<th>Penulis</th>
				<th>Tahun Terbit</th>
				<th>Penerbit</th>
				<th>Action</th>
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
			
			$sql = "SELECT * FROM t_buku $where LIMIT $from, $max_results";
			$result = mysqli_query($db,$sql);
			$jum_data = mysqli_num_rows($result);
			
			$no = 1;
			while($tampil = mysqli_fetch_array($result,MYSQLI_ASSOC))
			{
				$sinopsis = substr($tampil['sinopsis'],0,250);
				?>
					<tr>
						<td><?php echo $no;?></td>
						<td>
							<?php if(!empty($tampil['gambar'])): ?>
								<img src="../image/buku/<?php echo htmlspecialchars($tampil['gambar']); ?>?v=<?php echo time(); ?>" 
									 alt="Cover" 
									 class="img-thumbnail" 
									 style="max-height: 80px; max-width: 60px;"
									 onerror="this.onerror=null; this.src='../image/buku/default.jpg'; console.error('Error loading image: <?php echo htmlspecialchars($tampil['gambar']); ?>');">
							<?php else: ?>
								<span>No Cover</span>
							<?php endif; ?>
						</td>
						<td><?php echo $tampil['nama_buku']; ?></td>
						<td><?php echo $tampil['jenis']; ?></td>
						<td><?php echo $tampil['penulis']; ?></td>
						<td><?php echo $tampil['tahun_terbit']; ?></td>
						<td><?php echo $tampil['penerbit']; ?></td>
						<td>
							<div class="btn-group-action">
								<!-- <button type="button" class="btn btn-info btn-sm" onclick="showPreview(<?php echo $tampil['id_t_buku']; ?>)" title="Preview">
									<i class="fa fa-eye"></i>
								</button> -->
								<?php if($row['id_p_role'] != 3) { ?>
									<a href="input_buku.php?id=<?php echo $tampil['id_t_buku'];?>" class="btn btn-warning btn-sm" title="Edit">
										<i class="fa fa-edit"></i>
									</a>
									<button onclick="if(confirm('Apakah anda yakin ingin menghapus data ini?')){ location.href='hapus_buku.php?id=<?php echo $tampil['id_t_buku']; ?>' }" class="btn btn-danger btn-sm" title="Hapus">
										<i class="fa fa-trash"></i>
									</button>
								<?php } ?>
							</div>
						</td>
					</tr>
					
		  <?php
				$no++;
			}
		  ?>
		  </table>
		  <br>
		  <?php
				$total_sql = "SELECT COUNT(*) AS NUM FROM t_buku ";
				$total_results = mysqli_query($db,$total_sql);
				$row = mysqli_fetch_array($total_results,MYSQLI_ASSOC);
				$jum = $row['NUM'];
				$total_pages= ceil($jum / $max_results);
				
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

<!-- Tambahkan Modal untuk Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Preview Buku</h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-4">
						<img id="preview-cover" src="" class="img-fluid" alt="Cover Buku">
					</div>
					<div class="col-md-8">
						<h4 id="preview-judul"></h4>
						<p><strong>Penulis:</strong> <span id="preview-penulis"></span></p>
						<p><strong>Penerbit:</strong> <span id="preview-penerbit"></span></p>
						<p><strong>Tahun Terbit:</strong> <span id="preview-tahun"></span></p>
						<p><strong>Kategori:</strong> <span id="preview-kategori"></span></p>
						<hr>
						<h5>Sinopsis:</h5>
						<p id="preview-sinopsis"></p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<!-- Tambahkan Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function showPreview(id_buku) {
	console.log('Requesting preview for book ID:', id_buku);
	
	$.ajax({
		url: 'get_preview_buku.php',
		type: 'POST',
		data: { 
			id_buku: id_buku 
		},
		success: function(response) {
			try {
				console.log('Raw response:', response);
				var data = JSON.parse(response);
				
				if(data.error) {
					console.error('Error from server:', data.error);
					alert(data.error);
					return;
				}
				
				// Update modal content
				$('#preview-cover').attr('src', '../image/buku/' + data.gambar);
				$('#preview-judul').text(data.nama_buku);
				$('#preview-penulis').text(data.penulis);
				$('#preview-penerbit').text(data.penerbit);
				$('#preview-tahun').text(data.tahun_terbit);
				$('#preview-kategori').text(data.jenis);
				$('#preview-sinopsis').text(data.deskripsi); // Sesuaikan dengan nama kolom di database
				
				// Show modal
				$('#previewModal').modal('show');
			} catch(e) {
				console.error('Error parsing JSON:', e);
				console.error('Response text:', response);
				alert('Terjadi kesalahan saat memproses data buku');
			}
		},
		error: function(xhr, status, error) {
			console.error('AJAX Error:', error);
			console.error('Status:', status);
			console.error('Response:', xhr.responseText);
			alert('Terjadi kesalahan saat mengambil data buku');
		}
	});
}
</script>

<!-- Tambahkan Style -->
<style>
#preview-cover {
	max-height: 400px;
	object-fit: contain;
}

.modal-body {
	max-height: calc(100vh - 200px);
	overflow-y: auto;
}

#preview-sinopsis {
	text-align: justify;
	line-height: 1.6;
}

.btn-group-action {
	display: flex;
	gap: 5px;
	justify-content: center;
}

.btn-group-action .btn {
	padding: 6px 10px;
}

.table > tbody > tr > td {
	vertical-align: middle;
}
</style>
<?php 
// include "../template/footer.php";
?>