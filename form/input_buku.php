<?php
session_start();
require_once '../app/classes/pdfHandler.php';
require_once '../setting/koneksi.php';

// Debug session
error_log("Session contents: " . print_r($_SESSION, true));

// Cek login
if(!isset($_SESSION['login_user'])){
	header("location:../index.php");
	exit;
}

$usersession = $_SESSION['login_user'];

// Debug username
error_log("Username: " . $usersession);

// Cek role user (admin atau staff)
$sql_role = "SELECT id_p_role, id_t_account FROM t_account WHERE username = '$usersession'";
$result_role = mysqli_query($db, $sql_role);
$row_role = mysqli_fetch_assoc($result_role);

// Debug role query
error_log("Role query result: " . print_r($row_role, true));

if (!$row_role) {
	die("Error: User tidak ditemukan");
}

$role_id = $row_role['id_p_role'];
$id_account = $row_role['id_t_account'];

// Cek apakah ini edit atau input baru
$id_buku = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$buku = null;
$upload_path = "../image/buku/"; // Definisikan path upload

// Pastikan folder upload ada
if (!file_exists($upload_path)) {
    mkdir($upload_path, 0777, true);
}

if($id_buku > 0) {
	$sql = "SELECT * FROM t_buku WHERE id_t_buku = ?";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt, "i", $id_buku);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$buku = mysqli_fetch_assoc($result);
}

// Ambil data kategori dari tabel t_kategori_buku
$sql_kategori = "SELECT * FROM t_kategori_buku ORDER BY nama_kategori";
$result_kategori = mysqli_query($db, $sql_kategori);

// Cek apakah tabel t_kategori_buku ada
if (!$result_kategori) {
    // Jika tabel belum ada, buat tabel
    $sql_create_table = "CREATE TABLE IF NOT EXISTS t_kategori_buku (
        id_t_kategori INT AUTO_INCREMENT PRIMARY KEY,
        nama_kategori VARCHAR(100) NOT NULL,
        create_by VARCHAR(50) NOT NULL,
        create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($db, $sql_create_table);
    
    // Tambahkan kategori default
    $default_categories = ['Umum', 'Novel', 'Komputer', 'Pengembangan Diri', 'Komik'];
    foreach($default_categories as $category) {
        $sql_insert = "INSERT INTO t_kategori_buku (nama_kategori, create_by) VALUES (?, ?)";
        $stmt = mysqli_prepare($db, $sql_insert);
        mysqli_stmt_bind_param($stmt, "ss", $category, $usersession);
        mysqli_stmt_execute($stmt);
    }
    
    // Ambil kategori lagi setelah membuat tabel
    $result_kategori = mysqli_query($db, "SELECT * FROM t_kategori_buku ORDER BY nama_kategori");
}

// Cari file gambar yang sesuai jika ini adalah halaman edit
if(!empty($id_buku) && isset($buku['gambar']) && !empty($buku['gambar'])) {
    $gambar_id = $buku['gambar'];
    $gambar_path = "../image/buku/default.jpg"; // Default image
    
    // Coba cari file dengan pola nama yang sesuai
    $files = glob("../image/buku/{$gambar_id}*");
    if(!empty($files)) {
        $gambar_path = $files[0]; // Ambil file pertama yang ditemukan
    } else {
        // Jika tidak ditemukan, coba cari dengan pola lain
        $files2 = glob("../image/buku/*{$gambar_id}*");
        if(!empty($files2)) {
            $gambar_path = $files2[0];
        }
    }
}

// Proses form submit
if(isset($_POST['submit'])) {
	try {
		mysqli_begin_transaction($db);
		
		// Ambil dan bersihkan data dari form
		$nama_buku = mysqli_real_escape_string($db, $_POST['nama_buku']);
		$isbn = mysqli_real_escape_string($db, $_POST['isbn']);
		$jenis = mysqli_real_escape_string($db, $_POST['jenis']);
		$penulis = mysqli_real_escape_string($db, $_POST['penulis']);
		$penerbit = mysqli_real_escape_string($db, $_POST['penerbit']);
		$bahasa = mysqli_real_escape_string($db, $_POST['bahasa']);
		$tahun_terbit = mysqli_real_escape_string($db, $_POST['tahun_terbit']);
		$harga = isset($_POST['harga']) ? (int)$_POST['harga'] : 0;  // Default 0 jika tidak diisi
		$kode_rak = isset($_POST['kode_rak']) ? mysqli_real_escape_string($db, $_POST['kode_rak']) : '';
		$stok = (int)$_POST['stok'];
		$sinopsis = mysqli_real_escape_string($db, $_POST['sinopsis']);
		$total_halaman = (int)$_POST['total_halaman'];
		$batas_baca_guest = isset($_POST['batas_baca_guest']) ? (int)$_POST['batas_baca_guest'] : 5;

		if($id_buku > 0) {
			// Mode Edit
			try {
				mysqli_begin_transaction($db);
				
				// Siapkan variabel
				$nama_buku = $_POST['nama_buku'];
				$isbn = $_POST['isbn'];
				$jenis = $_POST['jenis'];
				$penulis = $_POST['penulis'];
				$penerbit = $_POST['penerbit'];
				$bahasa = $_POST['bahasa'];
				$tahun_terbit = $_POST['tahun_terbit'];
				$harga = (int)$_POST['harga'];
				$kode_rak = $_POST['kode_rak'];
				$stok = (int)$_POST['stok'];
				$sinopsis = $_POST['sinopsis'];
				$total_halaman = (int)$_POST['total_halaman'];
				$batas_baca_guest = (int)$_POST['batas_baca_guest'];

				// Handle upload gambar baru jika ada
				if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
					$allowed = ['jpg', 'jpeg', 'png'];
					$filename = $_FILES['gambar']['name'];
					$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
					
					if(!in_array($ext, $allowed)) {
						throw new Exception('Format file gambar tidak valid');
					}
					
					// Buat nama file yang konsisten
					$new_filename = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "-", $filename);
					$upload_destination = $upload_path . $new_filename;
					
					// Debug log
					error_log("Original filename: " . $filename);
					error_log("New filename: " . $new_filename);
					error_log("Upload destination: " . $upload_destination);
					
					if(move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_destination)) {
						// Hapus file lama jika ada
						if(!empty($buku['gambar'])) {
							$old_file = $upload_path . $buku['gambar'];
							if(file_exists($old_file)) {
								unlink($old_file);
								error_log("Deleted old file: " . $old_file);
							}
						}
						// Set nama file baru untuk database
						$gambar = $new_filename;
						error_log("New image name saved to database: " . $gambar);
					} else {
						throw new Exception('Gagal mengupload file gambar');
					}
				} else {
					// Jika tidak ada upload gambar baru, gunakan gambar lama
					$gambar = $buku['gambar'];
				}

				// Handle upload PDF baru jika ada
				if(isset($_FILES['file_buku']) && $_FILES['file_buku']['error'] == 0) {
					if($_FILES['file_buku']['type'] != 'application/pdf') {
						throw new Exception('File harus berformat PDF');
					}
					
					$file_buku = time() . '_' . $_FILES['file_buku']['name'];
					if(move_uploaded_file($_FILES['file_buku']['tmp_name'], $upload_path . $file_buku)) {
						// Hapus file lama jika ada
						if($buku['file_buku'] && file_exists($upload_path . $buku['file_buku'])) {
							unlink($upload_path . $buku['file_buku']);
						}
					}
				} else {
					// Jika tidak ada upload file baru, gunakan file lama
					$file_buku = $buku['file_buku'];
				}

				// Query Update
				$sql = "UPDATE t_buku SET 
						nama_buku = ?, isbn = ?, jenis = ?, penulis = ?, 
						penerbit = ?, bahasa = ?, tahun_terbit = ?, 
						harga = ?, kode_rak = ?, stok = ?, sinopsis = ?,
						total_halaman = ?, batas_baca_guest = ?,
						gambar = ?, file_buku = ?, update_by = ?,
						update_date = CURRENT_TIMESTAMP
						WHERE id_t_buku = ?";

				$stmt = mysqli_prepare($db, $sql);
				if(!$stmt) {
					throw new Exception('Prepare failed: ' . mysqli_error($db));
				}

				// Bind parameters untuk update
				if (!$stmt->bind_param("sssssssisissiissi", 
					$nama_buku, $isbn, $jenis, $penulis, 
					$penerbit, $bahasa, $tahun_terbit, 
					$harga, $kode_rak, $stok, $sinopsis,
					$total_halaman, $batas_baca_guest,
					$gambar, $file_buku, $usersession,
					$id_buku
				)) {
					throw new Exception('Bind failed: ' . $stmt->error);
				}

				if(!$stmt->execute()) {
					throw new Exception('Execute failed: ' . $stmt->error);
				}

				mysqli_commit($db);
				
				// Redirect setelah berhasil update
				header("Location: data_buku.php?message=updated");
				exit();

			} catch(Exception $e) {
				mysqli_rollback($db);
				error_log("Error updating book: " . $e->getMessage());
				echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
			}
		} else {
			// Mode Input Baru
			try {
				mysqli_begin_transaction($db);
				
				// Ambil dan bersihkan data dari form
				$nama_buku = mysqli_real_escape_string($db, $_POST['nama_buku']);
				$isbn = mysqli_real_escape_string($db, $_POST['isbn']);
				$jenis = mysqli_real_escape_string($db, $_POST['jenis']);
				$penulis = mysqli_real_escape_string($db, $_POST['penulis']);
				$penerbit = mysqli_real_escape_string($db, $_POST['penerbit']);
				$bahasa = mysqli_real_escape_string($db, $_POST['bahasa']);
				$tahun_terbit = mysqli_real_escape_string($db, $_POST['tahun_terbit']);
				$harga = isset($_POST['harga']) ? (int)$_POST['harga'] : 0;
				$kode_rak = mysqli_real_escape_string($db, $_POST['kode_rak']);
				$stok = (int)$_POST['stok'];
				$sinopsis = mysqli_real_escape_string($db, $_POST['sinopsis']);
				$preview_content = ''; // Field kosong
				$total_halaman = (int)$_POST['total_halaman'];
				$batas_baca_guest = isset($_POST['batas_baca_guest']) ? (int)$_POST['batas_baca_guest'] : 5;
				
				// Handle gambar
				$gambar = '';
				if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
					$allowed = ['jpg', 'jpeg', 'png'];
					$filename = $_FILES['gambar']['name'];
					$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
					
					if(!in_array($ext, $allowed)) {
						throw new Exception('Format file gambar tidak valid');
					}
					
					// Buat nama file yang konsisten
					$new_filename = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "-", $filename);
					$upload_destination = $upload_path . $new_filename;
					
					// Debug log
					error_log("Original filename: " . $filename);
					error_log("New filename: " . $new_filename);
					error_log("Upload destination: " . $upload_destination);
					
					if(move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_destination)) {
						// Hapus file lama jika ada
						if(!empty($buku['gambar'])) {
							$old_file = $upload_path . $buku['gambar'];
							if(file_exists($old_file)) {
								unlink($old_file);
								error_log("Deleted old file: " . $old_file);
							}
						}
						// Set nama file baru untuk database
						$gambar = $new_filename;
						error_log("New image name saved to database: " . $gambar);
					} else {
						throw new Exception('Gagal mengupload file gambar');
					}
				} else {
					// Jika tidak ada upload gambar baru, gunakan gambar lama
					$gambar = isset($buku['gambar']) ? $buku['gambar'] : '';
					error_log("Keeping existing image: " . $gambar);
				}
				
				// Handle PDF
				$file_buku = '';
				if(isset($_FILES['file_buku']) && $_FILES['file_buku']['error'] == 0) {
					if($_FILES['file_buku']['type'] != 'application/pdf') {
						throw new Exception('File harus berformat PDF');
					}
					
					$file_buku = time() . '_' . $_FILES['file_buku']['name'];
					if(move_uploaded_file($_FILES['file_buku']['tmp_name'], $upload_path . $file_buku)) {
						// Hapus file lama jika ada
						if($buku['file_buku'] && file_exists($upload_path . $buku['file_buku'])) {
							unlink($upload_path . $buku['file_buku']);
						}
					}
				} else {
					// Jika tidak ada upload file baru, gunakan file lama
					$file_buku = $buku['file_buku'];
				}

				// Query Insert
				$sql = "INSERT INTO t_buku (
					nama_buku, isbn, jenis, penulis, penerbit, 
					bahasa, tahun_terbit, harga, kode_rak, stok, 
					sinopsis, preview_content, gambar, file_buku, 
					total_halaman, batas_baca_guest, create_by
				) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				
				$stmt = mysqli_prepare($db, $sql);
				if(!$stmt) {
					throw new Exception('Prepare failed: ' . mysqli_error($db));
				}
				
				// Set variabel untuk binding
				$preview_content = ''; // Tambahkan ini karena dibutuhkan dalam query
				
				// Debug
				error_log("Preparing to bind parameters:");
				error_log("Types string length: " . strlen("sssssssisissssiis"));
				error_log("Number of parameters: 17");
				
				// Bind parameters langsung tanpa array
				$bind_result = mysqli_stmt_bind_param($stmt, "sssssssisissssiis",
					$nama_buku,
					$isbn,
					$jenis,
					$penulis,
					$penerbit,
					$bahasa,
					$tahun_terbit,
					$harga,
					$kode_rak,
					$stok,
					$sinopsis,
					$preview_content,
					$gambar,
					$file_buku,
					$total_halaman,
					$batas_baca_guest,
					$usersession
				);

				if (!$bind_result) {
					throw new Exception('Bind failed: ' . mysqli_stmt_error($stmt));
				}

				if(!mysqli_stmt_execute($stmt)) {
					throw new Exception('Execute failed: ' . mysqli_stmt_error($stmt));
				}

				mysqli_commit($db);
				header("Location: data_buku.php?message=success");
				exit();
				
			} catch(Exception $e) {
				mysqli_rollback($db);
				// Hapus file yang sudah diupload jika ada error
				if(!empty($gambar) && file_exists($upload_path . $gambar)) {
					unlink($upload_path . $gambar);
				}
				if(!empty($file_buku) && file_exists($upload_path . $file_buku)) {
					unlink($upload_path . $file_buku);
				}
				echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
			}
		}

		mysqli_commit($db);
		
		echo "<script>
				alert('Data buku berhasil disimpan!');
				window.location.href = 'data_buku.php';
			  </script>";
		exit();
		
	} catch(Exception $e) {
		mysqli_rollback($db);
		error_log("Error saving book: " . $e->getMessage());
		echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
		
		// Hapus file jika ada error
		if(!empty($gambar) && file_exists($upload_path . $gambar)) {
			unlink($upload_path . $gambar);
		}
		if(!empty($file_buku) && file_exists($upload_path . $file_buku)) {
			unlink($upload_path . $file_buku);
		}
	}
}

include("header.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Input Buku</title>
    <!-- Bootstrap CSS -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- MetisMenu CSS -->
    <link href="../assets/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="../assets/vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../assets/dist/css/sb-admin-2.css" rel="stylesheet">
    
    <!-- Multiple favicon sizes -->
    <link rel="icon" type="image/png" sizes="32x32" href="../public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="../public/assets/pelindo-logo.png">

    <!-- Font Awesome -->
    <link href="../assets/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h1 class="page-header"><?php echo $id_buku ? 'Edit Buku' : ''; ?></h1>
		</div>
	</div>
	
	<!-- Debug info (temporary) -->
	<div class="row" style="margin-bottom: 20px;">
		<div class="col-lg-12">
			<div class="alert alert-info">
				Username: <?php echo $usersession; ?><br>
				Role ID: <?php echo $role_id; ?><br>
				Account ID: <?php echo $id_account; ?>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-lg-12">
			<div class="card">
				<div class="card-body">
					<form method="post" action="" enctype="multipart/form-data">
						<div class="row">
							<div class="col-md-8">
								<div class="form-group">
									<label>Judul Buku</label>
									<input type="text" name="nama_buku" class="form-control" required
										   value="<?php echo $buku ? htmlspecialchars($buku['nama_buku']) : ''; ?>">
								</div>
								
								<div class="form-group">
									<label>Penulis</label>
									<input type="text" name="penulis" class="form-control" required
										   value="<?php echo $buku ? htmlspecialchars($buku['penulis']) : ''; ?>">
								</div>
								
								<div class="form-group">
									<label>Penerbit</label>
									<input type="text" name="penerbit" class="form-control" required
										   value="<?php echo $buku ? htmlspecialchars($buku['penerbit']) : ''; ?>">
								</div>
								
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label>Tahun Terbit</label>
											<input type="number" name="tahun_terbit" class="form-control" required
												   value="<?php echo $buku ? htmlspecialchars($buku['tahun_terbit']) : ''; ?>">
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label>Kategori</label>
											<select name="jenis" class="form-control" required>
												<option value="">Pilih Kategori</option>
												<?php
												// Reset result pointer
												mysqli_data_seek($result_kategori, 0);
												
												// Loop through categories
												while($row_kategori = mysqli_fetch_assoc($result_kategori)) {
													$selected = ($buku && $buku['jenis'] == $row_kategori['nama_kategori']) ? 'selected' : '';
													echo "<option value='" . htmlspecialchars($row_kategori['nama_kategori']) . "' $selected>" . 
														 htmlspecialchars($row_kategori['nama_kategori']) . "</option>";
												}
												?>
											</select>
										</div>
									</div>
								</div>
								
								<div class="form-group">
									<label>Ketersediaan Buku</label>
									<select name="ketersediaan" id="ketersediaan" class="form-control" required>
										<option value="">Pilih Ketersediaan</option>
										<option value="online">Online</option>
										<option value="offline">Offline</option>
										<option value="both">Online & Offline</option>
									</select>
								</div>
								
								<div class="form-group offline-field">
									<label>Stok</label>
									<input type="number" class="form-control" name="stok" id="stok" 
										   value="<?php echo isset($buku['stok']) ? $buku['stok'] : ''; ?>">
								</div>
								
								<div class="form-group offline-field">
									<label>Harga</label>
									<input type="number" class="form-control" name="harga" id="harga" 
										   value="<?php echo isset($buku['harga']) ? $buku['harga'] : ''; ?>">
								</div>
								
								<div class="form-group offline-field">
									<label>Kode Rak</label>
									<input type="text" class="form-control" name="kode_rak" id="kode_rak" 
										   value="<?php echo isset($buku['kode_rak']) ? $buku['kode_rak'] : ''; ?>">
								</div>
								
								<div class="form-group">
									<label>Sinopsis</label>
									<textarea name="sinopsis" class="form-control" rows="5" required><?php echo $buku ? htmlspecialchars($buku['sinopsis']) : ''; ?></textarea>
								</div>
							</div>
							
							<div class="col-md-4">
								<div class="form-group">
									<label>Cover Buku</label>
									<?php if(!empty($id_buku) && isset($buku['gambar']) && !empty($buku['gambar'])): ?>
										<div class="mt-2">
											<p>Cover saat ini:</p>
											<img src="<?php echo $gambar_path; ?>" alt="Cover Buku" class="img-thumbnail" style="max-height: 200px;">
										</div>
									<?php endif; ?>
									<div class="input-group">
										<input type="file" name="gambar" class="form-control" accept="image/*">
										<input type="hidden" name="id_t_buku" value="<?php echo $buku['id_t_buku']; ?>">
									</div>
									<small class="form-text text-muted">
										Format: JPG, JPEG, PNG. Maksimal 2MB
									</small>
								</div>
								
								<div class="form-group online-field">
									<label>File PDF</label>
									<input type="file" class="form-control" name="file_buku" id="file_buku" accept=".pdf">
									<?php if(isset($buku['file_buku']) && !empty($buku['file_buku'])): ?>
										<p class="help-block">File saat ini: <?php echo $buku['file_buku']; ?></p>
									<?php endif; ?>
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<label>ISBN</label>
							<?php if($id_buku > 0): ?>
								<!-- Mode Edit: Bisa diedit -->
								<input type="text" name="isbn" class="form-control" 
									   value="<?php echo $buku ? htmlspecialchars($buku['isbn']) : ''; ?>">
							<?php else: ?>
								<!-- Mode Input Baru: Wajib diisi -->
								<input type="text" name="isbn" class="form-control" required 
									   value="<?php echo $buku ? htmlspecialchars($buku['isbn']) : ''; ?>">
							<?php endif; ?>
						</div>

						<div class="form-group">
							<label>Bahasa</label>
							<?php if($id_buku > 0): ?>
								<!-- Mode Edit: Bisa diedit -->
								<input type="text" name="bahasa" class="form-control" 
									   value="<?php echo $buku ? htmlspecialchars($buku['bahasa']) : 'Indonesia'; ?>">
							<?php else: ?>
								<!-- Mode Input Baru: Wajib diisi -->
								<input type="text" name="bahasa" class="form-control" required 
									   value="<?php echo $buku ? htmlspecialchars($buku['bahasa']) : 'Indonesia'; ?>">
							<?php endif; ?>
						</div>

						<div class="form-group">
							<label>Total Halaman</label>
							<?php if($id_buku > 0): ?>
								<!-- Mode Edit: Bisa diedit -->
								<input type="number" name="total_halaman" class="form-control" 
									   value="<?php echo $buku ? htmlspecialchars($buku['total_halaman']) : ''; ?>">
							<?php else: ?>
								<!-- Mode Input Baru: Wajib diisi -->
								<input type="number" name="total_halaman" class="form-control" required 
									   value="<?php echo $buku ? htmlspecialchars($buku['total_halaman']) : ''; ?>">
							<?php endif; ?>
						</div>
						
						<div class="form-group">
							<label>Batas Halaman untuk Guest</label>
							<div class="input-group">
								<input type="number" name="batas_baca_guest" class="form-control" required
									   min="1" 
									   value="<?php echo $buku ? htmlspecialchars($buku['batas_baca_guest']) : '5'; ?>">
								<div class="input-group-append">
									<span class="input-group-text">halaman</span>
								</div>
							</div>
							<small class="form-text text-muted">
								Tentukan berapa halaman yang dapat dibaca oleh guest sebelum harus login.
								Minimum 1 halaman.
							</small>
						</div>
						
						<div class="form-group mt-4">
							<a href="data_buku.php" class="btn btn-secondary">
								<i class="fa fa-arrow-left"></i> Kembali
							</a>
							<button type="submit" name="submit" class="btn btn-primary">
								<i class="fa fa-save"></i> <?php echo $id_buku ? 'Update' : 'Simpan'; ?>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Tambahkan alert error jika ada -->
<?php if(isset($error)): ?>
<div class="alert alert-danger">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<!-- Tambahkan ini di bagian form untuk debugging -->
<div class="alert alert-info" style="display: none;" id="debugInfo">
    <pre><?php print_r($_POST); ?></pre>
    <pre><?php print_r($_FILES); ?></pre>
</div>

<!-- Tambahkan script ini di bagian JavaScript -->
<script>
$(document).ready(function() {
    function toggleFields() {
        var ketersediaan = $('#ketersediaan').val();
        
        // Sembunyikan semua field terlebih dahulu
        $('.offline-field, .online-field').hide();
        
        // Tampilkan field sesuai pilihan
        if (ketersediaan === 'online') {
            $('.online-field').show();
            // Reset nilai field offline
            $('.offline-field input').val('');
        } 
        else if (ketersediaan === 'offline') {
            $('.offline-field').show();
            // Reset nilai field online
            $('.online-field input').val('');
        }
        else if (ketersediaan === 'both') {
            $('.offline-field, .online-field').show();
        }
    }

    // Jalankan saat halaman dimuat dan saat select berubah
    $('#ketersediaan').change(toggleFields);
    toggleFields();
});
</script>

<style>
.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    border: none;
    border-radius: 8px;
    margin-bottom: 30px;
}

.card-body {
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.img-thumbnail {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 15px;
}

.input-group {
    margin-bottom: 5px;
}

input[type="file"] {
    padding: 3px;
    height: auto;
}

.input-group .form-control {
    border-radius: 4px;
}

.alert {
    padding: 8px 12px;
    margin-bottom: 10px;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}
</style>

<!-- jQuery -->
<script src="../assets/vendor/jquery/jquery.min.js"></script>

<!-- Bootstrap JavaScript -->
<script src="../assets/vendor/bootstrap/js/bootstrap.min.js"></script>

<!-- Metis Menu Plugin -->
<script src="../assets/vendor/metisMenu/metisMenu.min.js"></script>

<!-- DataTables JavaScript -->
<script src="../assets/vendor/datatables/js/jquery.dataTables.min.js"></script>
<script src="../assets/vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>

<!-- Custom Theme JavaScript -->
<script src="../assets/dist/js/sb-admin-2.js"></script>
</body>
</html>