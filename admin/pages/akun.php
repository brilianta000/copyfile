<?php
/*
|--------------------------------------------------------------------------
| BACA & UPDATE DATA ADMIN DARI JSON
|--------------------------------------------------------------------------
*/
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

$adminFile = __DIR__ . '/data_admin.json';

// Baca data admin dari JSON
$admin = file_exists($adminFile)
    ? json_decode(file_get_contents($adminFile), true)
    : [];

// Fallback default jika file belum ada
$admin = array_merge([
    'id'         => 'ADM001',
    'nama'       => 'Administrator',
    'jabatan'    => 'Admin Perpustakaan',
    'email'      => 'admin@polije.ac.id',
    'no_hp'      => '-',
    'last_login' => '',
], $admin ?? []);

// Catat last_login saat pertama buka halaman (jika belum ada di session)
if (empty($_SESSION['last_login'])) {
    $_SESSION['last_login'] = date('d/m/Y H:i');
}
$last_login = $_SESSION['last_login'];

/*
|--------------------------------------------------------------------------
| PROSES EDIT PROFIL (POST)
|--------------------------------------------------------------------------
*/
$pesan = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $admin['nama']    = trim($_POST['nama']    ?? $admin['nama']);
    $admin['jabatan'] = trim($_POST['jabatan'] ?? $admin['jabatan']);
    $admin['email']   = trim($_POST['email']   ?? $admin['email']);
    $admin['no_hp']   = trim($_POST['no_hp']   ?? $admin['no_hp']);

    file_put_contents($adminFile, json_encode($admin, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $_SESSION['nama']    = $admin['nama'];
    $_SESSION['jabatan'] = $admin['jabatan'];
    $pesan = 'success';
}

/*
|--------------------------------------------------------------------------
| PROSES LOGOUT
|--------------------------------------------------------------------------
*/
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../user/beranda.php');
    exit;
}

// Mode tampil: 'view' atau 'edit'
$mode = isset($_GET['edit']) ? 'edit' : 'view';
?>

<div class="akun-wrap">

    <?php if ($pesan === 'success'): ?>
    <div class="alert-success">
        <i class="bi bi-check-circle-fill"></i> Profil berhasil diperbarui.
    </div>
    <?php endif; ?>

    <div class="profile-card">

        <!-- JUDUL -->
        <div class="profile-card-title">Profil Admin</div>

        <!-- IDENTITY -->
        <div class="identity-row">
            <div class="avatar-box">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="identity-info">
                <div class="identity-row-item">
                    <span class="identity-key">NAMA</span>
                    <span class="identity-val"><?= htmlspecialchars($admin['nama']) ?></span>
                </div>
                <div class="identity-row-item">
                    <span class="identity-key">ID</span>
                    <span class="identity-val-sm"><?= htmlspecialchars($admin['id']) ?></span>
                </div>
                <div class="identity-row-item">
                    <span class="identity-key">JABATAN</span>
                    <span class="badge-jabatan"><?= htmlspecialchars($admin['jabatan']) ?></span>
                </div>
            </div>
        </div>

        <div class="card-divider"></div>

        <?php if ($mode === 'edit'): ?>
        <!-- ===== MODE EDIT ===== -->
        <form method="POST" action="admin.php?menu=akun" class="edit-form">
            <input type="hidden" name="aksi" value="edit">

            <div class="form-field">
                <label class="form-label"><i class="bi bi-person"></i> Nama</label>
                <input type="text" name="nama" class="form-input"
                       value="<?= htmlspecialchars($admin['nama']) ?>" required>
            </div>

            <div class="form-field">
                <label class="form-label"><i class="bi bi-briefcase"></i> Jabatan</label>
                <input type="text" name="jabatan" class="form-input"
                       value="<?= htmlspecialchars($admin['jabatan']) ?>" required>
            </div>

            <div class="form-field">
                <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                <input type="email" name="email" class="form-input"
                       value="<?= htmlspecialchars($admin['email']) ?>" required>
            </div>

            <div class="form-field" style="margin-bottom:1.75rem;">
                <label class="form-label"><i class="bi bi-telephone"></i> No HP</label>
                <input type="text" name="no_hp" class="form-input"
                       value="<?= htmlspecialchars($admin['no_hp']) ?>">
            </div>

            <button type="submit" class="btn-edit-profil">
                <i class="bi bi-check-lg"></i> Simpan Perubahan
            </button>
            <a href="admin.php?menu=akun" class="btn-logout" style="text-decoration:none; display:flex; align-items:center; justify-content:center; gap:.5rem;">
                <i class="bi bi-x-lg"></i> Batal
            </a>
        </form>

        <?php else: ?>
        <!-- ===== MODE VIEW ===== -->
        <div class="info-field">
            <div class="info-field-label"><i class="bi bi-envelope"></i> Email</div>
            <div class="info-field-value"><?= htmlspecialchars($admin['email']) ?></div>
        </div>

        <div class="info-field">
            <div class="info-field-label"><i class="bi bi-telephone"></i> No HP</div>
            <div class="info-field-value"><?= htmlspecialchars($admin['no_hp']) ?></div>
        </div>

        <div class="info-field" style="margin-bottom:1.75rem;">
            <div class="info-field-label"><i class="bi bi-clock-history"></i> Last Login</div>
            <div class="info-field-value"><?= htmlspecialchars($last_login ?: '-') ?></div>
        </div>

        <a href="admin.php?menu=akun&edit=1" class="btn-edit-profil"
           style="text-decoration:none; display:flex; align-items:center; justify-content:center; gap:.5rem;">
            <i class="bi bi-person-gear"></i> Edit Profil
        </a>

        <a href="admin.php?menu=akun&logout=1" class="btn-logout"
           style="text-decoration:none; display:flex; align-items:center; justify-content:center; gap:.5rem;"
           onclick="return confirm('Yakin ingin logout?')">
            <i class="bi bi-box-arrow-right"></i> Log Out
        </a>
        <?php endif; ?>

    </div>
</div>
