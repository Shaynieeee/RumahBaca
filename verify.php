<?php
require_once 'setting/koneksi.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $query = "SELECT id_t_anggota FROM t_account WHERE token = ? AND is_verified = 0";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $id_t_anggota = $row['id_t_anggota'];

        // Update status verifikasi
        $update_query = "UPDATE t_account SET is_verified = 1, token = NULL WHERE id_t_anggota = ?";
        $update_stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($update_stmt, "i", $id_t_anggota);
        mysqli_stmt_execute($update_stmt);

        echo "<div style='text-align: center; margin-top: 50px;'>
                <h2>Verifikasi Berhasil!</h2>
                <p>Akun Anda telah berhasil diverifikasi.</p>
                <p>Silakan <a href='login.php'>login</a> untuk melanjutkan.</p>
              </div>";
    } else {
        echo "<div style='text-align: center; margin-top: 50px;'>
                <h2>Verifikasi Gagal</h2>
                <p>Token tidak valid atau akun sudah diverifikasi.</p>
                <p>Silakan <a href='login.php'>kembali ke halaman login</a>.</p>
              </div>";
    }
} else {
    echo "<div style='text-align: center; margin-top: 50px;'>
            <h2>Error</h2>
            <p>Token tidak ditemukan.</p>
            <p>Silakan <a href='login.php'>kembali ke halaman login</a>.</p>
          </div>";
}
?>
