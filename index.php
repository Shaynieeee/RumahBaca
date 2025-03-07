<?php
session_start();
if(isset($_SESSION['level'])) {
    switch($_SESSION['level']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'staff':
            header("Location: staff/dashboard.php");
            break;
        default:
            header("Location: public/landing.php");
    }
} else {
    header("Location: public/landing.php");
}
exit();

// Cek pesan dari ganti password
if(isset($_GET['pesan'])) {
    if($_GET['pesan'] == "password_changed") {
        echo "<div class='alert alert-success'>Password berhasil diubah. Silahkan login ulang.</div>";
    }
}
