<?php
/*
|--------------------------------------------------------------------------
| KONFIGURASI AWAL
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (($_GET['action'] ?? '') !== 'export' && session_status() === PHP_SESSION_NONE) {
    @session_start();
}
$laporanFile = __DIR__ . '/data_laporan_transaksi.json';

/*
|--------------------------------------------------------------------------
| DATA AWAL LAPORAN
|--------------------------------------------------------------------------
*/
// Menyediakan data awal laporan transaksi ketika JSON belum tersedia.
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

// Membaca data laporan transaksi dari JSON.
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

// Menyimpan data laporan transaksi ke JSON.
function saveLaporanTransaksi($file, $data)
{
    file_put_contents(
        $file,
        json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

// Memperbarui status laporan sesuai tanggal kembali dan jatuh tempo.
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
// Escape output laporan agar aman ditampilkan ke HTML.
function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Memformat tanggal untuk tampilan tabel.
function formatTanggal($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    return date('d M Y', strtotime($tanggal));
}

// Memformat tanggal khusus untuk export PDF.
function formatTanggalPdf($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    return date('d-m-Y', strtotime($tanggal));
}

// Mengambil nama menu aktif untuk URL laporan.
function getCurrentMenuLaporan()
{
    return $_GET['menu'] ?? 'laporan';
}

// Membuat query string untuk filter laporan.
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

// Membuat URL export laporan dengan filter aktif.
function buildExportUrl(array $tambahan = [], array $hapus = [])
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

    return '?' . http_build_query($query);
}

// Menyediakan opsi jumlah data laporan per halaman.
function getLaporanPerPageOptions(): array
{
    return [5, 7, 10, 15, 20];
}

// Memvalidasi jumlah data laporan per halaman.
function normalizeLaporanPerPage($value, int $default = 5): int
{
    $value = (int) $value;
    return in_array($value, getLaporanPerPageOptions(), true) ? $value : $default;
}

// Menentukan class CSS berdasarkan status laporan.
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
// Mengambil CSS untuk tampilan export PDF.
function getPdfStyles()
{
    $pdfCssPath = __DIR__ . '/../../public/css/laporantransaksipdf.css';

    if (file_exists($pdfCssPath)) {
        return file_get_contents($pdfCssPath);
    }

    return 'body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10px;color:#000;margin:0}.letterhead{position:relative;min-height:82px;border-bottom:2px solid #111;margin-bottom:8px}.letterhead-logo{position:absolute;left:68px;top:0;width:72px}.letterhead-text{text-align:center;font-weight:700;line-height:1.15;font-size:14px}.letterhead-address{font-weight:400;font-size:10px}.report-heading{text-align:center;margin:8px 0 10px;font-size:11px}.report-table{width:100%;border-collapse:collapse}.report-table th,.report-table td{border:1px solid #202840;padding:4px 5px;font-size:9px;line-height:1.2}.report-table th{text-align:center;font-weight:700}.empty-state{text-align:center;padding:18px}';
}

// Mengubah logo Polije menjadi data URI untuk PDF.
function getLogoPolijeDataUri(): string
{
    $logoPath = __DIR__ . '/../../logo_polije.png';

    if (!file_exists($logoPath)) {
        return '';
    }

    $data = base64_encode(file_get_contents($logoPath));
    return 'data:image/png;base64,' . $data;
}

// Menghitung denda laporan berdasarkan keterlambatan.
function hitungDendaLaporan(array $row): string
{
    $jatuhTempo = $row['tgl_jatuh_tempo'] ?? '';

    if ($jatuhTempo === '') {
        return 'Rp 0';
    }

    $tanggalKembali = !empty($row['tgl_kembali']) ? $row['tgl_kembali'] : date('Y-m-d');
    $jatuhTempoTime = strtotime($jatuhTempo);
    $tanggalKembaliTime = strtotime($tanggalKembali);

    if (!$jatuhTempoTime || !$tanggalKembaliTime || $tanggalKembaliTime <= $jatuhTempoTime) {
        return 'Rp 0';
    }

    $hariTerlambat = (int) floor(($tanggalKembaliTime - $jatuhTempoTime) / 86400);
    return 'Rp ' . number_format($hariTerlambat * 500, 0, ',', '.');
}

// Membuat HTML laporan yang akan dipakai untuk PDF.
function buildExportHtml($laporan, $statusFilter, $startDate, $endDate, $keyword, $autoPrint = false, $showPrintNote = false)
{
    $styles = getPdfStyles();
    $logoDataUri = getLogoPolijeDataUri();

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
    <div class="report-wrapper">
        <div class="letterhead">
            <?php if ($logoDataUri !== ''): ?>
                <img src="<?= $logoDataUri ?>" class="letterhead-logo" alt="Logo Polije">
            <?php endif; ?>
            <div class="letterhead-text">
                <div>PERPUSTAKAAN</div>
                <div>POLITEKNIK NEGERI JEMBER</div>
                <div class="letterhead-address">Jl. Mastrip PO BOX 164, Jember - Jawa Timur- Indonesia</div>
            </div>
        </div>

        <div class="report-heading">Laporan Peminjaman Buku</div>

        <table class="report-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Peminjam</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Jatuh Tempo</th>
                    <th>Tanggal Kembali</th>
                    <th>Denda</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporan)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">Data laporan tidak ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($laporan as $index => $row): ?>
                        <tr>
                            <td class="text-center"><?= (int) $index + 1 ?>.</td>
                            <td><?= escape($row['peminjam']) ?></td>
                            <td><?= escape($row['judul_buku']) ?></td>
                            <td class="text-center"><?= escape(formatTanggalPdf($row['tgl_pinjam'])) ?></td>
                            <td class="text-center"><?= escape(formatTanggalPdf($row['tgl_jatuh_tempo'])) ?></td>
                            <td class="text-center"><?= escape(formatTanggalPdf($row['tgl_kembali'])) ?></td>
                            <td class="text-center"><?= escape(hitungDendaLaporan($row)) ?></td>
                            <td class="text-center"><?= escape($row['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
<?php

    return ob_get_clean();
}

// Membuat dan mengirim file PDF laporan.
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
    $dompdf->stream('laporan-peminjaman-buku.pdf', ['Attachment' => true]);
    exit;
}

/*
|--------------------------------------------------------------------------
| PROSES HAPUS DATA
|--------------------------------------------------------------------------
*/
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['delete_selected'])) {
    $selectedIds = array_map('intval', $_POST['selected_ids'] ?? []);
    $laporanData = loadLaporanTransaksi($laporanFile);

    // Hapus hanya data yang dipilih melalui checkbox tabel.
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
$perPage = normalizeLaporanPerPage($_GET['per_page'] ?? 5);

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
    // Export memakai data laporan yang sudah mengikuti filter aktif.
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

        <!-- Toolbar filter, pencarian, dan export laporan -->
        <section class="toolbar">
            <form method="GET" class="filter-form" id="filter-form">
                <input type="hidden" name="menu" value="<?= escape($_GET['menu'] ?? 'laporan') ?>">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="per_page" value="<?= (int) $perPage ?>">
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
                    <span class="separator">&ndash;</span>
                    <input type="date" name="end_date" value="<?= escape($endDate) ?>" id="end-date">
                </div>

                <div class="search-box">
                    <input type="text" name="keyword" placeholder="Cari peminjam / buku..." value="<?= escape($keyword) ?>">
                    <button type="submit" class="search-submit" aria-label="Cari laporan">
                        <i class="bi bi-search"></i>
                    </button>
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

        <!-- Ringkasan statistik laporan sesuai filter tanggal -->
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

        <!-- Tabel laporan dan aksi hapus data terpilih -->
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
                    <button type="button" class="action-link delete-link" id="openDeleteConfirm">Hapus Dipilih</button>
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

                <div class="delete-confirm-overlay" id="deleteConfirmOverlay" aria-hidden="true">
                    <div class="delete-confirm-box">
                        <div class="delete-confirm-header">
                            <h3>Konfirmasi Hapus</h3>
                            <button type="button" class="delete-confirm-close" id="deleteConfirmClose" aria-label="Tutup">&times;</button>
                        </div>

                        <div class="delete-confirm-body">
                            <p class="delete-confirm-text">Yakin ingin menghapus data laporan yang dipilih?</p>
                            <div class="delete-confirm-detail">
                                <div><strong>Jumlah data:</strong> <span id="deleteConfirmCount">0</span></div>
                                <div><strong>Menu:</strong> Laporan Peminjaman Buku</div>
                            </div>
                        </div>

                        <div class="delete-confirm-actions">
                            <button type="button" class="btn-delete-batal" id="deleteConfirmCancel">Batal</button>
                            <button type="submit" name="delete_selected" class="btn-delete-submit">Hapus</button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="pagination-wrap">
                <div class="table-footer-info">
                    <span>Menampilkan <?= (int) ($totalData > 0 ? $offset + 1 : 0) ?>-<?= (int) ($totalData > 0 ? min($offset + $perPage, $totalData) : 0) ?> dari <?= (int) $totalData ?> data</span>
                    <form method="get" class="per-page-form">
                        <input type="hidden" name="menu" value="<?= escape($_GET['menu'] ?? 'laporan') ?>">
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="status" value="<?= escape($statusFilter) ?>">
                        <input type="hidden" name="start_date" value="<?= escape($startDate) ?>">
                        <input type="hidden" name="end_date" value="<?= escape($endDate) ?>">
                        <input type="hidden" name="keyword" value="<?= escape($keyword) ?>">
                        <label>
                            <span>Tampilkan</span>
                            <select name="per_page" onchange="this.form.submit()">
                                <?php foreach (getLaporanPerPageOptions() as $option): ?>
                                    <option value="<?= (int) $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= (int) $option ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span>data</span>
                        </label>
                    </form>
                </div>
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
        const openDeleteConfirm = document.getElementById('openDeleteConfirm');
        const deleteConfirmOverlay = document.getElementById('deleteConfirmOverlay');
        const deleteConfirmClose = document.getElementById('deleteConfirmClose');
        const deleteConfirmCancel = document.getElementById('deleteConfirmCancel');
        const deleteConfirmCount = document.getElementById('deleteConfirmCount');

        // Auto-submit filter status dan rentang tanggal.
        [statusFilterElement, startDate, endDate].forEach(function (element) {
            if (!element) {
                return;
            }

            element.addEventListener('change', function () {
                formFilter.submit();
            });
        });

        // Mengatur semua checkbox baris laporan.
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

        // Membuka popup konfirmasi hapus laporan terpilih.
        function openDeleteModal() {
            const totalChecked = document.querySelectorAll('.row-checkbox:checked').length;
            if (totalChecked === 0) {
                alert('Pilih data yang ingin dihapus terlebih dahulu.');
                return;
            }

            if (deleteConfirmCount) {
                deleteConfirmCount.textContent = totalChecked;
            }

            if (deleteConfirmOverlay) {
                deleteConfirmOverlay.classList.add('show');
                deleteConfirmOverlay.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
            }
        }

        // Menutup popup konfirmasi hapus laporan.
        function closeDeleteModal() {
            if (!deleteConfirmOverlay) {
                return;
            }

            deleteConfirmOverlay.classList.remove('show');
            deleteConfirmOverlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        }

        if (openDeleteConfirm) {
            openDeleteConfirm.addEventListener('click', openDeleteModal);
        }

        if (deleteConfirmClose) {
            deleteConfirmClose.addEventListener('click', closeDeleteModal);
        }

        if (deleteConfirmCancel) {
            deleteConfirmCancel.addEventListener('click', closeDeleteModal);
        }

        if (deleteConfirmOverlay) {
            deleteConfirmOverlay.addEventListener('click', function (event) {
                if (event.target === deleteConfirmOverlay) {
                    closeDeleteModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && deleteConfirmOverlay && deleteConfirmOverlay.classList.contains('show')) {
                closeDeleteModal();
            }
        });
    </script>
