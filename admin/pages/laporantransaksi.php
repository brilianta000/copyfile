<?php
/*
|--------------------------------------------------------------------------
| KONFIGURASI AWAL
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (session_status() === PHP_SESSION_NONE) session_start();
$laporanFile = __DIR__ . '/data_laporan_transaksi.json';

/*
|--------------------------------------------------------------------------
| DATA AWAL LAPORAN
|--------------------------------------------------------------------------
*/
function defaultLaporanTransaksi()
{
    return [
        ['id' => 1, 'tanggal' => '2024-03-29', 'peminjam' => 'Budi', 'judul_buku' => 'Atomic Habits', 'tgl_pinjam' => '2024-03-28', 'tgl_jatuh_tempo' => '2024-04-02', 'tgl_kembali' => '2024-04-02', 'status' => 'Dikembalikan'],
        ['id' => 2, 'tanggal' => '2024-03-27', 'peminjam' => 'Fita', 'judul_buku' => 'Bumi Manusia', 'tgl_pinjam' => '2024-03-27', 'tgl_jatuh_tempo' => '2024-04-03', 'tgl_kembali' => '', 'status' => 'Terlambat'],
        ['id' => 3, 'tanggal' => '2024-03-27', 'peminjam' => 'Ayu', 'judul_buku' => 'Filosofi Teras', 'tgl_pinjam' => '2024-03-27', 'tgl_jatuh_tempo' => '2024-04-01', 'tgl_kembali' => '', 'status' => 'Belum Kembali'],
        ['id' => 4, 'tanggal' => '2024-03-26', 'peminjam' => 'Cahyo', 'judul_buku' => 'Orang-Orang Biasa', 'tgl_pinjam' => '2024-03-26', 'tgl_jatuh_tempo' => '2024-04-01', 'tgl_kembali' => '2024-04-01', 'status' => 'Dikembalikan'],
        ['id' => 5, 'tanggal' => '2024-03-25', 'peminjam' => 'Nanda', 'judul_buku' => 'Laut Bercerita', 'tgl_pinjam' => '2024-03-25', 'tgl_jatuh_tempo' => '2024-03-30', 'tgl_kembali' => '', 'status' => 'Terlambat'],
        ['id' => 6, 'tanggal' => '2024-03-24', 'peminjam' => 'Rina', 'judul_buku' => 'Negeri 5 Menara', 'tgl_pinjam' => '2024-03-24', 'tgl_jatuh_tempo' => '2024-03-31', 'tgl_kembali' => '', 'status' => 'Belum Kembali'],
        ['id' => 7, 'tanggal' => '2024-03-22', 'peminjam' => 'Dimas', 'judul_buku' => 'Sapiens', 'tgl_pinjam' => '2024-03-22', 'tgl_jatuh_tempo' => '2024-03-29', 'tgl_kembali' => '2024-03-28', 'status' => 'Dikembalikan'],
        ['id' => 8, 'tanggal' => '2024-03-20', 'peminjam' => 'Lina', 'judul_buku' => 'Ayat-Ayat Cinta', 'tgl_pinjam' => '2024-03-20', 'tgl_jatuh_tempo' => '2024-03-27', 'tgl_kembali' => '', 'status' => 'Belum Kembali'],
        ['id' => 9, 'tanggal' => '2024-03-18', 'peminjam' => 'Fajar', 'judul_buku' => 'Madilog', 'tgl_pinjam' => '2024-03-18', 'tgl_jatuh_tempo' => '2024-03-25', 'tgl_kembali' => '', 'status' => 'Terlambat'],
        ['id' => 10, 'tanggal' => '2024-03-15', 'peminjam' => 'Salsa', 'judul_buku' => 'Rich Dad Poor Dad', 'tgl_pinjam' => '2024-03-15', 'tgl_jatuh_tempo' => '2024-03-22', 'tgl_kembali' => '2024-03-21', 'status' => 'Dikembalikan'],
        ['id' => 11, 'tanggal' => '2024-03-12', 'peminjam' => 'Yoga', 'judul_buku' => 'Cantik Itu Luka', 'tgl_pinjam' => '2024-03-12', 'tgl_jatuh_tempo' => '2024-03-19', 'tgl_kembali' => '', 'status' => 'Belum Kembali'],
        ['id' => 12, 'tanggal' => '2024-03-10', 'peminjam' => 'Mira', 'judul_buku' => 'Bumi', 'tgl_pinjam' => '2024-03-10', 'tgl_jatuh_tempo' => '2024-03-17', 'tgl_kembali' => '2024-03-16', 'status' => 'Dikembalikan'],
    ];
}

function loadLaporanTransaksi($file)
{
    if (!file_exists($file)) {
        $default = defaultLaporanTransaksi();
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        return $default;
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        $default = defaultLaporanTransaksi();
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        return $default;
    }

    return $data;
}

function saveLaporanTransaksi($file, $data)
{
    file_put_contents(
        $file,
        json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function refreshStatusLaporan(array $item)
{
    if (!empty($item['tgl_kembali'])) {
        $item['status'] = 'Dikembalikan';
        return $item;
    }

    $jatuhTempo = $item['tgl_jatuh_tempo'] ?? '';
    $item['status'] = ($jatuhTempo !== '' && strtotime(date('Y-m-d')) > strtotime($jatuhTempo))
        ? 'Terlambat'
        : 'Belum Kembali';

    return $item;
}

/*
|--------------------------------------------------------------------------
| HELPER FUNCTIONS
|--------------------------------------------------------------------------
*/
function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatTanggal($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    return date('d M Y', strtotime($tanggal));
}

function getCurrentMenuLaporan()
{
    return $_GET['menu'] ?? 'laporan';
}

function buatQuery(array $tambahan = [], array $hapus = [])
{
    $query = array_merge(
        ['menu' => getCurrentMenuLaporan()],
        $_GET
    );

    foreach ($hapus as $key) {
        unset($query[$key]);
    }

    foreach ($tambahan as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }

    return http_build_query($query);
}

function buildExportUrl(array $tambahan = [], array $hapus = [])
{
    $query = $_GET;

    unset($query['menu']);

    foreach ($hapus as $key) {
        unset($query[$key]);
    }

    foreach ($tambahan as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }

    return 'laporantransaksi/laporantransaksi.php?' . http_build_query($query);
}

function getStatusClass($status)
{
    if ($status === 'Dikembalikan') {
        return 'status-green';
    }

    if ($status === 'Terlambat') {
        return 'status-orange';
    }

    if ($status === 'Belum Kembali') {
        return 'status-red';
    }

    return 'status-default';
}

/*
|--------------------------------------------------------------------------
| EXPORT PDF
|--------------------------------------------------------------------------
*/
function getPdfStyles()
{
    $pdfCssPath = __DIR__ . '/laporantransaksipdf.css';

    if (file_exists($pdfCssPath)) {
        return file_get_contents($pdfCssPath);
    }

    return 'body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#344054;margin:24px;} .report-title{font-size:22px;font-weight:700;margin:0 0 14px;color:#1d2939;} .filter-list{margin:0 0 16px;padding:0;list-style:none;} .filter-list li{display:inline-block;margin:0 8px 8px 0;padding:7px 12px;border:1px solid #e4e7ec;border-radius:999px;background:#f8fafc;font-size:11px;color:#475467;} .report-table{width:100%;border-collapse:collapse;} .report-table th{padding:12px 14px;background:#fafbfc;border:1px solid #edf0f4;font-size:11px;font-weight:700;color:#98a2b3;text-align:left;} .report-table td{padding:12px 14px;border:1px solid #edf0f4;font-size:12px;color:#344054;vertical-align:middle;} .badge{display:inline-block;padding:6px 12px;border-radius:4px;font-size:10px;font-weight:700;color:#ffffff;} .badge-green{background:#22c55e;} .badge-red{background:#ef4444;} .badge-blue{background:#0f56b8;} .badge-default{background:#667085;} .empty-state{text-align:center;padding:28px 12px;color:#667085;}';
}

function buildFilterText($statusFilter, $startDate, $endDate, $keyword)
{
    $items = [];
    $items[] = 'Status: ' . ($statusFilter !== '' ? $statusFilter : 'Semua');

    if ($startDate !== '' || $endDate !== '') {
        $tanggal = 'Tanggal: ' . ($startDate !== '' ? formatTanggal($startDate) : '-') . ' - ' . ($endDate !== '' ? formatTanggal($endDate) : '-');
        $items[] = $tanggal;
    }

    if ($keyword !== '') {
        $items[] = 'Pencarian: ' . $keyword;
    }

    return $items;
}

function buildExportHtml($laporan, $statusFilter, $startDate, $endDate, $keyword, $autoPrint = false, $showPrintNote = false)
{
    $filters = buildFilterText($statusFilter, $startDate, $endDate, $keyword);
    $styles = getPdfStyles();

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        <?= $styles ?>
    </style>
</head>
<body>
    <div class="main-content"></div>
    <div class="report-wrapper">
        <h1 class="report-title">Laporan Transaksi</h1>

        <ul class="filter-list">
            <?php foreach ($filters as $filter): ?>
                <li><?= escape($filter) ?></li>
            <?php endforeach; ?>
        </ul>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Peminjam</th>
                    <th>Judul Buku</th>
                    <th>Tgl. Pinjam</th>
                    <th>Tgl. Jatuh Tempo</th>
                    <th>Tgl. Kembali</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporan)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Data laporan tidak ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($laporan as $row): ?>
                        <?php
                        $badgeClass = 'badge-default';
                        if ($row['status'] === 'Dikembalikan') {
                            $badgeClass = 'badge-green';
                        } elseif ($row['status'] === 'Terlambat') {
                            $badgeClass = 'badge-orange';
                        } elseif ($row['status'] === 'Belum Kembali') {
                            $badgeClass = 'badge-red';
                        }
                        ?>
                        <tr>
                            <td><?= escape(formatTanggal($row['tanggal'])) ?></td>
                            <td><?= escape($row['peminjam']) ?></td>
                            <td><?= escape($row['judul_buku']) ?></td>
                            <td><?= escape(formatTanggal($row['tgl_pinjam'])) ?></td>
                            <td><?= escape(formatTanggal($row['tgl_jatuh_tempo'])) ?></td>
                            <td><?= escape(formatTanggal($row['tgl_kembali'])) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= escape($row['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($autoPrint): ?>
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
        </div>
    <?php endif; ?>
</body>
</html>
<?php

    return ob_get_clean();
}

function exportLaporanPdf($laporan, $statusFilter, $startDate, $endDate, $keyword)
{
    $html = buildExportHtml($laporan, $statusFilter, $startDate, $endDate, $keyword, false, false);

    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', false);
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->render();
    $dompdf->stream('laporantransaksi.pdf', ['Attachment' => true]);
    exit;
}

/*
|--------------------------------------------------------------------------
| PROSES HAPUS DATA
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $selectedIds = array_map('intval', $_POST['selected_ids'] ?? []);
    $laporanData = loadLaporanTransaksi($laporanFile);

    if (!empty($selectedIds)) {
        $laporanData = array_values(array_filter(
            $laporanData,
            function ($item) use ($selectedIds) {
                return !in_array((int) ($item['id'] ?? 0), $selectedIds, true);
            }
        ));

        saveLaporanTransaksi($laporanFile, $laporanData);
    }

    $returnQuery = trim($_POST['return_query'] ?? '');
    $redirectUrl = $_SERVER['PHP_SELF'] . ($returnQuery !== '' ? '?' . $returnQuery : '');
    header('Location: ' . $redirectUrl);
    exit;
}

/*
|--------------------------------------------------------------------------
| FILTER DATA DAN PAGINATION
|--------------------------------------------------------------------------
*/
$statusFilter = $_GET['status'] ?? 'Semua';
$startDate = array_key_exists('start_date', $_GET) ? $_GET['start_date'] : '';
$endDate = array_key_exists('end_date', $_GET) ? $_GET['end_date'] : '';
$keyword = trim($_GET['keyword'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 5;

$semuaLaporan = array_map('refreshStatusLaporan', loadLaporanTransaksi($laporanFile));
saveLaporanTransaksi($laporanFile, $semuaLaporan);

/*
|--------------------------------------------------------------------------
| Data dashboard: hanya ikut filter tanggal
|--------------------------------------------------------------------------
*/
$laporanDashboard = array_values(array_filter($semuaLaporan, function ($item) use ($startDate, $endDate) {
    if ($startDate !== '' && strtotime($item['tanggal']) < strtotime($startDate)) {
        return false;
    }

    if ($endDate !== '' && strtotime($item['tanggal']) > strtotime($endDate)) {
        return false;
    }

    return true;
}));

/*
|--------------------------------------------------------------------------
| Data tabel: ikut filter tanggal + status + keyword
|--------------------------------------------------------------------------
*/
$laporan = array_values(array_filter($laporanDashboard, function ($item) use ($statusFilter, $keyword) {
    if ($statusFilter !== 'Semua' && $item['status'] !== $statusFilter) {
        return false;
    }

    if ($keyword !== '') {
        $cocokPeminjam = stripos($item['peminjam'], $keyword) !== false;
        $cocokBuku = stripos($item['judul_buku'], $keyword) !== false;

        if (!$cocokPeminjam && !$cocokBuku) {
            return false;
        }
    }

    return true;
}));

if (isset($_GET['action']) && $_GET['action'] === 'export') {
    exportLaporanPdf($laporan, $statusFilter, $startDate, $endDate, $keyword);
}

/*
|--------------------------------------------------------------------------
| Dashboard dihitung dari data yang sudah terfilter tanggal
|--------------------------------------------------------------------------
*/
$totalPeminjaman = count($laporanDashboard);
$totalDikembalikan = count(array_filter($laporanDashboard, fn($item) => $item['status'] === 'Dikembalikan'));
$totalTerlambat = count(array_filter($laporanDashboard, fn($item) => $item['status'] === 'Terlambat'));
$totalBelumKembali = count(array_filter($laporanDashboard, fn($item) => $item['status'] === 'Belum Kembali'));

/*
|--------------------------------------------------------------------------
| Pagination tetap pakai data tabel
|--------------------------------------------------------------------------
*/
$totalData = count($laporan);
$totalHalaman = max(1, (int) ceil($totalData / $perPage));
$page = min($page, $totalHalaman);
$offset = ($page - 1) * $perPage;
$dataTampil = array_slice($laporan, $offset, $perPage);
$returnQuery = buatQuery([], ['action']);
?>
<!--
|--------------------------------------------------------------------------
| TEMPLATE HALAMAN LAPORAN TRANSAKSI
|--------------------------------------------------------------------------
-->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <link rel="stylesheet" href="laporantransaksi.css">
</head>
<body>
    <div class="page-wrapper">
        <header class="page-header">
            <h1>Laporan Transaksi</h1>
        </header>

        <section class="toolbar">
            <form method="GET" class="filter-form" id="filter-form">
                <input type="hidden" name="menu" value="<?= escape($_GET['menu'] ?? 'laporan') ?>">
                <input type="hidden" name="page" value="1">
                <div class="field-group status-filter">
                    <span class="field-label">Tampilkan:</span>
                    <select name="status" id="status-filter">
                        <option value="Semua" <?= $statusFilter === 'Semua' ? 'selected' : '' ?>>Semua</option>
                        <option value="Dikembalikan" <?= $statusFilter === 'Dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                        <option value="Terlambat" <?= $statusFilter === 'Terlambat' ? 'selected' : '' ?>>Terlambat</option>
                        <option value="Belum Kembali" <?= $statusFilter === 'Belum Kembali' ? 'selected' : '' ?>>Belum Kembali</option>
                    </select>
                    <svg class="select-chevron" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M5 7L10 12L15 7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>

                <div class="field-group date-range">
                    <input type="date" name="start_date" value="<?= escape($startDate) ?>" id="start-date">
                    <span class="separator">—</span>
                    <input type="date" name="end_date" value="<?= escape($endDate) ?>" id="end-date">
                </div>

                <div class="search-box">
                    <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                        <path d="M16 16L20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                    </svg>
                    <input type="text" name="keyword" placeholder="Cari peminjam / buku..." value="<?= escape($keyword) ?>">
                </div>

                <button type="submit" class="hidden-submit" aria-hidden="true" tabindex="-1">Terapkan</button>
            </form>

            <a href="<?= escape(buildExportUrl(['action' => 'export', 'page' => 1])) ?>" class="export-button">
    <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M7 3.5H14L18.5 8V20.5H7V3.5Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"></path>
        <path d="M14 3.5V8H18.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"></path>
        <path d="M12 10.5V16.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"></path>
        <path d="M9.5 14L12 16.5L14.5 14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path>
    </svg>
    <span>Export Laporan</span>
</a>
        </section>

        <section class="dashboard-cards">
    <article class="card card-blue">
        <div class="card-top">
            <span class="card-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="4" y="7" width="16" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"></rect>
                    <path d="M7 10H17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                </svg>
            </span>
            <span class="card-label">Peminjaman</span>
        </div>
        <div class="card-value"><?= $totalPeminjaman ?></div>
    </article>

    <article class="card card-green">
        <div class="card-top">
            <span class="card-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="4" y="4" width="16" height="16" rx="3" fill="none" stroke="currentColor" stroke-width="1.8"></rect>
                    <path d="M8 12L11 15L16 9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </span>
            <span class="card-label">Dikembalikan</span>
        </div>
        <div class="card-value"><?= $totalDikembalikan ?></div>
    </article>

    <article class="card card-yellow">
        <div class="card-top">
            <span class="card-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                    <path d="M12 8V12L14.5 13.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </span>
            <span class="card-label">Terlambat</span>
        </div>
        <div class="card-value"><?= $totalTerlambat ?></div>
    </article>

    <article class="card card-red">
        <div class="card-top">
            <span class="card-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                    <path d="M12 8V12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
                    <circle cx="12" cy="15.5" r="1" fill="currentColor"></circle>
                </svg>
            </span>
            <span class="card-label">Belum Kembali</span>
        </div>
        <div class="card-value"><?= $totalBelumKembali ?></div>
    </article>
</section>

        <section class="table-card">
            <form method="POST">
                <input type="hidden" name="return_query" value="<?= escape($returnQuery) ?>">

                <div class="table-actions">
                    <label class="select-master">
                        <input type="checkbox" id="select-all">
                    </label>
                    <span class="action-muted">Pilih:</span>
                    <button type="button" class="action-link" id="select-all-trigger">Semua</button>
                    <span class="action-divider">|</span>
                    <button type="submit" name="delete_selected" class="action-link delete-link" onclick="return confirmDelete()">Hapus Dipilih</button>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th class="checkbox-column"></th>
                                <th>Tanggal</th>
                                <th>Peminjam</th>
                                <th>Judul Buku</th>
                                <th>Tgl. Pinjam</th>
                                <th>Tgl. Jatuh Tempo</th>
                                <th>Tgl. Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($dataTampil)): ?>
                                <tr>
                                    <td colspan="8" class="empty-state">Data laporan tidak ditemukan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($dataTampil as $row): ?>
                                    <tr>
                                        <td class="checkbox-column">
                                            <input type="checkbox" name="selected_ids[]" value="<?= (int) $row['id'] ?>" class="row-checkbox">
                                        </td>
                                        <td><?= escape(formatTanggal($row['tanggal'])) ?></td>
                                        <td><?= escape($row['peminjam']) ?></td>
                                        <td><?= escape($row['judul_buku']) ?></td>
                                        <td><?= escape(formatTanggal($row['tgl_pinjam'])) ?></td>
                                        <td><?= escape(formatTanggal($row['tgl_jatuh_tempo'])) ?></td>
                                        <td><?= escape(formatTanggal($row['tgl_kembali'])) ?></td>
                                        <td><span class="status-badge <?= getStatusClass($row['status']) ?>"><?= escape($row['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="pagination-wrap">
                <div class="pagination">
                    <?php $prevDisabled = $page <= 1; ?>
                    <a class="page-arrow <?= $prevDisabled ? 'disabled' : '' ?>" href="<?= $prevDisabled ? '#' : '?' . escape(buatQuery(['page' => $page - 1], ['action'])) ?>" aria-label="Halaman sebelumnya">
                        <svg viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M12.5 5L7.5 10L12.5 15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>

                    <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                        <a class="page-number <?= $i === $page ? 'active' : '' ?>" href="?<?= escape(buatQuery(['page' => $i], ['action'])) ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php $nextDisabled = $page >= $totalHalaman; ?>
                    <a class="page-arrow <?= $nextDisabled ? 'disabled' : '' ?>" href="<?= $nextDisabled ? '#' : '?' . escape(buatQuery(['page' => $page + 1], ['action'])) ?>" aria-label="Halaman berikutnya">
                        <svg viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M7.5 5L12.5 10L7.5 15" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    </div>

    <!--
    |--------------------------------------------------------------------------
    | JAVASCRIPT INTERAKSI HALAMAN
    |--------------------------------------------------------------------------
    -->
    <script>
        const formFilter = document.getElementById('filter-form');
        const statusFilterElement = document.getElementById('status-filter');
        const startDate = document.getElementById('start-date');
        const endDate = document.getElementById('end-date');
        const selectAll = document.getElementById('select-all');
        const selectAllTrigger = document.getElementById('select-all-trigger');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');

        [statusFilterElement, startDate, endDate].forEach(function (element) {
            if (!element) {
                return;
            }

            element.addEventListener('change', function () {
                formFilter.submit();
            });
        });

        function setAllCheckboxes(checked) {
            rowCheckboxes.forEach(function (checkbox) {
                checkbox.checked = checked;
            });
            if (selectAll) {
                selectAll.checked = checked;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                setAllCheckboxes(selectAll.checked);
            });
        }

        if (selectAllTrigger) {
            selectAllTrigger.addEventListener('click', function () {
                const allChecked = rowCheckboxes.length > 0 && document.querySelectorAll('.row-checkbox:checked').length === rowCheckboxes.length;
                setAllCheckboxes(!allChecked);
            });
        }

        rowCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const totalChecked = document.querySelectorAll('.row-checkbox:checked').length;
                if (selectAll) {
                    selectAll.checked = rowCheckboxes.length > 0 && totalChecked === rowCheckboxes.length;
                }
            });
        });

        function confirmDelete() {
            const totalChecked = document.querySelectorAll('.row-checkbox:checked').length;
            if (totalChecked === 0) {
                alert('Pilih data yang ingin dihapus terlebih dahulu.');
                return false;
            }
            return confirm('Hapus laporan yang dipilih?');
        }
    </script>