<?php
session_start();
include "../setting/koneksi.php";

// Cek apakah ada ID buku
if (!isset($_GET['id'])) {
    header("Location: catalog.php");
    exit;
}

$id_buku = (int)$_GET['id'];
$query = "SELECT * FROM t_buku WHERE id_t_buku = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $id_buku);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$buku = mysqli_fetch_assoc($result)) {
    die("Buku tidak ditemukan");
}

// Set batas halaman untuk guest
$batas_halaman = $buku['batas_baca_guest'] ?? 5;

// Cek apakah file buku ada
if (empty($buku['file_buku'])) {
    die("File buku tidak tersedia");
}

// Verifikasi file exists
$file_path = "../image/buku/" . $buku['file_buku'];
if (!file_exists($file_path)) {
    die("File buku tidak ditemukan di server");
}
?>

<!DOCTYPE html>
<html>
<head>
          <!-- Multiple favicon sizes -->
          <link rel="icon" type="image/png" sizes="32x32" href="../public/assets/pelindo-logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../public/assets/pelindo-logo.png">
    <link rel="shortcut icon" href="../public/assets/pelindo-logo.png">

    <title>Baca Buku - <?php echo htmlspecialchars($buku['nama_buku']); ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }
        .blur-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.95);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pdf-container {
            width: 100%;
            height: 100vh;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 20px 20px;
            background: #f5f5f5;
        }
        .login-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .login-btn {
            background: #007bff;
            color: white;
            padding: 10px 30px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .login-btn:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
        }
        .book-info {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.9);
            padding: 10px;
            z-index: 100;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        .controls {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
            background: rgba(255,255,255,0.9);
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        canvas {
            margin-top: 38px;
            max-width: 85%;
            height: 85%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="book-info">
        <h5 class="mb-0"><?php echo htmlspecialchars($buku['nama_buku']); ?></h5>
        <small>oleh <?php echo htmlspecialchars($buku['penulis']); ?></small>
        <div id="page-info" class="text-muted">
            <small>Halaman: <span id="current-page">1</span></small>
        </div>
    </div>

    <div class="pdf-container">
        <canvas id="pdf-render"></canvas>
    </div>

    <div class="controls">
        <button id="prev-page" class="btn btn-light">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button id="next-page" class="btn btn-light">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>

    <div id="blur-overlay" class="blur-overlay" style="display: none;">
        <div class="login-card">
            <h3>Ingin membaca lebih banyak?</h3>
            <p class="mb-4">Login untuk membaca buku secara lengkap</p>
            <a href="../login.php" class="login-btn">
                <i class="fas fa-sign-in-alt mr-2"></i>Login Sekarang
            </a>
        </div>
    </div>

    <script>
    let currentPage = 1;
    let pdfDoc = null;
    const scale = 1.5;
    const batasHalaman = <?php echo (int)$batas_halaman; ?>;
    const canvas = document.getElementById('pdf-render');
    const ctx = canvas.getContext('2d');

    pdfjsLib.getDocument('../image/buku/<?php echo htmlspecialchars($buku['file_buku']); ?>')
        .promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            renderPage(currentPage);
        });

    function renderPage(num) {
        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({ scale: scale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };

            page.render(renderContext).promise.then(function() {
                document.getElementById('current-page').textContent = num;
                
                if(num >= batasHalaman) {
                    document.getElementById('blur-overlay').style.display = 'flex';
                }
            });
        });
    }

    document.getElementById('prev-page').addEventListener('click', function() {
        if(currentPage <= 1) return;
        currentPage--;
        renderPage(currentPage);
    });

    document.getElementById('next-page').addEventListener('click', function() {
        if(currentPage >= batasHalaman) {
            document.getElementById('blur-overlay').style.display = 'flex';
            return;
        }
        if(currentPage >= pdfDoc.numPages) return;
        currentPage++;
        renderPage(currentPage);
    });

    document.addEventListener('keydown', function(e) {
        if(e.key === 'ArrowLeft') {
            document.getElementById('prev-page').click();
        } else if(e.key === 'ArrowRight') {
            document.getElementById('next-page').click();
        }
    });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 