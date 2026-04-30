<!-- ===================== SIDEBAR — FIXED ===================== -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
        <img src="../logo_polije.png" width="44" height="44" alt="Logo Polije">
        </div>
        <span class="sidebar-brand-text">ADMIN</span>
    </div>

    <nav class="sidebar-nav">
        <a href="?menu=dashboard" class="sidebar-item <?= ($currentMenu === 'dashboard') ? 'active' : ''; ?>">
            <i></i> Dashboard
        </a>

        <div style="height:0.5rem;"></div>

        <a href="?menu=peminjaman" class="sidebar-subitem <?= ($currentMenu === 'peminjaman') ? 'active' : ''; ?>">

            <i class="bi bi-check-circle "></i> PEMINJAMAN
        </a>

        <a href="?menu=tambahbuku" class="sidebar-subitem <?= ($currentMenu === 'tambahbuku') ? 'active' : ''; ?>">
            <i class="bi bi-book"></i> DATA BUKU
        </a>

        <a href="?menu=laporan" class="sidebar-subitem <?= ($currentMenu === 'laporan') ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-text"></i> LAPORAN
        </a>
    </nav>
</aside>