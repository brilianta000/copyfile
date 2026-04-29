<?php
/*
|--------------------------------------------------------------------------
| KONFIGURASI AWAL
|--------------------------------------------------------------------------
*/
$dataFile = __DIR__ . '/data_peminjaman.json';
$laporanFile = __DIR__ . '/../laporantransaksi/data_laporan_transaksi.json';

$openPopup = false;
$errors = [];
$oldInput = [
    'nim' => '',
    'nama' => '',
    'buku' => '',
    'tgl_pinjam' => '',
    'tgl_kembali' => '',
];

/*
|--------------------------------------------------------------------------
| HELPER UMUM
|--------------------------------------------------------------------------
*/
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function createId(): string
{
    return uniqid('pjm_', true);
}

function todayDate(): string
{
    return date('Y-m-d');
}

function defaultTanggalKembali(): string
{
    return date('Y-m-d', strtotime('+7 days'));
}

function getCurrentMenuPeminjaman(): string
{
    return $_GET['menu'] ?? 'peminjaman';
}

function redirectTo(array $params = []): void
{
    $url = basename($_SERVER['PHP_SELF']);
    $params = array_merge(['menu' => getCurrentMenuPeminjaman()], $params);

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    header('Location: ' . $url);
    exit;
}

function formatTanggal($date): string
{
    if (empty($date)) {
        return '-';
    }

    $timestamp = strtotime((string) $date);
    return $timestamp ? date('d M Y', $timestamp) : '-';
}

/*
|--------------------------------------------------------------------------
| DATA AWAL
|--------------------------------------------------------------------------
| Dipakai hanya saat file JSON belum ada atau rusak.
*/
function seedPeminjaman(): array
{
    $today = new DateTimeImmutable('today');
    $rel = function (string $modifier) use ($today): string {
        return $today->modify($modifier)->format('Y-m-d');
    };

    return [
        [
            'id' => createId(),
            'nim' => '123456',
            'nama' => 'Fajar',
            'buku' => 'Pemrograman Web',
            'tanggal_pinjam' => $rel('-3 days'),
            'tanggal_kembali' => $rel('+6 days'),
            'returned_at' => null,
        ],
        [
            'id' => createId(),
            'nim' => '123457',
            'nama' => 'Dina',
            'buku' => 'Basis Data',
            'tanggal_pinjam' => $rel('-6 days'),
            'tanggal_kembali' => $rel('+2 days'),
            'returned_at' => null,
        ],
        [
            'id' => createId(),
            'nim' => '123458',
            'nama' => 'Budi',
            'buku' => 'Jaringan Komputer',
            'tanggal_pinjam' => $rel('-12 days'),
            'tanggal_kembali' => $rel('-2 days'),
            'returned_at' => null,
        ],
        [
            'id' => createId(),
            'nim' => '123459',
            'nama' => 'Andi',
            'buku' => 'Sistem Informasi',
            'tanggal_pinjam' => $rel('-18 days'),
            'tanggal_kembali' => $rel('-10 days'),
            'returned_at' => $rel('-8 days'),
        ],
        [
            'id' => createId(),
            'nim' => '123460',
            'nama' => 'Rani',
            'buku' => 'Pemrograman Java',
            'tanggal_pinjam' => $rel('-24 days'),
            'tanggal_kembali' => $rel('-16 days'),
            'returned_at' => $rel('-16 days'),
        ],
        [
            'id' => createId(),
            'nim' => '123461',
            'nama' => 'Eko',
            'buku' => 'Teknik Elektro',
            'tanggal_pinjam' => $rel('-14 days'),
            'tanggal_kembali' => $rel('-3 days'),
            'returned_at' => null,
        ],
        [
            'id' => createId(),
            'nim' => '123462',
            'nama' => 'Widya',
            'buku' => 'Manajemen Keuangan',
            'tanggal_pinjam' => $rel('-4 days'),
            'tanggal_kembali' => $rel('+5 days'),
            'returned_at' => null,
        ],
    ];
}

/*
|--------------------------------------------------------------------------
| SIMPAN DAN LOAD DATA JSON
|--------------------------------------------------------------------------
*/
function ensureDirectoryExists(string $file): void
{
    $dir = dirname($file);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

function readJsonArray(string $file, array $fallback = []): array
{
    if (!file_exists($file)) {
        return $fallback;
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    return is_array($data) ? $data : $fallback;
}

function writeJsonArray(string $file, array $data): void
{
    ensureDirectoryExists($file);

    file_put_contents(
        $file,
        json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function loadPeminjaman(string $file): array
{
    if (!file_exists($file)) {
        $defaultData = seedPeminjaman();
        savePeminjaman($file, $defaultData);
        return $defaultData;
    }

    $data = readJsonArray($file);

    if (!is_array($data)) {
        $defaultData = seedPeminjaman();
        savePeminjaman($file, $defaultData);
        return $defaultData;
    }

    return $data;
}

function savePeminjaman(string $file, array $data): void
{
    writeJsonArray($file, $data);
}

function loadLaporanTransaksi(string $file): array
{
    return readJsonArray($file);
}

function saveLaporanTransaksi(string $file, array $data): void
{
    writeJsonArray($file, $data);
}

/*
|--------------------------------------------------------------------------
| DAFTAR BUKU DAN STOK
|--------------------------------------------------------------------------
*/
function getDaftarBuku(): array
{
    return [
        'Algoritma' => 5,
        'Basis Data' => 5,
        'Jaringan' => 5,
        'AI' => 5,
        'Web Dev' => 5,
        'Python' => 5,
        'UI/UX' => 5,
        'Pemrograman Web' => 5,
        'Jaringan Komputer' => 5,
        'Sistem Informasi' => 5,
        'Pemrograman Java' => 5,
        'Teknik Elektro' => 5,
        'Manajemen Keuangan' => 5,
    ];
}

function isBukuValid(string $buku): bool
{
    return array_key_exists($buku, getDaftarBuku());
}

function getStokBuku(string $buku): int
{
    $daftarBuku = getDaftarBuku();
    return $daftarBuku[$buku] ?? 0;
}

function isPinjamanAktif(array $item): bool
{
    return empty($item['returned_at']);
}

function countPinjamanAktifByNim(array $data, string $nim): int
{
    $total = 0;
    $nim = trim($nim);

    foreach ($data as $item) {
        if (isPinjamanAktif($item) && trim((string) ($item['nim'] ?? '')) === $nim) {
            $total++;
        }
    }

    return $total;
}

function countPinjamanAktifByBuku(array $data, string $buku): int
{
    $total = 0;
    $buku = trim($buku);

    foreach ($data as $item) {
        if (isPinjamanAktif($item) && trim((string) ($item['buku'] ?? '')) === $buku) {
            $total++;
        }
    }

    return $total;
}

function getSisaStokBuku(array $data, string $buku): int
{
    if (!isBukuValid($buku)) {
        return 0;
    }

    return max(0, getStokBuku($buku) - countPinjamanAktifByBuku($data, $buku));
}

function getOpsiBuku(array $dataPeminjaman): array
{
    $opsi = [];

    foreach (getDaftarBuku() as $judul => $stokTotal) {
        $opsi[] = [
            'judul' => $judul,
            'stok' => getSisaStokBuku($dataPeminjaman, $judul),
            'stok_total' => $stokTotal,
        ];
    }

    return $opsi;
}

/*
|--------------------------------------------------------------------------
| LAPORAN TRANSAKSI
|--------------------------------------------------------------------------
*/
function nextLaporanId(array $data): int
{
    $max = 0;

    foreach ($data as $item) {
        $max = max($max, (int) ($item['id'] ?? 0));
    }

    return $max + 1;
}

function buildStatusLaporan(string $jatuhTempo, string $tglKembali = ''): string
{
    if ($tglKembali !== '') {
        return 'Dikembalikan';
    }

    return strtotime(todayDate()) > strtotime($jatuhTempo) ? 'Terlambat' : 'Belum Kembali';
}

function cariIndexLaporanBySourceId(array $laporanData, string $sourceId): ?int
{
    foreach ($laporanData as $index => $item) {
        if (($item['source_id'] ?? '') === $sourceId) {
            return $index;
        }
    }

    return null;
}

function buatItemLaporanDariPeminjaman(array $item, ?int $laporanId = null): array
{
    $tglKembaliReal = !empty($item['returned_at']) ? $item['returned_at'] : '';

    return [
        'id' => $laporanId,
        'source_id' => $item['id'],
        'tanggal' => $item['tanggal_pinjam'],
        'peminjam' => $item['nama'],
        'judul_buku' => $item['buku'],
        'tgl_pinjam' => $item['tanggal_pinjam'],
        'tgl_jatuh_tempo' => $item['tanggal_kembali'],
        'tgl_kembali' => $tglKembaliReal,
        'status' => buildStatusLaporan($item['tanggal_kembali'], $tglKembaliReal),
    ];
}

function sinkronkanPeminjamanKeLaporan(array $dataPeminjaman, array $laporanData): array
{
    $changed = false;

    foreach ($dataPeminjaman as $item) {
        if (empty($item['id'])) {
            continue;
        }

        $index = cariIndexLaporanBySourceId($laporanData, $item['id']);
        $laporanItem = buatItemLaporanDariPeminjaman($item);

        if ($index === null) {
            $laporanItem['id'] = nextLaporanId($laporanData);
            array_unshift($laporanData, $laporanItem);
            $changed = true;
            continue;
        }

        $laporanItem['id'] = $laporanData[$index]['id'] ?? nextLaporanId($laporanData);

        if ($laporanData[$index] != $laporanItem) {
            $laporanData[$index] = $laporanItem;
            $changed = true;
        }
    }

    return [$laporanData, $changed];
}

/*
|--------------------------------------------------------------------------
| STATUS, KETERLAMBATAN, DAN DENDA
|--------------------------------------------------------------------------
*/
function hitungMetaPeminjaman(array $item): array
{
    try {
        $today = new DateTimeImmutable('today');
        $tanggalKembali = new DateTimeImmutable($item['tanggal_kembali']);
        $returnedAt = !empty($item['returned_at']) ? new DateTimeImmutable($item['returned_at']) : null;
    } catch (Exception $e) {
        return [
            'status' => 'Dipinjam',
            'terlambat' => '-',
            'denda' => 'Rp 0',
            'late_days' => 0,
        ];
    }

    $pembanding = $returnedAt ?: $today;
    $lateDays = $pembanding > $tanggalKembali
        ? (int) $tanggalKembali->diff($pembanding)->format('%a')
        : 0;

    if ($returnedAt) {
        $status = 'Dikembalikan';
    } elseif ($today > $tanggalKembali) {
        $status = 'Terlambat';
    } else {
        $status = 'Dipinjam';
    }

    return [
        'status' => $status,
        'terlambat' => $lateDays > 0 ? $lateDays . ' hari' : '-',
        'denda' => 'Rp ' . number_format($lateDays * 500, 0, ',', '.'),
        'late_days' => $lateDays,
    ];
}

/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/
function buildPageUrl(int $page, string $search): string
{
    $params = [
        'menu' => getCurrentMenuPeminjaman(),
        'page' => $page,
    ];

    if ($search !== '') {
        $params['q'] = $search;
    }

    return '?' . http_build_query($params);
}

function getPaginationItems(int $currentPage, int $totalPages): array
{
    $items = [];
    $lastWasDots = false;

    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i === 1 || $i === $totalPages || abs($i - $currentPage) <= 1) {
            $items[] = $i;
            $lastWasDots = false;
            continue;
        }

        if (!$lastWasDots) {
            $items[] = '...';
            $lastWasDots = true;
        }
    }

    return $items;
}

/*
|--------------------------------------------------------------------------
| LOAD DATA
|--------------------------------------------------------------------------
*/
$dataPeminjamanSemua = loadPeminjaman($dataFile);
$laporanTransaksi = loadLaporanTransaksi($laporanFile);

[$laporanTransaksi, $laporanChanged] = sinkronkanPeminjamanKeLaporan($dataPeminjamanSemua, $laporanTransaksi);
$dataPeminjaman = array_values(array_filter($dataPeminjamanSemua, 'isPinjamanAktif'));
$peminjamanChanged = count($dataPeminjaman) !== count($dataPeminjamanSemua);

if ($laporanChanged) {
    saveLaporanTransaksi($laporanFile, $laporanTransaksi);
}

if ($peminjamanChanged) {
    savePeminjaman($dataFile, $dataPeminjaman);
}

/*
|--------------------------------------------------------------------------
| PROSES FORM
|--------------------------------------------------------------------------
*/
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_peminjaman') {
        $nim = trim($_POST['nim'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $buku = trim($_POST['buku'] ?? '');
        $tglPinjam = todayDate();
        $tglKembali = defaultTanggalKembali();

        $oldInput = [
            'nim' => $nim,
            'nama' => $nama,
            'buku' => $buku,
            'tgl_pinjam' => $tglPinjam,
            'tgl_kembali' => $tglKembali,
        ];

        if ($nim === '' || $nama === '' || $buku === '') {
            $errors[] = 'Semua field wajib diisi.';
        }

        if ($buku !== '' && !isBukuValid($buku)) {
            $errors[] = 'Buku yang dipilih tidak tersedia di daftar buku.';
        }

        if ($nim !== '' && countPinjamanAktifByNim($dataPeminjaman, $nim) >= 3) {
            $errors[] = 'Peminjaman gagal karena peminjam tersebut sedang meminjam 3 buku.';
        }

        if ($buku !== '' && isBukuValid($buku) && getSisaStokBuku($dataPeminjaman, $buku) < 1) {
            $errors[] = 'Peminjaman gagal karena stok buku "' . e($buku) . '" sedang habis.';
        }

        if (empty($errors)) {
            $newData = [
                'id' => createId(),
                'nim' => $nim,
                'nama' => $nama,
                'buku' => $buku,
                'tanggal_pinjam' => $tglPinjam,
                'tanggal_kembali' => $tglKembali,
                'returned_at' => null,
            ];

            array_unshift($dataPeminjaman, $newData);
            savePeminjaman($dataFile, $dataPeminjaman);

            $laporanTransaksi = loadLaporanTransaksi($laporanFile);
            array_unshift(
                $laporanTransaksi,
                buatItemLaporanDariPeminjaman($newData, nextLaporanId($laporanTransaksi))
            );
            saveLaporanTransaksi($laporanFile, $laporanTransaksi);

            redirectTo();
        }

        $openPopup = true;
    }

    if ($action === 'kembalikan_peminjaman') {
        $id = $_POST['id'] ?? '';
        $searchFromPost = trim($_POST['q'] ?? '');
        $pageFromPost = max(1, (int) ($_POST['page'] ?? 1));
        $laporanTransaksi = loadLaporanTransaksi($laporanFile);
        $dataBerubah = false;

        foreach ($dataPeminjaman as $index => $item) {
            if (($item['id'] ?? '') !== $id) {
                continue;
            }

            $item['returned_at'] = todayDate();
            $laporanIndex = cariIndexLaporanBySourceId($laporanTransaksi, $item['id']);
            $laporanItem = buatItemLaporanDariPeminjaman(
                $item,
                $laporanIndex !== null
                    ? ($laporanTransaksi[$laporanIndex]['id'] ?? nextLaporanId($laporanTransaksi))
                    : nextLaporanId($laporanTransaksi)
            );

            if ($laporanIndex === null) {
                array_unshift($laporanTransaksi, $laporanItem);
            } else {
                $laporanTransaksi[$laporanIndex] = $laporanItem;
            }

            unset($dataPeminjaman[$index]);
            $dataBerubah = true;
            break;
        }

        if ($dataBerubah) {
            savePeminjaman($dataFile, array_values($dataPeminjaman));
            saveLaporanTransaksi($laporanFile, $laporanTransaksi);
        }

        $redirectParams = ['menu' => 'peminjaman', 'page' => $pageFromPost];

        if ($searchFromPost !== '') {
            $redirectParams['q'] = $searchFromPost;
        }

        redirectTo($redirectParams);
    }
}

/*
|--------------------------------------------------------------------------
| SEARCH, PAGINATION, DAN OPSI POPUP
|--------------------------------------------------------------------------
*/
$search = trim($_GET['q'] ?? '');
$filteredData = array_values(array_filter($dataPeminjaman, function (array $item) use ($search): bool {
    if ($search === '') {
        return true;
    }

    return stripos((string) ($item['nim'] ?? ''), $search) !== false
        || stripos((string) ($item['nama'] ?? ''), $search) !== false
        || stripos((string) ($item['buku'] ?? ''), $search) !== false;
}));

$perPage = 7;
$totalData = count($filteredData);
$totalPages = max(1, (int) ceil($totalData / $perPage));
$currentPage = min(max(1, (int) ($_GET['page'] ?? 1)), $totalPages);
$offset = ($currentPage - 1) * $perPage;
$pageData = array_slice($filteredData, $offset, $perPage);
$startDisplay = $totalData > 0 ? $offset + 1 : 0;
$endDisplay = $totalData > 0 ? min($offset + $perPage, $totalData) : 0;
$paginationItems = getPaginationItems($currentPage, $totalPages);
$opsiBuku = getOpsiBuku($dataPeminjaman);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peminjam</title>
    <link rel="stylesheet" href="datapeminjam/datapeminjam.css">
    <link rel="stylesheet" href="datapeminjam/popuppeminjaman.css">
</head>
<body>
<div class="main-content">
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
                    <div class="search-box">
                        <input type="text" name="q" placeholder="Cari Peminjaman..." value="<?= e($search); ?>">
                        <button type="submit" class="search-submit" aria-label="Cari">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="2"></circle>
                                <path d="M16 16L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                            </svg>
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
                                    <button
                                        type="button"
                                        class="btn-kembalikan js-open-return-modal"
                                        data-id="<?= e($item['id'] ?? ''); ?>"
                                        data-nama="<?= e($item['nama'] ?? ''); ?>"
                                        data-buku="<?= e($item['buku'] ?? ''); ?>"
                                    >
                                        Kembalikan
                                    </button>
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

                    <form method="post" class="return-confirm-form">
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
                        </div>

                        <div class="return-confirm-actions">
                            <button type="button" class="btn-return-batal" id="returnConfirmCancel">Batal</button>
                            <button type="submit" class="btn-return-submit">Kembalikan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="datapeminjam-footer">
            <div class="data-count">
                Menampilkan <?= (int) $startDisplay; ?>-<?= (int) $endDisplay; ?> dari <?= (int) $totalData; ?> data
            </div>

            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= e(buildPageUrl($currentPage - 1, $search)); ?>" class="page-btn">&laquo;</a>
                <?php else: ?>
                    <span class="page-btn disabled">&laquo;</span>
                <?php endif; ?>

                <?php foreach ($paginationItems as $pageItem): ?>
                    <?php if ($pageItem === '...'): ?>
                        <span class="page-btn ellipsis">...</span>
                    <?php elseif ((int) $pageItem === $currentPage): ?>
                        <span class="page-btn active"><?= (int) $pageItem; ?></span>
                    <?php else: ?>
                        <a href="<?= e(buildPageUrl((int) $pageItem, $search)); ?>" class="page-btn"><?= (int) $pageItem; ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= e(buildPageUrl($currentPage + 1, $search)); ?>" class="page-btn">&raquo;</a>
                <?php else: ?>
                    <span class="page-btn disabled">&raquo;</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../popuppeminjaman.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const returnOverlay = document.getElementById('returnConfirmOverlay');
    const returnCloseBtn = document.getElementById('returnConfirmClose');
    const returnCancelBtn = document.getElementById('returnConfirmCancel');
    const returnIdInput = document.getElementById('returnConfirmId');
    const returnNamaText = document.getElementById('returnConfirmNama');
    const returnBukuText = document.getElementById('returnConfirmBuku');
    const returnButtons = document.querySelectorAll('.js-open-return-modal');

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

    function closeReturnModal() {
        if (!returnOverlay) {
            return;
        }

        returnOverlay.classList.remove('show');
        document.body.classList.remove('modal-open');
    }

    returnButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openReturnModal(this.dataset.id, this.dataset.nama, this.dataset.buku);
        });
    });

    if (returnCloseBtn) {
        returnCloseBtn.addEventListener('click', closeReturnModal);
    }

    if (returnCancelBtn) {
        returnCancelBtn.addEventListener('click', closeReturnModal);
    }

    if (returnOverlay) {
        returnOverlay.addEventListener('click', function (event) {
            if (event.target === returnOverlay) {
                closeReturnModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && returnOverlay && returnOverlay.classList.contains('show')) {
            closeReturnModal();
        }
    });

    const openPopupPeminjaman = document.getElementById('openPopupPeminjaman');
    const popupPeminjaman = document.getElementById('popupPeminjaman');
    const closePopupPeminjaman = document.getElementById('closePopupPeminjaman');
    const batalPopupPeminjaman = document.getElementById('batalPopupPeminjaman');

    function bukaPopupPeminjaman() {
        if (popupPeminjaman) {
            popupPeminjaman.classList.add('active');
            document.body.classList.add('modal-open');
        }
    }

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
</body>
</html>
