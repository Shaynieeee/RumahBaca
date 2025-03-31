95% penyimpanan digunakan … Jika ruang penyimpanan sudah penuh, Anda tidak akan dapat membuat, mengedit, dan mengupload file. Dapatkan penyimpanan sebesar 30 GB seharga Rp 14.500,00 Rp 3.500,00/bulan untuk 3 bulan.
kirim_notifikasi_email.php
<?php
include("header.php");
require_once '../setting/koneksi.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('ID Peminjaman tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit;
}

$id_peminjaman = mysqli_real_escape_string($db, $_GET['id']);

// Ambil data peminjaman dan email anggota
$sql = "SELECT p.*, a.nama as nama_anggota, a.email, 
               GROUP_CONCAT(b.nama_buku SEPARATOR ', ') as daftar_buku 
        FROM t_peminjaman p 
        JOIN t_anggota a ON p.id_t_anggota = a.id_t_anggota 
        JOIN t_detil_pinjam dp ON p.id_t_peminjaman = dp.id_t_peminjaman
        JOIN t_buku b ON dp.id_t_buku = b.id_t_buku
        WHERE p.id_t_peminjaman = '$id_peminjaman'
        GROUP BY p.id_t_peminjaman";

$result = mysqli_query($db, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>alert('Data Peminjaman tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit;
}

// Hitung sisa hari
$tgl_kembali = new DateTime($data['tgl_kembali']);
$today = new DateTime(date('Y-m-d'));
$interval = $today->diff($tgl_kembali);
$sisa_hari = $interval->days;
$is_today = $today->format('Y-m-d') === $tgl_kembali->format('Y-m-d');
$is_late = $today > $tgl_kembali;

// Ambil pengaturan denda
$sql_denda = "SELECT * FROM t_pengaturan_denda";
$result_denda = mysqli_query($db, $sql_denda);
$pengaturan_denda = [];
while($row = mysqli_fetch_assoc($result_denda)) {
    $pengaturan_denda[$row['jenis_denda']] = $row['nilai_denda'];
}

$denda_per_hari = isset($pengaturan_denda['terlambat']) ? $pengaturan_denda['terlambat'] : 2000;
$toleransi_hari = isset($pengaturan_denda['toleransi']) ? $pengaturan_denda['toleransi'] : 1;

// Status keterlambatan dan pesan yang akan ditampilkan
if ($is_late) {
    $status_keterlambatan = "terlambat";
    $hari_terlambat = abs($interval->days);
    $total_denda = $hari_terlambat * $denda_per_hari;
} elseif ($is_today) {
    $status_keterlambatan = "hari_ini";
} else {
    $status_keterlambatan = "belum_terlambat";
}

$pesan = "... Denda keterlambatan sebesar Rp " . number_format($denda_per_hari, 0, ',', '.') . " per hari...";

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
    $mail->Subject = 'Pengingat Pengembalian Buku Perpustakaan';

    // Template email yang lebih profesional dengan tema Pelindo
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; background-color: #f4f4f4; padding: 20px; border-radius: 8px;'>
        <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);'>

            <!-- Header dengan Logo -->
            <div style='text-align: center; padding-bottom: 20px;'>
                <img src='https://upload.wikimedia.org/wikipedia/commons/6/69/Logo_Baru_Pelindo_%282021%29.png' alt='Pelindo Logo' style='width: 150px;'>
                <h2 style='color: #003366;'>Perpustakaan Digital Pelindo</h2>
            </div>

            <p>Yth. <strong>{$data['nama_anggota']}</strong>,</p>
            <p>Kami ingin mengingatkan Anda bahwa ada buku yang Anda pinjam dari Perpustakaan Digital Pelindo dan perlu dikembalikan sebelum batas waktu yang ditentukan.</p>

            <div style='background: #ecf0f1; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <p><strong>Buku yang dipinjam:</strong><br>
                {$data['daftar_buku']}</p>

                <p><strong>Tanggal Peminjaman:</strong><br>
                " . date('d F Y', strtotime($data['tgl_pinjam'])) . "</p>

                <p><strong>Tanggal Pengembalian:</strong><br>
                " . date('d F Y', strtotime($data['tgl_kembali'])) . "</p>";

    // Tampilkan status dan pesan sesuai kondisi
    if ($status_keterlambatan == "terlambat") {
        $body .= "<div style='background-color: #ffebee; padding: 15px; border-radius: 5px; margin: 10px 0;'>
                    <p style='color: #c62828; font-weight: bold; margin: 0;'>
                        ⚠️ Status: Terlambat " . $hari_terlambat . " hari
                    </p>
                    <p style='color: #c62828; margin: 10px 0 0 0;'>
                        Total Denda: Rp " . number_format($total_denda, 0, ',', '.') . "<br>
                        <small>(Denda per hari: Rp " . number_format($denda_per_hari, 0, ',', '.') . ")</small>
                    </p>
                 </div>";
    } elseif ($status_keterlambatan == "hari_ini") {
        $body .= "<div style='background-color: #fff3e0; padding: 15px; border-radius: 5px; margin: 10px 0;'>
                    <p style='color: #e65100; font-weight: bold; margin: 0;'>
                        ⚠️ Hari ini adalah batas waktu pengembalian!
                    </p>
                    <p style='color: #e65100; margin: 10px 0 0 0;'>
                        Mohon segera melakukan pengembalian hari ini untuk menghindari denda keterlambatan sebesar Rp " . number_format($denda_per_hari, 0, ',', '.') . " per hari.
                    </p>
                 </div>";
    } else {
        $body .= "<div style='background-color: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0;'>
                    <p style='color: #2e7d32; font-weight: bold; margin: 0;'>
                        Sisa waktu: " . $sisa_hari . " hari
                    </p>
                    <p style='color: #2e7d32; margin: 10px 0 0 0;'>
                        Informasi Denda: Keterlambatan pengembalian akan dikenakan denda sebesar Rp " . number_format($denda_per_hari, 0, ',', '.') . " per hari.
                    </p>
                 </div>";
    }

    $body .= "
            </div>

            <p>Mohon segera mengembalikan buku ke Perpustakaan Digital Pelindo untuk menghindari denda keterlambatan.</p>

            <hr style='border: 1px solid #ddd; margin: 20px 0;'>

            <p style='text-align: center; color: #7f8c8d; font-size: 12px;'>
                Email ini dikirim secara otomatis oleh sistem <strong>Perpustakaan Digital Pelindo</strong>. Mohon untuk tidak membalas email ini.
            </p>

        </div>
    </div>";

    $mail->Body = $body;

    $mail->send();
    echo "<script>
            alert('Email pengingat berhasil dikirim ke {$data['nama_anggota']}!');
            window.location='dashboard.php';
          </script>";

} catch (Exception $e) {
    echo "<script>
            alert('Gagal mengirim email: {$mail->ErrorInfo}');
            window.location='dashboard.php';
          </script>";
}
?>