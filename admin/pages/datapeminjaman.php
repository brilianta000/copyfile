<?php
require_once __DIR__ . '/../../controllers/PeminjamanController.php';
extract(PeminjamanController::index(), EXTR_SKIP);
?>
<!-- Tampilan utama menu Peminjaman -->
<div class="datapeminjam-wrapper">
        <div class="datapeminjam-header">
            <div class="title-group">
                <h1>Data Peminjam</h1>
            </div>
        </div>

        <div class="toolbar">
            <div class="toolbar-left">
                <button type="button" class="btn-tambah" id="openPopupPeminjaman">
                    <span class="plus-icon">+</span>
                    Tambah Peminjaman
                </button>

                <form method="get" class="search-form">
                    <input type="hidden" name="menu" value="<?= e(getCurrentMenuPeminjaman()); ?>">
                    <input type="hidden" name="per_page" value="<?= (int) $perPage; ?>">
                    <div class="search-box">
                        <input type="text" name="q" placeholder="Cari Peminjaman..." value="<?= e($search); ?>">
                        <button type="submit" class="search-submit" aria-label="Cari">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-container">
            <table class="datapeminjam-table">
                <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Terlambat</th>
                    <th>Denda</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($pageData)): ?>
                    <?php foreach ($pageData as $item): ?>
                        <?php
                        $meta = hitungMetaPeminjaman($item);
                        $statusClass = 'status-dipinjam';
                        $tanggalKembaliLama = (string) ($item['tanggal_kembali'] ?? '');
                        $tanggalKembaliBaru = tambahTujuhHariPeminjaman($tanggalKembaliLama);

                        if ($meta['status'] === 'Dikembalikan') {
                            $statusClass = 'status-dikembalikan';
                        } elseif ($meta['status'] === 'Terlambat') {
                            $statusClass = 'status-terlambat';
                        }
                        ?>
                        <tr>
                            <td><?= e($item['nim'] ?? ''); ?></td>
                            <td><?= e($item['nama'] ?? ''); ?></td>
                            <td><?= e($item['buku'] ?? ''); ?></td>
                            <td><?= e(formatTanggal($item['tanggal_pinjam'] ?? '')); ?></td>
                            <td><?= e(formatTanggal($item['tanggal_kembali'] ?? '')); ?></td>
                            <td><?= e($meta['terlambat']); ?></td>
                            <td><?= e($meta['denda']); ?></td>
                            <td>
                                <span class="status-badge <?= e($statusClass); ?>"><?= e($meta['status']); ?></span>
                            </td>
                            <td>
                                <?php if (empty($item['returned_at'])): ?>
                                    <div class="peminjaman-action-group">
                                        <?php if (canPerpanjangPeminjaman($item)): ?>
                                            <button
                                                type="button"
                                                class="btn-perpanjang js-open-extend-modal"
                                                data-id="<?= e($item['id'] ?? ''); ?>"
                                                data-nama="<?= e($item['nama'] ?? ''); ?>"
                                                data-buku="<?= e($item['buku'] ?? ''); ?>"
                                                data-tanggal-lama="<?= e(formatTanggal($tanggalKembaliLama)); ?>"
                                                data-tanggal-baru="<?= e(formatTanggal($tanggalKembaliBaru)); ?>"
                                            >
                                                Perpanjang
                                            </button>
                                        <?php else: ?>
                                            <span class="extend-used-label">Sudah Diperpanjang</span>
                                        <?php endif; ?>

                                        <button
                                            type="button"
                                            class="btn-kembalikan js-open-return-modal"
                                            data-id="<?= e($item['id'] ?? ''); ?>"
                                            data-nama="<?= e($item['nama'] ?? ''); ?>"
                                            data-buku="<?= e($item['buku'] ?? ''); ?>"
                                        >
                                            Kembalikan
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="empty-action">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="empty-state">Data peminjaman tidak ditemukan.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <div class="return-confirm-overlay" id="returnConfirmOverlay">
                <div class="return-confirm-box">
                    <div class="return-confirm-header">
                        <h3>Konfirmasi Pengembalian</h3>
                        <button type="button" class="return-confirm-close" id="returnConfirmClose" aria-label="Tutup">
                            &times;
                        </button>
                    </div>

                    <form method="post" action="actions/peminjaman/delete.php" class="return-confirm-form">
                        <div class="return-confirm-body">
                            <p class="return-confirm-text">Yakin ingin mengembalikan data ini?</p>
                            <div class="return-confirm-detail">
                                <div><strong>Nama:</strong> <span id="returnConfirmNama">-</span></div>
                                <div><strong>Buku:</strong> <span id="returnConfirmBuku">-</span></div>
                            </div>

                            <input type="hidden" name="action" value="kembalikan_peminjaman">
                            <input type="hidden" name="id" id="returnConfirmId">
                            <input type="hidden" name="q" value="<?= e($search); ?>">
                            <input type="hidden" name="page" value="<?= (int) $currentPage; ?>">
                            <input type="hidden" name="per_page" value="<?= (int) $perPage; ?>">
                        </div>

                        <div class="return-confirm-actions">
                            <button type="button" class="btn-return-batal" id="returnConfirmCancel">Batal</button>
                            <button type="submit" class="btn-return-submit">Kembalikan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="return-confirm-overlay" id="extendConfirmOverlay">
                <div class="return-confirm-box">
                    <div class="return-confirm-header">
                        <h3>Konfirmasi Perpanjangan</h3>
                        <button type="button" class="return-confirm-close" id="extendConfirmClose" aria-label="Tutup">
                            &times;
                        </button>
                    </div>

                    <form method="post" action="actions/peminjaman/update.php" class="return-confirm-form">
                        <div class="return-confirm-body">
                            <p class="return-confirm-text">Yakin ingin memperpanjang peminjaman ini selama 7 hari?</p>
                            <div class="return-confirm-detail">
                                <div><strong>Nama:</strong> <span id="extendConfirmNama">-</span></div>
                                <div><strong>Buku:</strong> <span id="extendConfirmBuku">-</span></div>
                                <div><strong>Jatuh Tempo Lama:</strong> <span id="extendConfirmTanggalLama">-</span></div>
                                <div><strong>Jatuh Tempo Baru:</strong> <span id="extendConfirmTanggalBaru">-</span></div>
                            </div>

                            <input type="hidden" name="action" value="perpanjang_peminjaman">
                            <input type="hidden" name="id" id="extendConfirmId">
                            <input type="hidden" name="q" value="<?= e($search); ?>">
                            <input type="hidden" name="page" value="<?= (int) $currentPage; ?>">
                            <input type="hidden" name="per_page" value="<?= (int) $perPage; ?>">
                        </div>

                        <div class="return-confirm-actions">
                            <button type="button" class="btn-return-batal" id="extendConfirmCancel">Batal</button>
                            <button type="submit" class="btn-return-submit">Perpanjang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="datapeminjam-footer">
            <div class="data-count">
                Menampilkan <?= (int) $startDisplay; ?>-<?= (int) $endDisplay; ?> dari <?= (int) $totalData; ?> data
                <form method="get" class="per-page-form">
                    <input type="hidden" name="menu" value="<?= e(getCurrentMenuPeminjaman()); ?>">
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="q" value="<?= e($search); ?>">
                    <label>
                        <span>Tampilkan</span>
                        <select name="per_page" onchange="this.form.submit()">
                            <?php foreach (getPeminjamanPerPageOptions() as $option): ?>
                                <option value="<?= (int) $option; ?>" <?= $perPage === $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span>data</span>
                    </label>
                </form>
            </div>

            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= e(buildPageUrl($currentPage - 1, $search, $perPage)); ?>" class="page-btn">&laquo;</a>
                <?php else: ?>
                    <span class="page-btn disabled">&laquo;</span>
                <?php endif; ?>

                <?php foreach ($paginationItems as $pageItem): ?>
                    <?php if ($pageItem === '...'): ?>
                        <span class="page-btn ellipsis">...</span>
                    <?php elseif ((int) $pageItem === $currentPage): ?>
                        <span class="page-btn active"><?= (int) $pageItem; ?></span>
                    <?php else: ?>
                        <a href="<?= e(buildPageUrl((int) $pageItem, $search, $perPage)); ?>" class="page-btn"><?= (int) $pageItem; ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= e(buildPageUrl($currentPage + 1, $search, $perPage)); ?>" class="page-btn">&raquo;</a>
                <?php else: ?>
                    <span class="page-btn disabled">&raquo;</span>
                <?php endif; ?>
            </div>
        </div>
</div>

<?php include __DIR__ . '/../popuppeminjaman.php'; ?>

<!-- Script popup tambah peminjaman, konfirmasi perpanjangan, dan konfirmasi pengembalian -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const returnOverlay = document.getElementById('returnConfirmOverlay');
    const returnCloseBtn = document.getElementById('returnConfirmClose');
    const returnCancelBtn = document.getElementById('returnConfirmCancel');
    const returnIdInput = document.getElementById('returnConfirmId');
    const returnNamaText = document.getElementById('returnConfirmNama');
    const returnBukuText = document.getElementById('returnConfirmBuku');
    const returnButtons = document.querySelectorAll('.js-open-return-modal');
    const extendOverlay = document.getElementById('extendConfirmOverlay');
    const extendCloseBtn = document.getElementById('extendConfirmClose');
    const extendCancelBtn = document.getElementById('extendConfirmCancel');
    const extendIdInput = document.getElementById('extendConfirmId');
    const extendNamaText = document.getElementById('extendConfirmNama');
    const extendBukuText = document.getElementById('extendConfirmBuku');
    const extendTanggalLamaText = document.getElementById('extendConfirmTanggalLama');
    const extendTanggalBaruText = document.getElementById('extendConfirmTanggalBaru');
    const extendButtons = document.querySelectorAll('.js-open-extend-modal');

    // Membuka popup konfirmasi pengembalian buku.
    function openReturnModal(id, nama, buku) {
        if (!returnOverlay) {
            return;
        }

        returnIdInput.value = id || '';
        returnNamaText.textContent = nama || '-';
        returnBukuText.textContent = buku || '-';
        returnOverlay.classList.add('show');
        document.body.classList.add('modal-open');
    }

    // Menutup popup konfirmasi pengembalian buku.
    function closeReturnModal() {
        if (!returnOverlay) {
            return;
        }

        returnOverlay.classList.remove('show');
        document.body.classList.remove('modal-open');
    }

    // Membuka popup konfirmasi perpanjangan buku.
    function openExtendModal(id, nama, buku, tanggalLama, tanggalBaru) {
        if (!extendOverlay) {
            return;
        }

        extendIdInput.value = id || '';
        extendNamaText.textContent = nama || '-';
        extendBukuText.textContent = buku || '-';
        extendTanggalLamaText.textContent = tanggalLama || '-';
        extendTanggalBaruText.textContent = tanggalBaru || '-';
        extendOverlay.classList.add('show');
        document.body.classList.add('modal-open');
    }

    // Menutup popup konfirmasi perpanjangan buku.
    function closeExtendModal() {
        if (!extendOverlay) {
            return;
        }

        extendOverlay.classList.remove('show');
        document.body.classList.remove('modal-open');
    }

    returnButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openReturnModal(this.dataset.id, this.dataset.nama, this.dataset.buku);
        });
    });

    extendButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openExtendModal(
                this.dataset.id,
                this.dataset.nama,
                this.dataset.buku,
                this.dataset.tanggalLama,
                this.dataset.tanggalBaru
            );
        });
    });

    if (returnCloseBtn) {
        returnCloseBtn.addEventListener('click', closeReturnModal);
    }

    if (returnCancelBtn) {
        returnCancelBtn.addEventListener('click', closeReturnModal);
    }

    if (extendCloseBtn) {
        extendCloseBtn.addEventListener('click', closeExtendModal);
    }

    if (extendCancelBtn) {
        extendCancelBtn.addEventListener('click', closeExtendModal);
    }

    if (returnOverlay) {
        returnOverlay.addEventListener('click', function (event) {
            if (event.target === returnOverlay) {
                closeReturnModal();
            }
        });
    }

    if (extendOverlay) {
        extendOverlay.addEventListener('click', function (event) {
            if (event.target === extendOverlay) {
                closeExtendModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && returnOverlay && returnOverlay.classList.contains('show')) {
            closeReturnModal();
        }

        if (event.key === 'Escape' && extendOverlay && extendOverlay.classList.contains('show')) {
            closeExtendModal();
        }
    });

    const openPopupPeminjaman = document.getElementById('openPopupPeminjaman');
    const popupPeminjaman = document.getElementById('popupPeminjaman');
    const closePopupPeminjaman = document.getElementById('closePopupPeminjaman');
    const batalPopupPeminjaman = document.getElementById('batalPopupPeminjaman');

    // Membuka popup tambah peminjaman baru.
    function bukaPopupPeminjaman() {
        if (popupPeminjaman) {
            popupPeminjaman.classList.add('active');
            document.body.classList.add('modal-open');
        }
    }

    // Menutup popup tambah peminjaman baru.
    function tutupPopupPeminjaman() {
        if (popupPeminjaman) {
            popupPeminjaman.classList.remove('active');
            document.body.classList.remove('modal-open');
        }
    }

    if (openPopupPeminjaman) {
        openPopupPeminjaman.addEventListener('click', bukaPopupPeminjaman);
    }

    if (closePopupPeminjaman) {
        closePopupPeminjaman.addEventListener('click', function (event) {
            event.preventDefault();
            tutupPopupPeminjaman();
        });
    }

    if (batalPopupPeminjaman) {
        batalPopupPeminjaman.addEventListener('click', function (event) {
            event.preventDefault();
            tutupPopupPeminjaman();
        });
    }

    if (popupPeminjaman) {
        popupPeminjaman.addEventListener('click', function (event) {
            if (event.target === popupPeminjaman) {
                tutupPopupPeminjaman();
            }
        });
    }
});
</script>

