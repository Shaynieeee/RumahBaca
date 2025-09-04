<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once '../../setting/koneksi.php';

// cek scopes
$scopes = [];
if (isset($_SESSION['login_user'])) {
    $u = mysqli_real_escape_string($db, $_SESSION['login_user']);
    $sql_s = "SELECT s.name FROM t_account a
              JOIN t_role_scope rs ON a.id_p_role = rs.role_id
              JOIN t_scope s ON rs.scope_id = s.id
              WHERE a.username = '$u'";
    $rs = mysqli_query($db, $sql_s);
    if ($rs) {
        while ($r = mysqli_fetch_assoc($rs))
            $scopes[] = strtolower(trim($r['name']));
    }
}
if (!isset($_SESSION['login_user']) || (!in_array('cetakkartu-member', $scopes) && !in_array('profil-member', $scopes))) {
    header("location: ../../login.php");
    exit();
}
?>
<?php
require_once('../../tcpdf/tcpdf.php');
require_once '../../setting/koneksi.php';
// require_once '../../phpqrcode/qrlib.php'; // Pastikan pustaka QR Code tersedia

// Tambahkan favicon untuk tab browser
echo '<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../../public/assets/logo-rumahbaca.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../public/assets/logo-rumahbaca.png">
    <link rel="shortcut icon" href="../../public/assets/logo-rumahbaca.png">
</head>
<body>';

// Ambil data anggota berdasarkan ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM t_anggota WHERE id_t_anggota = ?";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$anggota = mysqli_fetch_assoc($result);

if (!$anggota) {
    die("Data anggota tidak ditemukan");
}

// Pastikan folder temp ada
$tempDir = '../../temp/';
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0777, true);
}

// // Buat QR Code
// $qrData = "ID: " . $anggota['no_anggota'] . "\nNama: " . $anggota['nama'] . "\nStatus: " . $anggota['status'];
// $qrFile = $tempDir . 'qrcode_' . $anggota['no_anggota'] . '.png';
// QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 4);

// Buat objek TCPDF
$pdf = new TCPDF('P', 'mm', [90, 55], true, 'UTF-8', false); // Ukuran kartu sesuai (90mm x 55mm)
$pdf->SetMargins(5, 5, 5);
$pdf->AddPage();

// Logo Perpustakaan
$logo = '../../public/assets/pelindo-logo.png'; // Logo Pelindo untuk kartu
if (file_exists($logo)) {
    $pdf->Image($logo, 5, 5, 20, 20, 'PNG');
}

// QR Code
if (file_exists($qrFile)) {
    $pdf->Image($qrFile, 65, 25, 18, 18, 'PNG');
}

// Desain Kartu Depan
$htmlFront = '<div style="border: 2px solid #003366; padding: 5px; width: 100%; background-color: #e0f7fa; border-radius: 8px; text-align: center;">
            <h4 style="color: #004d99; margin-bottom: 2px;">PERPUSTAKAAN DIGITAL</h4>
            <h4 style="background-color: #004d99; color: white; padding: 5px; border-radius: 5px; margin-top: 0;">KARTU ANGGOTA</h4>
            <table border="0" cellpadding="3" width="100%" style="font-size: 8px; text-align: left; color: #004d99;">
                <tr><td><b>No. Anggota:</b></td><td>' . htmlspecialchars($anggota['no_anggota']) . '</td></tr>
                <tr><td><b>Nama:</b></td><td>' . htmlspecialchars($anggota['nama']) . '</td></tr>
                <tr><td><b>Tanggal Daftar:</b></td><td>' . date('d F Y', strtotime($anggota['tgl_daftar'])) . '</td></tr>
                <tr><td><b>Status:</b></td><td><span style="color: green; font-weight: bold;">' . htmlspecialchars($anggota['status']) . '</span></td></tr>
            </table>
        </div>';
$pdf->writeHTML($htmlFront, true, false, true, false, '');

$pdf->AddPage(); // Tambah halaman untuk sisi belakang kartu

// Desain Kartu Belakang
$htmlBack = '<div style="border: 2px solid #003366; padding: 5px; width: 100%; background-color: #f1f1f1; border-radius: 8px; text-align: center;">
                <h4 style="color: #004d99;">PERATURAN ANGGOTA</h4>
                <ul style="text-align: left; font-size: 7px;">
                    <li>Kartu ini hanya boleh digunakan oleh pemiliknya.</li>
                    <li>Kehilangan kartu harus segera dilaporkan.</li>
                    <li>Menjaga kebersihan dan ketertiban perpustakaan.</li>
                    <li>Mengembalikan buku tepat waktu.</li>
                </ul>
                <div style="margin-top: 8px; font-size: 7px;">Hubungi: 123-456-789 | Email: info@perpustakaan.com</div>
            </div>';
$pdf->writeHTML($htmlBack, true, false, true, false, '');

// Bersihkan output buffer sebelum mengeluarkan PDF
ob_clean();

// Output file PDF
$pdf->Output('Kartu_Anggota.pdf', 'I');
?>