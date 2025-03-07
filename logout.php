<?php
   session_start();
   
   if(session_destroy()) {
      header("Location: ../perpustakaan/public/landing.php");
   }
?>