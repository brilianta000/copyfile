<nav class="header-navbar">
<?php 
// Detect from where this file is being included
$trace = debug_backtrace();
$caller_file = isset($trace[0]['file']) ? $trace[0]['file'] : __FILE__;
$caller_dir = dirname($caller_file);

// Get the last directory name in the path
$parts = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $caller_dir));
$caller_dir_name = end($parts);

// If included from 'user' folder directly
$base = ($caller_dir_name === 'user') ? '' : '../';
?>
    <div class="container">

        <!-- LOGO -->
        <div class="logo-section">
           <img src="<?php echo $base; ?>gambar/logo-polije.png" alt="Logo" class="logo-gambar">
            <span class="brand-name">Perpustakaan Polije</span>
        </div>

        <!-- MENU (TENGAH) -->
        <ul class="nav-menu">

         <li>
        <a href="<?php echo $base; ?>beranda.php" class="nav-link">Beranda</a>
    </li>

            <li class="dropdown">
                <a class="nav-link">
                    Tentang Kami
                    <svg class="chevron" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.3" fill="none"/>
                    </svg>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base; ?>profil.php">Profil</a></li>
                    <li><a href="<?php echo $base; ?>lokasi.php">Lokasi & Jam Kerja</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a class="nav-link">
                    Panduan
                    <svg class="chevron" width="16" height="16">
                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.3" fill="none"/>
                    </svg>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base; ?>tata tertib/tatatertib.php">Tata Tertib</a></li>
                    <li><a href="<?php echo $base; ?>tata cara/tatacara.php">Tata Cara</a></li>
                </ul>
            </li>

            <li>
                <a href="<?php echo $base; ?>koleksi/koleksi.php" class="nav-link">Koleksi</a>
            </li>

        </ul>

        <div class="profile-section">
    <a href="<?php echo $base; ?>../login.php" class="profile-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2">
            <circle cx="12" cy="7" r="4"/>
            <path d="M5 21c0-4 14-4 14 0"/>
        </svg>
    </a>
</div>

    </div>
</nav>