<?php
require_once __DIR__ . '/../../controllers/BukuController.php';
$bukuController = new BukuController();
extract($bukuController->formState(), EXTR_SKIP);

if (!function_exists('eTambahBuku')) {
    function eTambahBuku($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<!-- Navigasi kembali dari form Tambah Buku ke Data Buku -->
<div class="breadcrumb-bar">
    <a href="?menu=databuku" class="breadcrumb-back-btn">
        <i class="bi bi-chevron-left"></i>
    </a>
    <span class="breadcrumb-title">Tambah Buku</span>
</div>

<!-- Tampilan form tambah buku baru -->
<main class="tambah-content">
    <?php if (!empty($errorsTambahBuku)): ?>
        <div class="form-alert">
            <?= eTambahBuku(implode(' ', $errorsTambahBuku)); ?>
        </div>
    <?php endif; ?>

    <!-- Form create buku, proses submit ditangani create.php -->
    <form method="POST" action="actions/buku/store.php" enctype="multipart/form-data">
        <div class="panel-card mb-3">
            <div class="panel-title">Input Data Buku</div>

            <label class="form-label-custom" for="judul">Judul Buku</label>
            <input class="form-input-custom" id="judul" name="judul" type="text" placeholder="Masukkan judul buku..." value="<?= eTambahBuku($oldTambahBuku['judul']); ?>" required>

            <label class="form-label-custom" for="penulis">Penulis</label>
            <input class="form-input-custom" id="penulis" name="penulis" type="text" placeholder="Masukkan nama penulis..." value="<?= eTambahBuku($oldTambahBuku['penulis']); ?>" required>

            <label class="form-label-custom" for="penerbit">Penerbit</label>
            <input class="form-input-custom" id="penerbit" name="penerbit" type="text" placeholder="Masukkan nama penerbit..." value="<?= eTambahBuku($oldTambahBuku['penerbit']); ?>" required>

            <label class="form-label-custom" for="tahun">Tahun Terbit</label>
            <input class="form-input-custom" id="tahun" name="tahun" type="number" min="0" placeholder="cth: 2023" value="<?= eTambahBuku($oldTambahBuku['tahun']); ?>" required>

            <label class="form-label-custom" for="tempat_terbit">Tempat Terbit</label>
            <input class="form-input-custom" id="tempat_terbit" name="tempat_terbit" type="text" placeholder="cth: Jakarta" value="<?= eTambahBuku($oldTambahBuku['tempat_terbit']); ?>">

            <label class="form-label-custom" for="isbn">ISBN</label>
            <input class="form-input-custom" id="isbn" name="isbn" type="text" placeholder="cth: 978-3-16-148410-0" value="<?= eTambahBuku($oldTambahBuku['isbn']); ?>">

            <span class="kategori-label">Kategori :</span>
            <div class="kategori-grid">
                <?php foreach ($kategoriList as $kategori): ?>
                    <label class="kategori-item">
                        <input
                            type="checkbox"
                            class="kategori-checkbox"
                            name="kategori[]"
                            value="<?= eTambahBuku($kategori); ?>"
                            <?= (string) ($oldTambahBuku['kategori'] ?? '') === $kategori ? 'checked' : ''; ?>
                        >
                        <span class="kategori-text"><?= eTambahBuku($kategori); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <span class="cover-section-title">Input Cover Buku</span>
            <label class="cover-upload-area" for="cover">
                <span class="cover-upload-hint">Klik untuk upload cover</span>
            </label>
            <input type="file" id="cover" name="cover" accept="image/*" class="cover-file-input">

            <label class="synopsis-label" for="sinopsis">Deskripsi/Sinopsis :</label>
            <textarea class="synopsis-area" id="sinopsis" name="sinopsis" placeholder="Masukkan deskripsi atau sinopsis buku..."><?= eTambahBuku($oldTambahBuku['sinopsis']); ?></textarea>

            <button type="submit" class="btn-tambahkan">Tambahkan</button>
        </div>

        <div class="panel-card">
            <div class="copy-panel-title">Copy Buku</div>
            <label class="form-label-custom" for="stok">Jumlah Copy / Stok</label>
            <input class="form-input-custom stock-input" id="stok" name="stok" type="number" min="1" value="<?= (int) $oldTambahBuku['stok']; ?>" required>
            <div class="copy-list-row" id="copyPreview" aria-live="polite"></div>
        </div>
    </form>
</main>

<!-- Script preview jumlah copy/stok buku -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stokInput = document.getElementById('stok');
    const copyPreview = document.getElementById('copyPreview');

    // Menampilkan preview badge copy berdasarkan input stok.
    function renderCopyPreview() {
        if (!stokInput || !copyPreview) {
            return;
        }

        const total = Math.max(1, parseInt(stokInput.value || '1', 10));
        const visible = Math.min(total, 12);
        copyPreview.innerHTML = '';

        for (let index = 1; index <= visible; index++) {
            const badge = document.createElement('span');
            badge.className = 'copy-badge';
            badge.textContent = 'b' + String(index).padStart(3, '0');
            copyPreview.appendChild(badge);
        }

        if (total > visible) {
            const badge = document.createElement('span');
            badge.className = 'copy-badge';
            badge.textContent = '+' + (total - visible);
            copyPreview.appendChild(badge);
        }
    }

    if (stokInput) {
        stokInput.addEventListener('input', renderCopyPreview);
        renderCopyPreview();
    }
});
</script>
