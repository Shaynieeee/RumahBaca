<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../setting/koneksi.php';

// Cek login
if(!isset($_SESSION['login_user']) || $_SESSION['role'] != 3) {
    header("location: ../../login.php");
    exit();
}

if(isset($_GET['id'])) {
    $id_buku = (int)$_GET['id'];
    $username = $_SESSION['login_user'];
    
    // Dapatkan id_anggota
    $sql_anggota = "SELECT id_t_anggota FROM t_account WHERE username = ?";
    $stmt = mysqli_prepare($db, $sql_anggota);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $anggota = mysqli_fetch_assoc($result);
    
    // Catat waktu mulai baca
    $sql = "INSERT INTO t_riwayat_baca 
            (id_t_buku, id_t_anggota, tanggal_baca, waktu_mulai) 
            VALUES (?, ?, CURRENT_DATE(), CURRENT_TIME())";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_buku, $anggota['id_t_anggota']);
    mysqli_stmt_execute($stmt);
    
    // Ambil data buku
    $sql_buku = "SELECT * FROM t_buku WHERE id_t_buku = ?";
    $stmt = mysqli_prepare($db, $sql_buku);
    mysqli_stmt_bind_param($stmt, "i", $id_buku);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $buku = mysqli_fetch_assoc($result);
} else {
    header("Location: index.php");
    exit();
}

// Update total view
$sql_update = "UPDATE t_buku SET total_view = total_view + 1 WHERE id_t_buku = ?";
$stmt_update = mysqli_prepare($db, $sql_update);
mysqli_stmt_bind_param($stmt_update, "i", $id_buku);
mysqli_stmt_execute($stmt_update);

include("header_anggota.php");
?>

<div id="page-wrapper">
    <div class="book-reader-container">
        <!-- Control Bar -->
        <div class="control-bar">
            <div class="left-controls">
                <button id="prevPage" class="btn btn-light">
                    <i class="fa fa-chevron-left"></i>
                </button>
                <button id="nextPage" class="btn btn-light">
                    <i class="fa fa-chevron-right"></i>
                </button>
                <span class="page-info">
                    Halaman <input type="number" id="pageNumber" value="1" min="1" 
                           max="<?php echo $buku['total_halaman']; ?>">
                    dari <span id="pageCount"><?php echo $buku['total_halaman']; ?></span>
                </span>
            </div>
            <div class="center-controls">
                <button id="zoomOut" class="btn btn-light" title="Perkecil" style="background: transparent;  border:none; cursor:default;">
                    <!-- <i class="fa fa-search-minus"></i> -->
                </button>
                <button id="zoomIn" class="btn btn-light" title="Perbesar" style="background: transparent;  border:none; cursor:default;">
                    <!-- <i class="fa fa-search-plus"></i> -->
                </button>
                <button id="toggleTwoPage" class="btn btn-light" title="Tampilan 2 Halaman">
                    <i class="fa fa-book-open"></i>
                </button>
                <button id="toggleFullscreen" class="btn btn-light" title="Layar Penuh">
                    <i class="fa fa-expand"></i>
                </button>
            </div>
            <div class="right-controls">
                <form method="POST" id="formTutup">
                    <input type="hidden" name="current_page" id="current_page" value="1">
                    <a href="data_buku.php" class="btn btn-secondary" onclick="selesaiBaca()">
                        <i class="fa fa-times"></i> Tutup
                    </a>
                </form>
            </div>
        </div>

        <!-- Book Viewer -->
        <div id="book-viewer">
            <div id="pdf_container" class="book-container">
                <canvas id="pdf_renderer_left" class="page-canvas"></canvas>
                <canvas id="pdf_renderer_right" class="page-canvas"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Overlay untuk mencegah screenshot -->
<div id="screenshotOverlay" class="screenshot-overlay">
    <div class="overlay-content">
        <h2>⚠️ PERINGATAN HAK CIPTA!</h2>
        <p>Screenshot dan screen recording tidak diizinkan.<br>Konten ini dilindungi hak cipta.</p>
        <button onclick="hideScreenshotOverlay()">Tutup</button>
    </div>
</div>

<style>
.book-reader-container {
    background: #2a2a2a;
    height: calc(100vh - 100px);
    display: flex;
    flex-direction: column;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.control-bar {
    background: #333;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #444;
}

.left-controls, .center-controls, .right-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-info {
    color: #fff;
    margin: 0 15px;
}

#pageNumber {
    width: 60px;
    background: #444;
    border: 1px solid #555;
    color: #fff;
    padding: 3px;
    text-align: center;
}

.book-container {
    display: flex;
    justify-content: center;
    align-items: center;
    flex: 1;
    background: #525659;
    padding: 12px;
    gap: 20px;
    position: relative;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.page-canvas {
    background: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    max-height: calc(100vh - 180px);
    position: relative;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    pointer-events: none;
}

.single-page-mode .page-canvas:nth-child(2) {
    display: none;
}

.btn {
    padding: 5px 10px;
    background: #444;
    border: 1px solid #555;
    color: #fff;
}

.btn:hover {
    background: #555;
}

.fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
}

/* Style untuk overlay blur dan pesan hak cipta */
.copyright-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    z-index: 99999;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    text-align: center;
    padding: 20px;
}

.copyright-content {
    background: rgba(255, 255, 255, 0.95);
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
}

.copyright-message {
    font-size: 32px;
    color: #dc3545;
    font-weight: bold;
    margin-bottom: 20px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.copyright-submessage {
    font-size: 20px;
    color: #333;
    line-height: 1.6;
    margin-bottom: 30px;
}

.close-overlay-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 12px 30px;
    font-size: 18px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.close-overlay-btn:hover {
    background: #c82333;
}

/* Tambahkan CSS untuk mencegah screenshot */
@media print {
    .book-container {
        display: none !important;
    }
    .copyright-overlay {
        display: flex !important;
    }
}

.screenshot-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.95);
    z-index: 999999;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.overlay-content {
    background-color: white;
    padding: 2rem;
    border-radius: 10px;
    text-align: center;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 0 20px rgba(0,0,0,0.5);
}

.overlay-content h2 {
    color: #dc3545;
    font-size: 24px;
    margin-bottom: 1rem;
}

.overlay-content p {
    color: #333;
    font-size: 18px;
    line-height: 1.5;
    margin-bottom: 1.5rem;
}

.overlay-content button {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.overlay-content button:hover {
    background-color: #c82333;
}

.page-canvas {
    pointer-events: none !important;
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';

let pdfDoc = null,
    pageNum = 1,
    pageRendering = false,
    pageNumPending = null,
    scale = 1.0,
    isTwoPageMode = false,
    isFullscreen = false,
    currentRenderTask = null;

// Fungsi untuk membatalkan render yang sedang berjalan
function cancelRendering() {
    if (currentRenderTask) {
        currentRenderTask.cancel();
        currentRenderTask = null;
    }
}

async function renderPage(pageNum, canvasId) {
    const canvas = document.getElementById(canvasId);
    const ctx = canvas.getContext('2d');
    
    try {
        cancelRendering(); // Batalkan render sebelumnya
        
        const page = await pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: scale });
        
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        
        currentRenderTask = page.render({
            canvasContext: ctx,
            viewport: viewport
        });
        
        await currentRenderTask.promise;
        currentRenderTask = null;
    } catch (error) {
        if (error.name === 'RenderingCancelled') {
            // Abaikan error pembatalan
            return;
        }
        console.error('Error rendering page:', error);
    }
}

async function renderPages(num) {
    if (pageRendering) {
        pageNumPending = num;
        return;
    }
    
    pageRendering = true;
    
    try {
        // Render halaman kiri
        await renderPage(num, 'pdf_renderer_left');
        
        // Render halaman kanan jika dalam mode 2 halaman
        if (isTwoPageMode && num < pdfDoc.numPages) {
            const rightCanvas = document.getElementById('pdf_renderer_right');
            rightCanvas.style.display = 'block';
            await renderPage(num + 1, 'pdf_renderer_right');
        } else {
            const rightCanvas = document.getElementById('pdf_renderer_right');
            rightCanvas.style.display = 'none';
        }
        
        pageNum = num;
        document.getElementById('pageNumber').value = num;
    } catch (error) {
        console.error('Error in renderPages:', error);
    } finally {
        pageRendering = false;
        if (pageNumPending !== null) {
            renderPages(pageNumPending);
            pageNumPending = null;
        }
    }
}

// Fungsi zoom yang diperbaiki
async function updateZoom(newScale) {
    if (newScale < 0.25) newScale = 0.25;
    if (newScale > 3.0) newScale = 3.0;
    
    if (pageRendering) {
        return; // Jangan zoom jika masih rendering
    }
    
    scale = newScale;
    await renderPages(pageNum);
    
    // Update zoom level display
    const zoomPercent = Math.round(scale * 100);
    document.getElementById('zoomLevel').textContent = `${zoomPercent}%`;
}

// Event listeners
document.getElementById('zoomIn').addEventListener('click', () => {
    updateZoom(scale * 1.2);
});

document.getElementById('zoomOut').addEventListener('click', () => {
    updateZoom(scale * 0.8);
});

document.getElementById('prevPage').addEventListener('click', () => {
    if (pageNum <= 1) return;
    pageNum -= isTwoPageMode ? 2 : 1;
    if (pageNum < 1) pageNum = 1;
    renderPages(pageNum);
});

document.getElementById('nextPage').addEventListener('click', () => {
    if (pageNum >= pdfDoc.numPages) return;
    pageNum += isTwoPageMode ? 2 : 1;
    if (pageNum > pdfDoc.numPages) pageNum = pdfDoc.numPages;
    renderPages(pageNum);
});

document.getElementById('toggleTwoPage').addEventListener('click', () => {
    isTwoPageMode = !isTwoPageMode;
    const container = document.getElementById('pdf_container');
    container.classList.toggle('single-page-mode');
    
    // Ensure we're on an odd page number when switching to two-page mode
    if (isTwoPageMode && pageNum % 2 === 0) {
        pageNum--;
    }
    
    renderPages(pageNum);
    
    // Update button appearance
    const button = document.getElementById('toggleTwoPage');
    button.classList.toggle('active');
    if (isTwoPageMode) {
        button.title = 'Tampilan 1 Halaman';
    } else {
        button.title = 'Tampilan 2 Halaman';
    }
});

// Fungsi fullscreen yang diperbaiki
function toggleFullscreen() {
    const container = document.querySelector('.book-reader-container');
    
    if (!document.fullscreenElement && 
        !document.webkitFullscreenElement && 
        !document.msFullscreenElement) {
        
        isFullscreen = true;
        container.classList.add('fullscreen');
        
        if (container.requestFullscreen) {
            container.requestFullscreen();
        } else if (container.webkitRequestFullscreen) {
            container.webkitRequestFullscreen();
        } else if (container.msRequestFullscreen) {
            container.msRequestFullscreen();
        }
    } else {
        isFullscreen = false;
        container.classList.remove('fullscreen');
        
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
    
    // Re-render setelah toggle fullscreen
    setTimeout(() => {
        renderPages(pageNum);
    }, 100);
}

// Event listener untuk fullscreen
document.getElementById('toggleFullscreen').addEventListener('click', toggleFullscreen);

// Handle fullscreen change events
document.addEventListener('fullscreenchange', handleFullscreenChange);
document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
document.addEventListener('mozfullscreenchange', handleFullscreenChange);
document.addEventListener('MSFullscreenChange', handleFullscreenChange);

function handleFullscreenChange() {
    const container = document.querySelector('.book-reader-container');
    isFullscreen = !!(document.fullscreenElement || 
                      document.webkitFullscreenElement || 
                      document.mozFullscreenElement || 
                      document.msFullscreenElement);
    
    container.classList.toggle('fullscreen', isFullscreen);
    
    // Re-render untuk menyesuaikan ukuran
    setTimeout(() => {
        renderPages(pageNum);
    }, 100);
}

// Tambahkan CSS untuk fullscreen
const fullscreenStyles = `
.book-reader-container.fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    background: #2a2a2a;
    width: 100vw;
    height: 100vh;
    margin: 0;
    padding: 0;
}

.fullscreen .control-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 10000;
}

.fullscreen #book-viewer {
    height: calc(100vh - 60px);
    margin-top: 60px;
}

.fullscreen .book-container {
    height: 100%;
    max-height: calc(100vh - 60px);
}

.fullscreen .page-canvas {
    max-height: calc(100vh - 80px);
}

.fullscreen #toggleFullscreen i::before {
    content: "\\f066"; /* Font Awesome compress icon */
}
`;

// Tambahkan styles ke head
const styleSheet = document.createElement("style");
styleSheet.textContent = fullscreenStyles;
document.head.appendChild(styleSheet);

// Update icon fullscreen button
function updateFullscreenButton() {
    const button = document.getElementById('toggleFullscreen');
    const icon = button.querySelector('i');
    if (isFullscreen) {
        icon.classList.remove('fa-expand');
        icon.classList.add('fa-compress');
        button.title = 'Keluar Layar Penuh';
    } else {
        icon.classList.remove('fa-compress');
        icon.classList.add('fa-expand');
        button.title = 'Layar Penuh';
    }
}

// Tambahkan ke handleFullscreenChange
function handleFullscreenChange() {
    // ... kode sebelumnya ...
    updateFullscreenButton();
}

// Page number input
document.getElementById('pageNumber').addEventListener('change', function() {
    let num = parseInt(this.value);
    if (num >= 1 && num <= pdfDoc.numPages) {
        // Ensure odd page number in two-page mode
        if (isTwoPageMode && num % 2 === 0) {
            num--;
        }
        pageNum = num;
        renderPages(pageNum);
    }
});

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
        document.getElementById('prevPage').click();
    } else if (e.key === 'ArrowRight') {
        document.getElementById('nextPage').click();
    }
});

// Handle window resize
window.addEventListener('resize', () => {
    if (!pageRendering) {
        renderPages(pageNum);
    }
});

// Load PDF
pdfjsLib.getDocument('../../image/buku/<?php echo htmlspecialchars($buku['file_buku']); ?>').promise
    .then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        document.getElementById('pageCount').textContent = pdfDoc.numPages;
        renderPages(pageNum);
    })
    .catch(function(error) {
        console.error('Error loading PDF:', error);
    });

// Update current_page saat navigasi halaman
document.getElementById('prevPage').addEventListener('click', function() {
    document.getElementById('current_page').value = pageNum;
});

document.getElementById('nextPage').addEventListener('click', function() {
    document.getElementById('current_page').value = pageNum;
});

// Update current_page saat input halaman berubah
document.getElementById('pageNumber').addEventListener('change', function() {
    let num = parseInt(this.value);
    if (num >= 1 && num <= pdfDoc.numPages) {
        document.getElementById('current_page').value = num;
    }
});

// Update fungsi selesaiBaca di baca-buku.php
function selesaiBaca() {
    const currentPage = pageNum; // Menggunakan pageNum langsung dari PDF viewer
    fetch('proses_selesai_baca.php?id_buku=<?php echo $id_buku; ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'halaman_terakhir=' + currentPage
    })
    .then(response => response.json())
    .then(data => {
        console.log('Halaman terakhir tersimpan:', data.halaman_terakhir);
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Update event listener untuk tombol tutup
document.querySelector('.right-controls .btn-secondary').addEventListener('click', function(e) {
    e.preventDefault();
    selesaiBaca();
    window.location.href = 'data_buku.php';
});

// Tambahkan event listener untuk update halaman
document.getElementById('pageNumber').addEventListener('change', function() {
    let num = parseInt(this.value);
    if (num >= 1 && num <= pdfDoc.numPages) {
        pageNum = num;
        document.getElementById('current_page').value = pageNum;
    }
});

// Update halaman saat navigasi
document.getElementById('prevPage').addEventListener('click', function() {
    if (pageNum > 1) {
        pageNum--;
        document.getElementById('current_page').value = pageNum;
    }
});

document.getElementById('nextPage').addEventListener('click', function() {
    if (pageNum < pdfDoc.numPages) {
        pageNum++;
        document.getElementById('current_page').value = pageNum;
    }
});

// Fungsi untuk menampilkan overlay hak cipta yang diperbarui
function showCopyrightOverlay() {
    const overlay = document.getElementById('copyrightOverlay');
    overlay.style.display = 'flex';
    
    // Tambahkan blur ke konten saat overlay muncul
    document.getElementById('pdf_container').style.filter = 'blur(20px)';
}

// Fungsi untuk menyembunyikan overlay
function hideCopyrightOverlay() {
    const overlay = document.getElementById('copyrightOverlay');
    overlay.style.display = 'none';
    document.getElementById('pdf_container').style.filter = 'none';
}

// Perkuat deteksi screenshot
window.addEventListener('keydown', function(e) {
    // Deteksi kombinasi tombol screenshot
    if (
        (e.key === 'PrintScreen') || 
        (e.ctrlKey && e.key === 'p') || 
        (e.ctrlKey && e.key === 's') ||
        (e.ctrlKey && e.shiftKey && e.key === 'i') ||
        (e.ctrlKey && e.shiftKey && e.key === 'c') ||
        (e.ctrlKey && e.shiftKey && e.key === 'j') ||
        (e.metaKey && e.shiftKey && e.key === 's')
    ) {
        e.preventDefault();
        showCopyrightOverlay();
        return false;
    }
}, true);

// Tambahkan event listener untuk mencegah copy-paste
document.addEventListener('copy', function(e) {
    e.preventDefault();
    showCopyrightOverlay();
});

document.addEventListener('paste', function(e) {
    e.preventDefault();
    showCopyrightOverlay();
});

// Nonaktifkan menu klik kanan
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    showCopyrightOverlay();
});

// Deteksi screen recording menggunakan Fullscreen API
document.addEventListener('fullscreenchange', checkScreenRecording);
document.addEventListener('webkitfullscreenchange', checkScreenRecording);
document.addEventListener('mozfullscreenchange', checkScreenRecording);
document.addEventListener('MSFullscreenChange', checkScreenRecording);

function checkScreenRecording() {
    if (document.fullscreenElement || 
        document.webkitFullscreenElement || 
        document.mozFullScreenElement || 
        document.msFullscreenElement) {
        showCopyrightOverlay();
    }
}

// Tambahkan deteksi untuk DevTools
let devToolsCheck = setInterval(() => {
    const widthThreshold = window.outerWidth - window.innerWidth > 160;
    const heightThreshold = window.outerHeight - window.innerHeight > 160;
    
    if(widthThreshold || heightThreshold) {
        showCopyrightOverlay();
    }
}, 1000);

// Tambahkan deteksi untuk tab visibility
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        showCopyrightOverlay();
    }
});

// Fungsi untuk menampilkan overlay
function showScreenshotOverlay() {
    const overlay = document.getElementById('screenshotOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
    }
}

// Fungsi untuk menyembunyikan overlay
function hideScreenshotOverlay() {
    const overlay = document.getElementById('screenshotOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Deteksi screenshot menggunakan berbagai metode
document.addEventListener('keyup', function(e) {
    if (e.key === 'PrintScreen') {
        showScreenshotOverlay();
    }
});

document.addEventListener('keydown', function(e) {
    // Deteksi kombinasi tombol umum untuk screenshot
    if (
        (e.key === 'PrintScreen') ||
        (e.ctrlKey && e.key === 'p') ||
        (e.ctrlKey && e.key === 'P') ||
        (e.metaKey && e.key === 'p') ||
        (e.metaKey && e.key === 'P') ||
        (e.ctrlKey && e.shiftKey && ['I', 'i', 'J', 'j', 'C', 'c'].includes(e.key)) ||
        (e.ctrlKey && e.altKey && ['I', 'i', 'J', 'j', 'C', 'c'].includes(e.key))
    ) {
        e.preventDefault();
        showScreenshotOverlay();
        return false;
    }
});

// Deteksi screenshot pada browser yang mendukung
if ('ClipboardItem' in window) {
    navigator.permissions.query({ name: 'clipboard-write' }).then(result => {
        if (result.state === 'granted') {
            document.addEventListener('copy', function(e) {
                showScreenshotOverlay();
            });
        }
    });
}

// Deteksi perubahan visibilitas halaman
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        showScreenshotOverlay();
    }
});

// Deteksi screen capture menggunakan Fullscreen API
document.addEventListener('fullscreenchange', function() {
    if (document.fullscreenElement) {
        showScreenshotOverlay();
    }
});

// Deteksi penggunaan DevTools
window.addEventListener('resize', function() {
    if (window.outerWidth - window.innerWidth > 160 || 
        window.outerHeight - window.innerHeight > 160) {
        showScreenshotOverlay();
    }
});

// Nonaktifkan menu konteks
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    showScreenshotOverlay();
});

// Deteksi screenshot menggunakan MutationObserver
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'childList') {
            showScreenshotOverlay();
        }
    });
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Tambahan: Deteksi perubahan pada clipboard
document.addEventListener('paste', function(e) {
    showScreenshotOverlay();
});

// Deteksi drag and drop
document.addEventListener('dragstart', function(e) {
    e.preventDefault();
    showScreenshotOverlay();
});

// Deteksi selection
document.addEventListener('selectstart', function(e) {
    e.preventDefault();
    showScreenshotOverlay();
});

// Tambahan: Deteksi screenshot pada mobile
window.addEventListener('touchstart', function(e) {
    if (e.touches.length > 2) {
        showScreenshotOverlay();
    }
});

// Deteksi screenshot pada browser tertentu
if (navigator.userAgent.toLowerCase().indexOf('chrome') > -1) {
    window.addEventListener('beforeprint', function(e) {
        e.preventDefault();
        showScreenshotOverlay();
    });
}
</script>

<?php include "footer.php"; ?>

<?php
// Tambahkan query update halaman terakhir di proses_selesai_baca.php
if(isset($_POST['halaman_terakhir'])) {
    $halaman_terakhir = (int)$_POST['halaman_terakhir'];
    
    $sql_update = "UPDATE t_riwayat_baca 
                   SET halaman_terakhir = ?
                   WHERE id_t_anggota = ? 
                   AND id_t_buku = ? 
                   AND waktu_selesai IS NULL";
    
    $stmt = mysqli_prepare($db, $sql_update);
    mysqli_stmt_bind_param($stmt, "iii", 
        $halaman_terakhir,
        $anggota['id_t_anggota'],
        $id_buku
    );
    mysqli_stmt_execute($stmt);
}
?> 