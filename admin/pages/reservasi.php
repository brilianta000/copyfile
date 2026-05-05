<?php
require_once __DIR__ . '/../../controllers/ReservasiController.php';
extract(ReservasiController::index(), EXTR_SKIP);

function eR($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmtTgl($d): string {
    if (empty($d)) return '-';
    $ts = strtotime((string)$d);
    return $ts ? date('d M Y', $ts) : '-';
}

$statusLabel = [
    'pending'   => ['label' => 'Menunggu',   'class' => 'badge-pending'],
    'confirmed' => ['label' => 'Dikonfirmasi','class' => 'badge-confirmed'],
    'cancelled' => ['label' => 'Dibatalkan', 'class' => 'badge-cancelled'],
    'expired'   => ['label' => 'Kadaluarsa', 'class' => 'badge-expired'],
];
?>

<div class="reservasi-wrapper">
    <!-- Header -->
    <div class="reservasi-header">
        <div class="title-group">
            <h1>Reservasi Buku</h1>
            <span class="total-badge"><?= $totalData; ?> data</span>
        </div>
    </div>

    <!-- Toolbar: search + filter status + per page -->
    <div class="toolbar">
        <div class="toolbar-left">
            <form method="get" class="search-form">
                <input type="hidden" name="menu" value="reservasi">
                <input type="hidden" name="per_page" value="<?= (int)$perPage; ?>">
                <?php if ($filterStatus !== ''): ?>
                    <input type="hidden" name="status" value="<?= eR($filterStatus); ?>">
                <?php endif; ?>
                <input
                    type="search"
                    name="q"
                    value="<?= eR($search); ?>"
                    placeholder="Cari kode, nama, atau judul buku..."
                    class="search-input"
                    autocomplete="off"
                >
                <button type="submit" class="btn-search">Cari</button>
            </form>

            <!-- Filter status -->
            <div class="filter-group">
                <?php
                $statusOptions = [
                    ''          => 'Semua',
                    'pending'   => 'Menunggu',
                    'confirmed' => 'Dikonfirmasi',
                    'cancelled' => 'Dibatalkan',
                    'expired'   => 'Kadaluarsa',
                ];
                foreach ($statusOptions as $val => $lbl):
                    $active = ($filterStatus === $val) ? 'active' : '';
                ?>
                    <a href="?menu=reservasi&status=<?= eR($val); ?>&q=<?= eR($search); ?>&per_page=<?= (int)$perPage; ?>"
                       class="filter-chip <?= $active; ?>">
                        <?= eR($lbl); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="toolbar-right">
            <form method="get" class="per-page-form">
                <input type="hidden" name="menu" value="reservasi">
                <input type="hidden" name="q" value="<?= eR($search); ?>">
                <input type="hidden" name="status" value="<?= eR($filterStatus); ?>">
                <label for="per_page_reservasi">Tampilkan</label>
                <select name="per_page" id="per_page_reservasi" onchange="this.form.submit()">
                    <?php foreach (Reservasi::perPageOptions() as $opt): ?>
                        <option value="<?= $opt; ?>" <?= $perPage === $opt ? 'selected' : ''; ?>>
                            <?= $opt; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span>data</span>
            </form>
        </div>
    </div>

    <!-- Tabel -->
    <div class="table-wrapper">
        <table class="reservasi-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Anggota</th>
                    <th>Judul Buku</th>
                    <th>Tgl Reservasi</th>
                    <th>Kadaluarsa</th>
                    <th>Status</th>
                    <th>Admin</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($pageData)): ?>
                <?php foreach ($pageData as $i => $r): ?>
                    <?php
                    $st    = $r['status_reservasi'] ?? 'pending';
                    $badge = $statusLabel[$st] ?? ['label' => $st, 'class' => 'badge-pending'];
                    ?>
                    <tr>
                        <td><?= $startDisplay + $i; ?></td>
                        <td><?= eR($r['kode_anggota'] ?? ''); ?></td>
                        <td><?= eR($r['nama_anggota'] ?? ''); ?></td>
                        <td><?= eR($r['judul_buku'] ?? ''); ?></td>
                        <td><?= fmtTgl($r['tanggal_reservasi'] ?? ''); ?></td>
                        <td><?= fmtTgl($r['tanggal_kadaluarsa'] ?? ''); ?></td>
                        <td>
                            <span class="status-badge <?= eR($badge['class']); ?>">
                                <?= eR($badge['label']); ?>
                            </span>
                        </td>
                        <td><?= eR($r['nama_admin'] ?? '-'); ?></td>
                        <td>
                            <div class="action-group">
                                <?php if ($st === 'pending'): ?>
                                    <button
                                        type="button"
                                        class="btn-konfirmasi js-open-konfirmasi-modal"
                                        data-id="<?= (int)$r['id_reservasi']; ?>"
                                        data-nama="<?= eR($r['nama_anggota'] ?? ''); ?>"
                                        data-buku="<?= eR($r['judul_buku'] ?? ''); ?>"
                                    >
                                        Konfirmasi
                                    </button>
                                    <button
                                        type="button"
                                        class="btn-batal js-open-batalkan-modal"
                                        data-id="<?= (int)$r['id_reservasi']; ?>"
                                        data-nama="<?= eR($r['nama_anggota'] ?? ''); ?>"
                                        data-buku="<?= eR($r['judul_buku'] ?? ''); ?>"
                                    >
                                        Batalkan
                                    </button>
                                <?php elseif ($st === 'confirmed'): ?>
                                    <button
                                        type="button"
                                        class="btn-batal js-open-batalkan-modal"
                                        data-id="<?= (int)$r['id_reservasi']; ?>"
                                        data-nama="<?= eR($r['nama_anggota'] ?? ''); ?>"
                                        data-buku="<?= eR($r['judul_buku'] ?? ''); ?>"
                                    >
                                        Batalkan
                                    </button>
                                <?php else: ?>
                                    <span class="empty-action">-</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="empty-row">
                        <?= $search !== '' || $filterStatus !== ''
                            ? 'Tidak ada data yang cocok dengan filter.'
                            : 'Belum ada data reservasi.'; ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Info + pagination -->
    <div class="table-footer">
        <span class="table-info">
            <?php if ($totalData > 0): ?>
                Menampilkan <?= $startDisplay; ?>–<?= $endDisplay; ?> dari <?= $totalData; ?> data
            <?php else: ?>
                Tidak ada data
            <?php endif; ?>
        </span>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?menu=reservasi&page=<?= $currentPage - 1; ?>&per_page=<?= $perPage; ?>&q=<?= eR($search); ?>&status=<?= eR($filterStatus); ?>"
                       class="page-btn">&laquo;</a>
                <?php endif; ?>

                <?php foreach ($paginationItems as $pg): ?>
                    <?php if ($pg === '...'): ?>
                        <span class="page-dots">...</span>
                    <?php else: ?>
                        <a href="?menu=reservasi&page=<?= $pg; ?>&per_page=<?= $perPage; ?>&q=<?= eR($search); ?>&status=<?= eR($filterStatus); ?>"
                           class="page-btn <?= $pg === $currentPage ? 'active' : ''; ?>">
                            <?= $pg; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?menu=reservasi&page=<?= $currentPage + 1; ?>&per_page=<?= $perPage; ?>&q=<?= eR($search); ?>&status=<?= eR($filterStatus); ?>"
                       class="page-btn">&raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== MODAL KONFIRMASI ===== -->
<div class="modal-overlay" id="modalKonfirmasi">
    <div class="modal-box">
        <div class="modal-header">
            <span>Konfirmasi Reservasi</span>
            <button type="button" class="modal-close" id="closeKonfirmasiModal">&times;</button>
        </div>
        <div class="modal-body">
            <p>Konfirmasi reservasi buku <strong id="konfirmasi-buku"></strong> untuk anggota
                <strong id="konfirmasi-nama"></strong>?
            </p>
        </div>
        <form method="post" action="actions/reservasi/konfirmasi.php">
            <input type="hidden" name="id" id="konfirmasi-id">
            <input type="hidden" name="page" value="<?= (int)$currentPage; ?>">
            <input type="hidden" name="per_page" value="<?= (int)$perPage; ?>">
            <input type="hidden" name="q" value="<?= eR($search); ?>">
            <input type="hidden" name="status" value="<?= eR($filterStatus); ?>">
            <div class="modal-footer">
                <button type="button" class="btn-cancel-modal" id="cancelKonfirmasiModal">Batal</button>
                <button type="submit" class="btn-confirm-modal btn-green">Ya, Konfirmasi</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL BATALKAN ===== -->
<div class="modal-overlay" id="modalBatalkan">
    <div class="modal-box">
        <div class="modal-header">
            <span>Batalkan Reservasi</span>
            <button type="button" class="modal-close" id="closeBatalkanModal">&times;</button>
        </div>
        <div class="modal-body">
            <p>Batalkan reservasi buku <strong id="batalkan-buku"></strong> untuk anggota
                <strong id="batalkan-nama"></strong>?
            </p>
        </div>
        <form method="post" action="actions/reservasi/batalkan.php">
            <input type="hidden" name="id" id="batalkan-id">
            <input type="hidden" name="page" value="<?= (int)$currentPage; ?>">
            <input type="hidden" name="per_page" value="<?= (int)$perPage; ?>">
            <input type="hidden" name="q" value="<?= eR($search); ?>">
            <input type="hidden" name="status" value="<?= eR($filterStatus); ?>">
            <div class="modal-footer">
                <button type="button" class="btn-cancel-modal" id="cancelBatalkanModal">Batal</button>
                <button type="submit" class="btn-confirm-modal btn-red">Ya, Batalkan</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    // Modal Konfirmasi
    const modalKonfirmasi   = document.getElementById('modalKonfirmasi');
    const konfirmasiButtons = document.querySelectorAll('.js-open-konfirmasi-modal');

    konfirmasiButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('konfirmasi-id').value   = btn.dataset.id;
            document.getElementById('konfirmasi-nama').textContent = btn.dataset.nama;
            document.getElementById('konfirmasi-buku').textContent = btn.dataset.buku;
            modalKonfirmasi.classList.add('active');
        });
    });

    ['closeKonfirmasiModal', 'cancelKonfirmasiModal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', () => {
            modalKonfirmasi.classList.remove('active');
        });
    });

    modalKonfirmasi.addEventListener('click', e => {
        if (e.target === modalKonfirmasi) modalKonfirmasi.classList.remove('active');
    });

    // Modal Batalkan
    const modalBatalkan   = document.getElementById('modalBatalkan');
    const batalkanButtons = document.querySelectorAll('.js-open-batalkan-modal');

    batalkanButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('batalkan-id').value   = btn.dataset.id;
            document.getElementById('batalkan-nama').textContent = btn.dataset.nama;
            document.getElementById('batalkan-buku').textContent = btn.dataset.buku;
            modalBatalkan.classList.add('active');
        });
    });

    ['closeBatalkanModal', 'cancelBatalkanModal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', () => {
            modalBatalkan.classList.remove('active');
        });
    });

    modalBatalkan.addEventListener('click', e => {
        if (e.target === modalBatalkan) modalBatalkan.classList.remove('active');
    });
})();
</script>
