<?php
require_once '../setting/koneksi.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Validasi input
if (!isset($_POST['id_peminjaman']) || !isset($_POST['id_anggota'])) {
    die(json_encode(['status' => 'error', 'message' => 'Parameter tidak lengkap']));
}

$id_peminjaman = mysqli_real_escape_string($db, $_POST['id_peminjaman']);
$id_anggota = mysqli_real_escape_string($db, $_POST['id_anggota']);

// Ambil data peminjaman dan anggota
$sql = "SELECT p.*, a.nama as nama_anggota, a.email, 
               GROUP_CONCAT(b.nama_buku SEPARATOR ', ') as daftar_buku,
               DATEDIFF(CURDATE(), p.tgl_kembali) as hari_terlambat
        FROM t_peminjaman p 
        JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota 
        JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
        JOIN t_buku b ON dp.id_t_buku = b.id_t_buku
        WHERE p.id_t_peminjaman = '$id_peminjaman'
        GROUP BY p.id_t_peminjaman";

$result = mysqli_query($db, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die(json_encode(['status' => 'error', 'message' => 'Data peminjaman tidak ditemukan']));
}

// Hitung denda
$denda_per_hari = 2000; // Default denda per hari
$total_denda = $data['hari_terlambat'] * $denda_per_hari;

// Nonaktifkan akun anggota
$sql_update = "UPDATE t_anggota SET 
               status = 'Tidak Aktif',
               keterangan = 'Akun dinonaktifkan karena keterlambatan pengembalian buku',
               update_date = CURDATE(),
               update_by = 'System'
               WHERE id_t_anggota = '$id_anggota'";

if (!mysqli_query($db, $sql_update)) {
    die(json_encode(['status' => 'error', 'message' => 'Gagal menonaktifkan akun']));
}

// Konfigurasi email
$mail = new PHPMailer(true);

try {
    // Konfigurasi Server
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pelindo.subregjawa@gmail.com';
    $mail->Password = 'vcpjhbtmbryvcikp';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Penerima
    $mail->setFrom('pelindo.subregjawa@gmail.com', 'Perpustakaan Digital Pelindo');
    $mail->addAddress($data['email'], $data['nama_anggota']);

    // Konten
    $mail->isHTML(true);
    $mail->Subject = 'Pemberitahuan Keterlambatan Pengembalian Buku';

    // Template email
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background-color: #f4f4f4; padding: 20px; border-radius: 8px;'>
        <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);'>
            <!-- Header dengan Logo -->
            <div style='text-align: center; padding-bottom: 20px;'>
                <img src='https://upload.wikimedia.org/wikipedia/commons/6/69/Logo_Baru_Pelindo_%282021%29.png' alt='Pelindo Logo' style='width: 150px;'>
                <h2 style='color: #003366;'>Perpustakaan Digital Pelindo</h2>
            </div>

            <p>Yth. <strong>{$data['nama_anggota']}</strong>,</p>
            
            <div style='background-color: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <h3 style='color: #c62828; margin-top: 0;'>⚠️ Pemberitahuan Penting</h3>
                <p style='color: #c62828;'>Akun Anda telah dinonaktifkan karena keterlambatan dalam mengembalikan buku.</p>
            </div>

            <div style='background: #ecf0f1; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <p><strong>Detail Keterlambatan:</strong></p>
                <ul>
                    <li>Buku yang dipinjam: {$data['daftar_buku']}</li>
                    <li>Tanggal Peminjaman: " . date('d F Y', strtotime($data['tgl_pinjam'])) . "</li>
                    <li>Tanggal Pengembalian: " . date('d F Y', strtotime($data['tgl_kembali'])) . "</li>
                    <li>Keterlambatan: {$data['hari_terlambat']} hari</li>
                    <li>Total Denda: Rp " . number_format($total_denda, 0, ',', '.') . "</li>
                </ul>
            </div>

            <div style='background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <h4 style='color: #1565c0; margin-top: 0;'>Langkah yang Harus Dilakukan:</h4>
                <ol>
                    <li>Segera kembalikan buku yang dipinjam ke Perpustakaan Digital Pelindo</li>
                    <li>Lakukan pembayaran denda keterlambatan</li>
                    <li>Setelah mengembalikan buku dan membayar denda, silakan hubungi admin perpustakaan untuk mengaktifkan kembali akun Anda</li>
                </ol>
            </div>

            <hr style='border: 1px solid #ddd; margin: 20px 0;'>

            <p style='text-align: center; color: #7f8c8d; font-size: 12px;'>
                Email ini dikirim secara otomatis oleh sistem <strong>Perpustakaan Digital Pelindo</strong>. Mohon untuk tidak membalas email ini.
            </p>
        </div>
    </div>";

    $mail->Body = $body;

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Pengingat berhasil dikirim dan akun dinonaktifkan']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim email: ' . $mail->ErrorInfo]);
}
?> 