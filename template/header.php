<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Perpustakaan Digital</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="catalog.php">Katalog</a>
                </li>
                <?php if(isset($_SESSION['level']) && $_SESSION['level'] == 'admin'): ?>
                    <!-- Menu admin yang sudah ada -->
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-toggle="dropdown">
                            <?php echo $_SESSION['username']; ?>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="profile.php">Profil</a>
                            <a class="dropdown-item" href="history.php">Riwayat Baca</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>