<?php
require_once __DIR__ . '/../../controllers/BukuController.php';
$bukuController = new BukuController();
extract($bukuController->index(), EXTR_SKIP);
?>

<!-- Tampilan utama tabel Data Buku -->
<section class="databuku-page">
    <div class="databuku-header">
        <h1>Data Buku</h1>
    </div>

    <!-- Toolbar tambah, pencarian, dan filter buku -->
    <div class="databuku-toolbar">
        <a href="?menu=tambahbuku" class="btn-add-book">
            <i class="bi bi-plus"></i>
            <span>Tambah Buku</span>
        </a>

        <form method="get" class="databuku-search">
            <input type="hidden" name="menu" value="databuku">
            <input type="hidden" name="kategori" value="<?= eBuku($kategoriFilter); ?>">
            <input type="hidden" name="per_page" value="<?= (int) $perPage; ?>">
            <input type="text" name="q" placeholder="Cari Buku..." value="<?= eBuku($search); ?>">
            <button type="submit" aria-label="Cari buku">
                <i class="bi bi-search"></i>
            </button>
        </form>

        <form method="get" class="databuku-filter" id="filterBukuForm">
            <input type="hidden" name="menu" value="databuku">
            <input type="hidden" name="q" value="<?= eBuku($search); ?>">
            <input type="hidden" name="per_page" value="<?= (int) $perPage; ?>">
            <select name="kategori" aria-label="Filter kategori buku">
                <option value="Semua" <?= $kategoriFilter === 'Semua' ? 'selected' : ''; ?>>Filter</option>
                <?php foreach ($kategoriOptions as $kategori): ?>
                    <option value="<?= eBuku($kategori); ?>" <?= $kategoriFilter === $kategori ? 'selected' : ''; ?>>
                        <?= eBuku($kategori); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <i class="bi bi-chevron-down"></i>
        </form>
    </div>

    <!-- Tabel daftar buku dan tombol aksi -->
    <div class="databuku-table-wrap">
        <table class="databuku-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th>Penerbit</th>
                    <th>Tahun</th>
                    <th>Kategori :</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pageData)): ?>
                    <tr>
                        <td colspan="8" class="databuku-empty">Data buku tidak ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pageData as $index => $book): ?>
                        <tr>
                            <td><?= (int) ($offset + $index + 1); ?>.</td>
                            <td><?= eBuku($book['judul']); ?></td>
                            <td><?= eBuku($book['penulis']); ?></td>
                            <td><?= eBuku($book['penerbit']); ?></td>
                            <td><?= eBuku($book['tahun']); ?></td>
                            <td><?= eBuku($book['kategori']); ?></td>
                            <?php
                            $dipinjam = (int) ($book['dipinjam'] ?? 0);
                            $stokTersedia = (int) ($book['stok_tersedia'] ?? 0);
                            $bookPayload = $book;
                            $bookPayload['stok_tersedia'] = $stokTersedia;
                            $bookPayload['dipinjam'] = $dipinjam;
                            ?>
                            <td><?= eBuku($stokTersedia); ?></td>
                            <td class="databuku-actions">
                                <button
                                    type="button"
                                    class="btn-table-action js-edit-book"
                                    data-book='<?= eBuku(json_encode($bookPayload, JSON_UNESCAPED_UNICODE)); ?>'
                                >
                                    <i class="bi bi-pencil"></i>
                                    <span>Edit</span>
                                </button>
                                <button
                                    type="button"
                                    class="btn-table-action js-detail-book"
                                    data-book='<?= eBuku(json_encode($bookPayload, JSON_UNESCAPED_UNICODE)); ?>'
                                >
                                    <i class="bi bi-search"></i>
                                    <span>Detail</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="databuku-footer">
        <div class="table-footer-info">
            <span>Menampilkan <?= (int) $startDisplay; ?>-<?= (int) $endDisplay; ?> dari <?= (int) $totalData; ?> data</span>
            <form method="get" class="per-page-form">
                <input type="hidden" name="menu" value="databuku">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="q" value="<?= eBuku($search); ?>">
                <input type="hidden" name="kategori" value="<?= eBuku($kategoriFilter); ?>">
                <label>
                    <span>Tampilkan</span>
                    <select name="per_page" onchange="this.form.submit()">
                        <?php foreach (getBukuPerPageOptions() as $option): ?>
                            <option value="<?= (int) $option; ?>" <?= $perPage === $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span>data</span>
                </label>
            </form>
        </div>
        <div class="databuku-pagination">
            <a class="page-arrow <?= $currentPage <= 1 ? 'disabled' : ''; ?>" href="<?= $currentPage <= 1 ? '#' : eBuku(buildBukuUrl($currentPage - 1, $search, $kategoriFilter, $perPage)); ?>">
                <i class="bi bi-chevron-left"></i>
            </a>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i <= 2 || $i === $totalPages || abs($i - $currentPage) <= 1): ?>
                    <a class="page-number <?= $i === $currentPage ? 'active' : ''; ?>" href="<?= eBuku(buildBukuUrl($i, $search, $kategoriFilter, $perPage)); ?>"><?= (int) $i; ?></a>
                <?php elseif ($i === 3): ?>
                    <span class="page-dots">...</span>
                <?php endif; ?>
            <?php endfor; ?>

            <a class="page-arrow <?= $currentPage >= $totalPages ? 'disabled' : ''; ?>" href="<?= $currentPage >= $totalPages ? '#' : eBuku(buildBukuUrl($currentPage + 1, $search, $kategoriFilter, $perPage)); ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Modal detail buku -->
<div class="book-modal" id="detailBookModal" aria-hidden="true">
    <div class="book-modal-box detail-modal-box">
        <div class="book-modal-header">
            <h2>Detail Buku</h2>
            <button type="button" class="book-modal-close js-close-book-modal" aria-label="Tutup">&times;</button>
        </div>
        <div class="book-detail-list">
            <div><span>Judul Buku</span><strong id="detailJudul">-</strong></div>
            <div><span>Penulis</span><strong id="detailPenulis">-</strong></div>
            <div><span>Penerbit</span><strong id="detailPenerbit">-</strong></div>
            <div><span>Tahun</span><strong id="detailTahun">-</strong></div>
            <div><span>Kategori :</span><strong id="detailKategori">-</strong></div>
            <div><span>Stok Total</span><strong id="detailStok">-</strong></div>
            <div><span>Dipinjam</span><strong id="detailDipinjam">-</strong></div>
            <div><span>Tersedia</span><strong id="detailTersedia">-</strong></div>
        </div>
    </div>
</div>

<!-- Modal edit buku -->
<div class="book-modal" id="editBookModal" aria-hidden="true">
    <div class="book-modal-box">
        <div class="book-modal-header">
            <h2>Edit Data Buku</h2>
            <button type="button" class="book-modal-close js-close-book-modal" aria-label="Tutup">&times;</button>
        </div>
        <form method="post" action="actions/buku/update.php" class="book-edit-form">
            <input type="hidden" name="action" value="edit_buku">
            <input type="hidden" name="id" id="editId">
            <input type="hidden" name="page" value="<?= (int) $currentPage; ?>">
            <input type="hidden" name="q" value="<?= eBuku($search); ?>">
            <input type="hidden" name="kategori_filter" value="<?= eBuku($kategoriFilter); ?>">
            <input type="hidden" name="per_page" value="<?= (int) $perPage; ?>">

            <label>Judul Buku</label>
            <input type="text" name="judul" id="editJudul" required>

            <label>Penulis</label>
            <input type="text" name="penulis" id="editPenulis" required>

            <label>Penerbit</label>
            <input type="text" name="penerbit" id="editPenerbit" required>

            <div class="book-edit-grid">
                <div>
                    <label>Tahun</label>
                    <input type="number" name="tahun" id="editTahun" min="0" required>
                </div>
                <div>
                    <label>Stok</label>
                    <input type="number" name="stok" id="editStok" min="0" required>
                </div>
            </div>

            <label>Kategori :</label>
            <select name="kategori" id="editKategori" required>
                <?php foreach ($kategoriOptions as $kategori): ?>
                    <option value="<?= eBuku($kategori); ?>"><?= eBuku($kategori); ?></option>
                <?php endforeach; ?>
            </select>

            <div class="book-modal-actions">
                <button type="button" class="btn-cancel-edit js-close-book-modal">Batal</button>
                <button type="submit" class="btn-save-edit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Script modal detail/edit dan filter Data Buku -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterBukuForm');
    const filterSelect = filterForm ? filterForm.querySelector('select') : null;
    const detailModal = document.getElementById('detailBookModal');
    const editModal = document.getElementById('editBookModal');

    if (filterSelect) {
        filterSelect.addEventListener('change', function () {
            filterForm.submit();
        });
    }

    // Membuka modal detail atau edit buku.
    function openModal(modal) {
        if (!modal) return;
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }

    // Menutup semua modal buku yang sedang aktif.
    function closeModals() {
        document.querySelectorAll('.book-modal.show').forEach(function (modal) {
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        });
        document.body.classList.remove('modal-open');
    }

    // Mengambil payload JSON buku dari tombol aksi.
    function parseBook(button) {
        try {
            return JSON.parse(button.dataset.book || '{}');
        } catch (error) {
            return {};
        }
    }

    document.querySelectorAll('.js-detail-book').forEach(function (button) {
        button.addEventListener('click', function () {
            const book = parseBook(button);
            document.getElementById('detailJudul').textContent = book.judul || '-';
            document.getElementById('detailPenulis').textContent = book.penulis || '-';
            document.getElementById('detailPenerbit').textContent = book.penerbit || '-';
            document.getElementById('detailTahun').textContent = book.tahun || '-';
            document.getElementById('detailKategori').textContent = book.kategori || '-';
            document.getElementById('detailStok').textContent = book.stok || '-';
            document.getElementById('detailDipinjam').textContent = book.dipinjam || '0';
            document.getElementById('detailTersedia').textContent = book.stok_tersedia || '0';
            openModal(detailModal);
        });
    });

    document.querySelectorAll('.js-edit-book').forEach(function (button) {
        button.addEventListener('click', function () {
            const book = parseBook(button);
            document.getElementById('editId').value = book.id || '';
            document.getElementById('editJudul').value = book.judul || '';
            document.getElementById('editPenulis').value = book.penulis || '';
            document.getElementById('editPenerbit').value = book.penerbit || '';
            document.getElementById('editTahun').value = book.tahun || '';
            document.getElementById('editStok').value = book.stok || '';
            document.getElementById('editKategori').value = book.kategori || '';
            openModal(editModal);
        });
    });

    document.querySelectorAll('.js-close-book-modal').forEach(function (button) {
        button.addEventListener('click', closeModals);
    });

    document.querySelectorAll('.book-modal').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModals();
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModals();
        }
    });
});
</script>
