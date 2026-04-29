<?php
/*
|--------------------------------------------------------------------------
| BACA DATA DARI JSON
|--------------------------------------------------------------------------
*/
$peminjamanFile  = __DIR__ . '/../data_peminjaman.json';
$laporanFile     = __DIR__ . '/../data_laporan_transaksi.json';

$peminjaman = file_exists($peminjamanFile) ? json_decode(file_get_contents($peminjamanFile), true) : [];
$laporan    = file_exists($laporanFile)    ? json_decode(file_get_contents($laporanFile),    true) : [];

if (!is_array($peminjaman)) $peminjaman = [];
if (!is_array($laporan))    $laporan    = [];

/*
|--------------------------------------------------------------------------
| HITUNG STATISTIK
|--------------------------------------------------------------------------
*/
$today = date('Y-m-d');

// Total judul buku unik dari laporan
$judulList   = array_unique(array_column($laporan, 'judul_buku'));
$total_buku  = count($judulList);

// Sedang dipinjam = Belum Kembali
$dipinjam  = count(array_filter($laporan, fn($r) => $r['status'] === 'Belum Kembali'));

// Terlambat
$terlambat = count(array_filter($laporan, fn($r) => $r['status'] === 'Terlambat'));

// Tersedia
$tersedia  = max(0, $total_buku - $dipinjam - $terlambat);

// Total anggota (NIM unik)
$total_anggota = count(array_unique(array_column($peminjaman, 'nim')));

/*
|--------------------------------------------------------------------------
| LOG TRANSAKSI TERBARU (10 data terakhir)
|--------------------------------------------------------------------------
*/
$logs_sorted = $laporan;
usort($logs_sorted, fn($a, $b) => $b['id'] <=> $a['id']);
$logs_recent = array_slice($logs_sorted, 0, 10);

/*
|--------------------------------------------------------------------------
| DATA GRAFIK — Peminjaman per bulan (6 bulan terakhir)
|--------------------------------------------------------------------------
*/
$bulan_labels = [];
$bulan_data   = [];
for ($i = 5; $i >= 0; $i--) {
    $ts    = strtotime("-$i months");
    $key   = date('Y-m', $ts);
    $label = date('M', $ts);
    $bulan_labels[] = $label;
    $bulan_data[]   = count(array_filter($laporan, fn($r) => str_starts_with($r['tgl_pinjam'], $key)));
}
$chart_max = max(max($bulan_data), 1);

/*
|--------------------------------------------------------------------------
| INFO ADMIN (dari session jika tersedia)
|--------------------------------------------------------------------------
*/
if (session_status() === PHP_SESSION_NONE) session_start();
$admin_nama    = $_SESSION['nama']    ?? 'Administrator';
$admin_jabatan = $_SESSION['jabatan'] ?? 'Admin Perpustakaan';
?>

<link rel="stylesheet" href="../admin/css/dash.css">

<div class="main-content">
    <div class="dash-container">

        <!-- STAT CARDS -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-value"><?= $total_buku ?></span>
                    <span class="stat-label">Total Judul Buku</span>
                </div>
                <div class="stat-icon icon-blue"><i class="bi bi-book-half"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-value"><?= $tersedia ?></span>
                    <span class="stat-label">Tersedia</span>
                </div>
                <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-value"><?= $dipinjam ?></span>
                    <span class="stat-label">Sedang Dipinjam</span>
                </div>
                <div class="stat-icon icon-orange"><i class="bi bi-bookmark-check"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-value"><?= $terlambat ?></span>
                    <span class="stat-label">Terlambat</span>
                </div>
                <div class="stat-icon icon-red"><i class="bi bi-exclamation-circle"></i></div>
            </div>
        </div>

        <!-- BARIS UTAMA -->
        <div class="dash-main">

            <!-- LOG TRANSAKSI TERBARU -->
            <div class="card card-log">
                <div class="card-header-row">
                    <h6 class="card-title">Transaksi Terbaru</h6>
                    <span class="date-badge"><?= date('d M Y') ?></span>
                </div>
                <?php if (empty($logs_recent)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Belum ada transaksi.</p>
                </div>
                <?php else: ?>
                <div class="table-wrap">
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Peminjam</th>
                                <th>Judul Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs_recent as $log): ?>
                            <?php
                                $statusClass = match($log['status']) {
                                    'Dikembalikan' => 'badge-kembali',
                                    'Terlambat'    => 'badge-terlambat',
                                    default        => 'badge-pinjam',
                                };
                                $idShort = substr($log['source_id'], 0, 14) . '…';
                            ?>
                            <tr>
                                <td class="id-col" title="<?= htmlspecialchars($log['source_id']) ?>">
                                    <?= htmlspecialchars($idShort) ?>
                                </td>
                                <td><?= htmlspecialchars($log['peminjam']) ?></td>
                                <td><?= htmlspecialchars($log['judul_buku']) ?></td>
                                <td><?= htmlspecialchars($log['tgl_pinjam'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($log['tgl_jatuh_tempo'] ?: '-') ?></td>
                                <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($log['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- KOLOM KANAN -->
            <div class="dash-right">

                <!-- GRAFIK BAR -->
                <div class="card card-chart">
                    <h6 class="card-title">Peminjaman 6 Bulan Terakhir</h6>
                    <div class="chart-bars">
                        <?php foreach ($bulan_labels as $i => $lbl): ?>
                        <?php $pct = $chart_max > 0 ? round(($bulan_data[$i] / $chart_max) * 100) : 0; ?>
                        <div class="chart-col">
                            <div class="chart-bar-wrap">
                                <div class="chart-bar" style="height:<?= max($pct, 4) ?>%">
                                    <?php if ($bulan_data[$i] > 0): ?>
                                    <span class="bar-val"><?= $bulan_data[$i] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="bar-label"><?= $lbl ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ADMIN CARD -->
                <div class="card card-admin">
                    <div class="admin-avatar"><i class="bi bi-person-circle"></i></div>
                    <div class="admin-info">
                        <span class="admin-name"><?= htmlspecialchars($admin_nama) ?></span>
                        <span class="admin-role"><?= htmlspecialchars($admin_jabatan) ?></span>
                    </div>
                    <div class="admin-stats">
                        <div class="admin-stat-item">
                            <span class="admin-stat-val"><?= count($laporan) ?></span>
                            <span class="admin-stat-lbl">Total Transaksi</span>
                        </div>
                        <div class="admin-stat-item">
                            <span class="admin-stat-val"><?= $total_anggota ?></span>
                            <span class="admin-stat-lbl">Anggota</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>