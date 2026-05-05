<?php

require_once __DIR__ . '/../../config/database.php';

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

$conn = (new Database())->getConnection();
$adminId = (int) ($_SESSION['id_admin'] ?? $_SESSION['id_user'] ?? 0);

if ($adminId < 1) {
    $result = $conn->query('SELECT id_admin FROM admin ORDER BY id_admin ASC LIMIT 1');
    $adminId = (int) (($result->fetch_assoc()['id_admin'] ?? 0));
}

$pesan = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['aksi'] ?? '') === 'edit' && $adminId > 0) {
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $noHp = trim($_POST['no_hp'] ?? '');

    if ($nama !== '' && $email !== '') {
        $stmt = $conn->prepare(
            'UPDATE admin
             SET nama_admin = ?, jabatan_admin = ?, email_admin = ?, no_hp_admin = ?
             WHERE id_admin = ?'
        );
        $stmt->bind_param('ssssi', $nama, $jabatan, $email, $noHp, $adminId);
        $stmt->execute();

        $_SESSION['nama'] = $nama;
        $_SESSION['jabatan'] = $jabatan !== '' ? $jabatan : 'Admin Perpustakaan';
        $pesan = 'success';
    }
}

$stmt = $conn->prepare('SELECT * FROM admin WHERE id_admin = ? LIMIT 1');
$stmt->bind_param('i', $adminId);
$stmt->execute();
$adminRow = $stmt->get_result()->fetch_assoc() ?: [];

$admin = [
    'id' => $adminRow['id_admin'] ?? 'ADM001',
    'nama' => $adminRow['nama_admin'] ?? 'Administrator',
    'jabatan' => $adminRow['jabatan_admin'] ?? 'Admin Perpustakaan',
    'email' => $adminRow['email_admin'] ?? 'admin@perpustakaan.com',
    'no_hp' => $adminRow['no_hp_admin'] ?? '-',
    'last_login' => $adminRow['last_login_at'] ?? '',
];

$last_login = $admin['last_login'] !== ''
    ? date('d/m/Y H:i', strtotime((string) $admin['last_login']))
    : '-';
$mode = isset($_GET['edit']) ? 'edit' : 'view';
?>

<div class="akun-wrap">

    <?php if ($pesan === 'success'): ?>
    <div class="alert-success">
        <i class="bi bi-check-circle-fill"></i> Profil berhasil diperbarui.
    </div>
    <?php endif; ?>

    <div class="profile-card">

        <div class="profile-card-title">Profil Admin</div>

        <div class="identity-row">
            <div class="avatar-box">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="identity-info">
                <div class="identity-row-item">
                    <span class="identity-key">NAMA</span>
                    <span class="identity-val"><?= htmlspecialchars((string) $admin['nama']) ?></span>
                </div>
                <div class="identity-row-item">
                    <span class="identity-key">ID</span>
                    <span class="identity-val-sm"><?= htmlspecialchars('ADM' . str_pad((string) $admin['id'], 3, '0', STR_PAD_LEFT)) ?></span>
                </div>
                <div class="identity-row-item">
                    <span class="identity-key">JABATAN</span>
                    <span class="badge-jabatan"><?= htmlspecialchars((string) $admin['jabatan']) ?></span>
                </div>
            </div>
        </div>

        <div class="card-divider"></div>

        <?php if ($mode === 'edit'): ?>
        <form method="POST" action="?menu=akun" class="edit-form">
            <input type="hidden" name="aksi" value="edit">

            <div class="form-field">
                <label class="form-label"><i class="bi bi-person"></i> Nama</label>
                <input type="text" name="nama" class="form-input"
                       value="<?= htmlspecialchars((string) $admin['nama']) ?>" required>
            </div>

            <div class="form-field">
                <label class="form-label"><i class="bi bi-briefcase"></i> Jabatan</label>
                <input type="text" name="jabatan" class="form-input"
                       value="<?= htmlspecialchars((string) $admin['jabatan']) ?>">
            </div>

            <div class="form-field">
                <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                <input type="email" name="email" class="form-input"
                       value="<?= htmlspecialchars((string) $admin['email']) ?>" required>
            </div>

            <div class="form-field" style="margin-bottom:1.75rem;">
                <label class="form-label"><i class="bi bi-telephone"></i> No HP</label>
                <input type="text" name="no_hp" class="form-input"
                       value="<?= htmlspecialchars((string) $admin['no_hp']) ?>">
            </div>

            <button type="submit" class="btn-edit-profil">
                <i class="bi bi-check-lg"></i> Simpan Perubahan
            </button>
            <a href="?menu=akun" class="btn-logout" style="text-decoration:none; display:flex; align-items:center; justify-content:center; gap:.5rem;">
                <i class="bi bi-x-lg"></i> Batal
            </a>
        </form>

        <?php else: ?>
        <div class="info-field">
            <div class="info-field-label"><i class="bi bi-envelope"></i> Email</div>
            <div class="info-field-value"><?= htmlspecialchars((string) $admin['email']) ?></div>
        </div>

        <div class="info-field">
            <div class="info-field-label"><i class="bi bi-telephone"></i> No HP</div>
            <div class="info-field-value"><?= htmlspecialchars((string) ($admin['no_hp'] ?: '-')) ?></div>
        </div>

        <div class="info-field" style="margin-bottom:1.75rem;">
            <div class="info-field-label"><i class="bi bi-clock-history"></i> Last Login</div>
            <div class="info-field-value"><?= htmlspecialchars($last_login) ?></div>
        </div>

        <a href="?menu=akun&edit=1" class="btn-edit-profil"
           style="text-decoration:none; display:flex; align-items:center; justify-content:center; gap:.5rem;">
            <i class="bi bi-person-gear"></i> Edit Profil
        </a>

        <a href="?menu=akun&logout=1" class="btn-logout"
           style="text-decoration:none; display:flex; align-items:center; justify-content:center; gap:.5rem;"
           onclick="return confirm('Yakin ingin logout?')">
            <i class="bi bi-box-arrow-right"></i> Log Out
        </a>
        <?php endif; ?>

    </div>
</div>
