<!-- ===================== SIDEBAR NAVIGASI ADMIN ===================== -->
<aside class="sidebar">
    <!-- Brand/logo sidebar admin -->
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <img src="../logo_polije.png" alt="Logo Polije">
        </div>
        <span class="sidebar-brand-text">ADMIN</span>
    </div>

    <!-- Link menu utama admin -->
    <nav class="sidebar-nav">
        <a href="?menu=dashboard" class="sidebar-item <?= ($currentMenu === 'dashboard') ? 'active' : ''; ?>">
            <span>Dashboard</span>
        </a>

        <div class="sidebar-divider"></div>

        <a href="?menu=peminjaman" class="sidebar-subitem <?= ($currentMenu === 'peminjaman') ? 'active' : ''; ?>">
            <i class="bi bi-check-circle"></i>
            <span>PEMINJAMAN</span>
        </a>

        <a href="?menu=databuku" class="sidebar-subitem <?= in_array($currentMenu, ['databuku', 'tambahbuku'], true) ? 'active' : ''; ?>">
            <i class="bi bi-book"></i>
            <span>DATA BUKU</span>
        </a>

        <a href="?menu=laporan" class="sidebar-subitem <?= ($currentMenu === 'laporan') ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span>LAPORAN</span>
        </a>

        <a href="?menu=reservasi" class="sidebar-subitem <?= ($currentMenu === 'reservasi') ? 'active' : ''; ?>">
            <i class="bi bi-bookmark-check"></i>
            <span>RESERVASI</span>
        </a>
    </nav>
</aside>
