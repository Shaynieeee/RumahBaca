<?php
   include('koneksi.php');
   
   // Cek apakah session sudah dimulai
   if (session_status() === PHP_SESSION_NONE) {
       session_start();
   }
   
   // Cek apakah user sudah login
   if(!isset($_SESSION['login_user'])){
      header("location: ../login.php");
      exit();
   }
   
   $user_check = $_SESSION['login_user'];
   
   // Query diperbarui untuk mengecek status dengan benar
   $ses_sql = "SELECT a.*, r.nama_role, 
               CASE 
                   WHEN a.id_p_role = 3 THEN (
                       SELECT status 
                       FROM t_anggota 
                       WHERE id_t_anggota = a.id_t_anggota
                   )
                   WHEN a.id_p_role = 2 THEN (
                       SELECT status 
                       FROM t_staff 
                       WHERE id_t_account = a.id_t_account
                   )
                   WHEN a.id_p_role = 1 THEN 'Aktif'
               END as user_status
               FROM t_account a
               JOIN p_role r ON a.id_p_role = r.id_p_role
               WHERE a.username = ?";

   $stmt = mysqli_prepare($db, $ses_sql);
   mysqli_stmt_bind_param($stmt, "s", $user_check);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);

   if($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
       $login_session = $row['username'];
       $_SESSION['id_t_account'] = $row['id_t_account'];
       $_SESSION['id_t_anggota'] = $row['id_t_anggota'];
       
       // Cek status user kecuali untuk admin
       if($row['id_p_role'] != 1 && ($row['user_status'] === 'Tidak Aktif' || $row['user_status'] === NULL)) {
           session_destroy();
           header("location: ../login.php?error=inactive");
           exit();
       }
   } else {
       session_destroy();
       header("location: ../login.php");
       exit();
   }

   // Cek role untuk akses halaman
   function cek_role($allowed_roles) {
       if (!in_array($_SESSION['role'], $allowed_roles)) {
           header("location: ../login.php");
           exit();
       }
   }
?>