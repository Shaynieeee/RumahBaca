<?php
include("header.php");
require_once '../setting/koneksi.php';

$where = "WHERE 1=1";
$txtNo = "";
$txtTgl = "";
$txtStatus = "";

if (isset($_GET['txtNo']) && $_GET['txtNo'] != "") {
	$txtNo = mysqli_real_escape_string($db, $_GET['txtNo']);
	$where .= " AND p.no_peminjaman LIKE '%$txtNo%'";
}

if (isset($_GET['txtTgl']) && $_GET['txtTgl'] != "") {
	$txtTgl = mysqli_real_escape_string($db, $_GET['txtTgl']);
	$where .= " AND p.tgl_pinjam = '$txtTgl'";
}

if (isset($_GET['txtStatus']) && $_GET['txtStatus'] != "Semua") {
	$txtStatus = mysqli_real_escape_string($db, $_GET['txtStatus']);
	$where .= " AND p.status = '$txtStatus'";
}

// Update status otomatis jika lewat tanggal
$update_status = "UPDATE t_peminjaman 
				  SET status = 'Belum Kembali' 
				  WHERE tgl_kembali < CURDATE() 
				  AND status = 'Dipinjam'";
mysqli_query($db, $update_status);

// Fungsi denda telah dihapus
?>

<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h1 class="page-header"></h1>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-search"></i> Filter Pencarian
				</div>
				<div class="panel-body">
					<form method="GET">
						<div class="col-md-4">
							<div class="form-group">
								<label>No Peminjaman</label>
								<input type="text" class="form-control" name="txtNo" value="<?php echo $txtNo; ?>"
									placeholder="Masukkan No Peminjaman">
							</div>
							<div class="form-group">
								<label>Tanggal Peminjaman</label>
								<input type="date" class="form-control" name="txtTgl" value="<?php echo $txtTgl; ?>">
							</div>
							<div class="form-group">
								<label>Status</label>
								<select name="txtStatus" class="form-control">
									<option value="Semua">Semua Status</option>
									<option value="Dipinjam" <?php echo ($txtStatus == 'Dipinjam') ? 'selected' : ''; ?>>
										Dipinjam</option>
									<option value="Sudah Kembali" <?php echo ($txtStatus == 'Sudah Kembali') ? 'selected' : ''; ?>>Sudah Kembali</option>
									<option value="Belum Kembali" <?php echo ($txtStatus == 'Belum Kembali') ? 'selected' : ''; ?>>Belum Kembali</option>
								</select>
							</div>
							<div class="form-group">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-search"></i> Cari
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<hr>

	<div class="row">
		<div class="col-lg-12">
			<table class="table table-striped table-bordered table-hover">
				<thead style="background: #0067b0; color:#fff;">
					<tr>
						<th>No</th>
						<th>No Peminjaman</th>
						<th>Nama Anggota</th>
						<th>Staff</th>
						<th>Tanggal Pinjam</th>
						<th>Tanggal Kembali</th>
						<th>Jumlah Buku</th>
						<th>Status</th>
						<th>Kondisi</th>
						<th>Total Denda</th>
						<th colspan="2">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$page = isset($_GET['hal']) ? $_GET['hal'] : 1;
					$max_results = 10;
					$from = (($page * $max_results) - $max_results);

					// Query untuk mengambil data peminjaman
					$sql = "SELECT p.*, dp.id_t_buku, dp.qty, dp.denda, dp.kondisi,
						b.nama_buku, b.penulis, b.harga,
						a.nama as nama_anggota, a.no_anggota,
						DATEDIFF(CURDATE(), p.tgl_kembali) as hari_terlambat,
						s.nama as nama_staff
						FROM t_peminjaman p
						JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota 
						JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
						JOIN t_buku b ON dp.id_t_buku = b.id_t_buku
						LEFT JOIN t_staff s ON p.id_t_staff = s.id_t_staff
						$where
						ORDER BY p.tgl_pinjam DESC, p.id_t_peminjaman DESC
						LIMIT $from, $max_results";

					$result = mysqli_query($db, $sql);
					$jum_data = mysqli_num_rows($result);

					if ($jum_data > 0) {
						$no = $from + 1;
						while ($row = mysqli_fetch_assoc($result)) {
							?>
							<tr>
								<td><?php echo $no++; ?></td>
								<td><?php echo $row['no_peminjaman']; ?></td>
								<td><?php echo htmlspecialchars($row['nama_anggota']); ?></td>
								<td><?php
								if (!empty($row['nama_staff'])) {
									echo "Staff-" . htmlspecialchars($row['nama_staff']);
								} else {
									echo "Admin-" . htmlspecialchars($_SESSION['login_user']);
								}
								?></td>
								<td><?php echo date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
								<td><?php echo $row['tgl_kembali'] ? date('d/m/Y', strtotime($row['tgl_kembali'])) : '-'; ?>
								</td>
								<td><?php echo $row['qty']; ?></td>
								<td><?php
								$status = $row['status'];
								$icon = '';
								$text_color = '';

								if ($status == 'Belum Kembali') {
									$text_color = 'danger';
									$icon = '<i class="fa fa-exclamation-circle"></i> ';
									echo '<span style="color: red;">' . $icon . 'Belum Kembali</span>';
								} else if ($status == 'Dipinjam') {
									$text_color = 'warning';
									echo '<span style="color: #ffc107;">' . $icon . 'Dipinjam (' . $row['hari_terlambat'] . ' hari)</span>';
								} else if ($status == 'Sudah Kembali') {
									$text_color = 'success';
									$icon = '<i class="fa fa-check-circle"></i> ';
									echo '<span style="color: green;">' . $icon . 'Sudah Kembali</span>';
								}
								?></td>
								<td><?php
								if ($row['status'] == 'Sudah Kembali') {
									echo isset($row['kondisi']) ? $row['kondisi'] : "Baik";
								} else {
									echo '-';
								}
								?></td>
								<td>
									<?php
									echo "Rp " . number_format($row['total_denda'], 0, ',', '.');
									?>
								</td>
								<td>
									<a href="edit_detil_pinjam.php?id=<?php echo $row['id_t_peminjaman']; ?>"
										class="btn btn-info action-btn" data-toggle="tooltip" title="Edit Peminjaman">
										<i class="fa fa-edit fa-lg"></i>
									</a>
								</td>
								<td>
									<a href="detil_peminjaman.php?id=<?php echo $row['id_t_peminjaman']; ?>"
										class="btn btn-warning" data-toggle="tooltip" title="Lihat Detail">
										<i class="fa fa-eye fa-lg"></i>
									</a>
								</td>
							</tr>
							<?php
						}
					} else {
						echo "<tr><td colspan='11' class='text-center'>Data tidak ditemukan</td></tr>";
					}
					?>
				</tbody>
			</table>

			<?php if ($jum_data > 0): ?>
				<div class="text-center">
					<?php
					$total_sql = "SELECT COUNT(DISTINCT p.id_t_peminjaman) as total 
								FROM t_peminjaman p
								JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota 
								LEFT JOIN t_staff s ON p.id_t_staff = s.id_t_staff
								$where";
					$total_results = mysqli_query($db, $total_sql);
					$row = mysqli_fetch_assoc($total_results);
					$total_pages = ceil($row['total'] / $max_results);

					echo "<ul class='pagination'>";
					for ($i = 1; $i <= $total_pages; $i++) {
						$active = ($page == $i) ? 'active' : '';
						// Tambahkan parameter filter ke URL pagination
						$url = "?hal=$i";
						if ($txtNo != "")
							$url .= "&txtNo=$txtNo";
						if ($txtTgl != "")
							$url .= "&txtTgl=$txtTgl";
						if ($txtStatus != "")
							$url .= "&txtStatus=$txtStatus";

						echo "<li class='$active'><a href='$url'>$i</a></li>";
					}
					echo "</ul>";
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<style>
	/* Style untuk tombol aksi */
	.btn-info,
	.btn-warning {
		padding: 6px 12px;
		margin: 2px;
		font-size: 14px;
	}

	/* Style untuk icon */
	.fa-lg {
		font-size: 18px;
	}

	/* Hover effect */
	.btn-info:hover,
	.btn-warning:hover {
		opacity: 0.9;
		transform: scale(1.05);
	}

	/* Tooltip style */
	.tooltip-inner {
		font-size: 12px;
		padding: 5px 10px;
	}

	.panel-body {
		padding: 25px;
	}

	.form-group label {
		font-weight: 600;
		color: #333;
	}

	.form-control {
		height: 38px;
	}

	.btn-primary {
		margin-right: 15px;
	}
</style>

<script>
	$(document).ready(function () {
		$('[data-toggle="tooltip"]').tooltip();
	});
</script>

<?php
// include "../template/footer.php"
; ?>