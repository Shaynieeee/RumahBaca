        </div>
        <!-- /#page-wrapper -->
    </div>
    <!-- /#wrapper -->

    <footer class="footer bg-dark" style="margin-left: 250px;">
        <div class="container-fluid py-1 bg-dark">
            <div class="row">
                <div class="col-md-4">
                    <!-- <h5 class="text-white">Tentang Kami</h5> -->
                    <p class="text-muted">Perpustakaan Digital untuk semua</p>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white">Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="catalog.php" class="text-muted">Katalog</a></li>
                        <li><a href="contact.php" class="text-muted">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white">Ikuti Kami</h5>
                    <div class="social-links mt-2">
                        <a href="#" class="text-muted mr-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-muted mr-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-muted mr-3"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
    .footer {
        position: relative;
        bottom: 0;
        width: calc(100% - 250px);
        z-index: 1000;
    }

    #page-wrapper {
        min-height: calc(100vh - 200px);
        padding-bottom: 60px;
    }

    @media(max-width: 768px) {
        .footer {
            margin-left: 0;
            width: 100%;
        }
    }
    </style>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Core JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Theme JavaScript -->
    <script>
    $(document).ready(function() {
        // Preview gambar saat dipilih
        $('input[name="gambar"]').change(function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('.img-preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Konfirmasi sebelum submit
        // $('form').submit(function(e) {
        //     if(!confirm('Apakah Anda yakin ingin menyimpan data ini?')) {
        //         e.preventDefault();
        //     }
        // });
    });
    </script>
</body>
</html> 