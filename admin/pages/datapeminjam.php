<?php
/*
|--------------------------------------------------------------------------
| KONFIGURASI AWAL
|--------------------------------------------------------------------------
*/
$dataFile = __DIR__ . '/data_peminjaman.json';
$laporanFile = __DIR__ . '/data_laporan_transaksi.json';

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
// Escape output agar aman ditampilkan ke HTML.
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Membuat ID unik untuk transaksi peminjaman.
function createId(): string
{
    return uniqid('pjm_', true);
}

// Mengambil tanggal hari ini dalam format Y-m-d.
function todayDate(): string
{
    return date('Y-m-d');
}

// Menentukan tanggal kembali default tujuh hari setelah peminjaman.
function defaultTanggalKembali(): string
{
    return date('Y-m-d', strtotime('+7 days'));
}

// Mengambil nama menu aktif untuk URL peminjaman.
function getCurrentMenuPeminjaman(): string
{
    return $_GET['menu'] ?? 'peminjaman';
}

// Mengarahkan ulang halaman peminjaman dengan query tertentu.
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

// Memformat tanggal untuk tampilan tabel.
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
// Menyediakan data peminjaman awal ketika JSON belum tersedia.
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
// Memastikan folder penyimpanan JSON sudah ada.
function ensureDirectoryExists(string $file): void
{
    $dir = dirname($file);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

// Membaca file JSON dan memastikan hasilnya array.
function readJsonArray(string $file, array $fallback = []): array
{
    if (!file_exists($file)) {
        return $fallback;
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    return is_array($data) ? $data : $fallback;
}

// Menulis array ke file JSON dengan format rapi.
function writeJsonArray(string $file, array $data): void
{
    ensureDirectoryExists($file);

    file_put_contents(
        $file,
        json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

// Membaca data peminjaman aktif dari JSON.
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

// Menyimpan data peminjaman ke JSON.
function savePeminjaman(string $file, array $data): void
{
    writeJsonArray($file, $data);
}

// Membaca data laporan transaksi dari JSON.
function loadLaporanTransaksi(string $file): array
{
    return readJsonArray($file);
}

// Menyimpan data laporan transaksi ke JSON.
function saveLaporanTransaksi(string $file, array $data): void
{
    writeJsonArray($file, $data);
}

/*
|--------------------------------------------------------------------------
| DAFTAR BUKU DAN STOK
|--------------------------------------------------------------------------
*/
// Mengambil daftar judul buku dan stok dari Data Buku.
function getDaftarBuku(): array
{
    $dataBukuFile = __DIR__ . '/data_buku.json';
    $dataBuku = readJsonArray($dataBukuFile);
    $daftarBuku = [];

    foreach ($dataBuku as $item) {
        $judul = trim((string) ($item['judul'] ?? ''));

        if ($judul === '') {
            continue;
        }

        $daftarBuku[$judul] = max(0, (int) ($item['stok'] ?? 0));
    }

    return $daftarBuku;
}

// Memastikan buku yang dipilih ada di Data Buku.
function isBukuValid(string $buku): bool
{
    return array_key_exists($buku, getDaftarBuku());
}

// Mengambil stok total buku berdasarkan judul.
function getStokBuku(string $buku): int
{
    $daftarBuku = getDaftarBuku();
    return $daftarBuku[$buku] ?? 0;
}

// Mengecek apakah transaksi peminjaman masih aktif.
function isPinjamanAktif(array $item): bool
{
    return empty($item['returned_at']);
}

// Mengecek apakah transaksi peminjaman masih bisa diperpanjang.
function canPerpanjangPeminjaman(array $item): bool
{
    return isPinjamanAktif($item) && empty($item['extended_at']);
}

// Menambahkan tujuh hari ke tanggal jatuh tempo peminjaman.
function tambahTujuhHariPeminjaman(string $tanggal): string
{
    try {
        return (new DateTimeImmutable($tanggal))->modify('+7 days')->format('Y-m-d');
    } catch (Exception $e) {
        return date('Y-m-d', strtotime('+7 days'));
    }
}

// Menghitung pinjaman aktif berdasarkan NIM.
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

// Menghitung pinjaman aktif berdasarkan judul buku.
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

// Menghitung sisa stok buku setelah dikurangi pinjaman aktif.
function getSisaStokBuku(array $data, string $buku): int
{
    if (!isBukuValid($buku)) {
        return 0;
    }

    return max(0, getStokBuku($buku) - countPinjamanAktifByBuku($data, $buku));
}

// Membuat daftar opsi buku untuk popup peminjaman.
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
// Membuat ID berikutnya untuk data laporan.
function nextLaporanId(array $data): int
{
    $max = 0;

    foreach ($data as $item) {
        $max = max($max, (int) ($item['id'] ?? 0));
    }

    return $max + 1;
}

// Menentukan status laporan berdasarkan jatuh tempo dan tanggal kembali.
function buildStatusLaporan(string $jatuhTempo, string $tglKembali = ''): string
{
    if ($tglKembali !== '') {
        return 'Dikembalikan';
    }

    return strtotime(todayDate()) > strtotime($jatuhTempo) ? 'Terlambat' : 'Belum Kembali';
}

// Mencari indeks laporan berdasarkan ID sumber peminjaman.
function cariIndexLaporanBySourceId(array $laporanData, string $sourceId): ?int
{
    foreach ($laporanData as $index => $item) {
        if (($item['source_id'] ?? '') === $sourceId) {
            return $index;
        }
    }

    return null;
}

// Membentuk data laporan dari data peminjaman.
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

// Menyinkronkan data peminjaman ke laporan transaksi.
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
// Menghitung status, keterlambatan, dan denda peminjaman.
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
// Menyediakan opsi jumlah data peminjaman per halaman.
function getPeminjamanPerPageOptions(): array
{
    return [5, 7, 10, 15, 20];
}

// Memvalidasi jumlah data peminjaman per halaman.
function normalizePeminjamanPerPage($value, int $default = 7): int
{
    $value = (int) $value;
    return in_array($value, getPeminjamanPerPageOptions(), true) ? $value : $default;
}

// Membuat URL pagination peminjaman.
function buildPageUrl(int $page, string $search, int $perPage): string
{
    $params = [
        'menu' => getCurrentMenuPeminjaman(),
        'page' => $page,
        'per_page' => $perPage,
    ];

    if ($search !== '') {
        $params['q'] = $search;
    }

    return '?' . http_build_query($params);
}

// Membuat daftar item pagination dengan titik pemisah.
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

    // Proses tambah peminjaman dari popup.
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

            redirectTo(['per_page' => normalizePeminjamanPerPage($_POST['per_page'] ?? 7)]);
        }

        $openPopup = true;
    }

    // Proses perpanjangan peminjaman satu kali per ID transaksi dan sinkronisasi laporan.
    if ($action === 'perpanjang_peminjaman') {
        $id = $_POST['id'] ?? '';
        $searchFromPost = trim($_POST['q'] ?? '');
        $pageFromPost = max(1, (int) ($_POST['page'] ?? 1));
        $perPageFromPost = normalizePeminjamanPerPage($_POST['per_page'] ?? 7);
        $laporanTransaksi = loadLaporanTransaksi($laporanFile);
        $dataBerubah = false;

        foreach ($dataPeminjaman as $index => $item) {
            if (($item['id'] ?? '') !== $id || !canPerpanjangPeminjaman($item)) {
                continue;
            }

            $item['tanggal_kembali'] = tambahTujuhHariPeminjaman((string) ($item['tanggal_kembali'] ?? todayDate()));
            $item['extended_at'] = todayDate();
            $dataPeminjaman[$index] = $item;

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

            $dataBerubah = true;
            break;
        }

        if ($dataBerubah) {
            savePeminjaman($dataFile, array_values($dataPeminjaman));
            saveLaporanTransaksi($laporanFile, $laporanTransaksi);
        }

        $redirectParams = ['menu' => 'peminjaman', 'page' => $pageFromPost, 'per_page' => $perPageFromPost];

        if ($searchFromPost !== '') {
            $redirectParams['q'] = $searchFromPost;
        }

        redirectTo($redirectParams);
    }

    // Proses pengembalian buku dan sinkronisasi ke laporan.
    if ($action === 'kembalikan_peminjaman') {
        $id = $_POST['id'] ?? '';
        $searchFromPost = trim($_POST['q'] ?? '');
        $pageFromPost = max(1, (int) ($_POST['page'] ?? 1));
        $perPageFromPost = normalizePeminjamanPerPage($_POST['per_page'] ?? 7);
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

        $redirectParams = ['menu' => 'peminjaman', 'page' => $pageFromPost, 'per_page' => $perPageFromPost];

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
$perPage = normalizePeminjamanPerPage($_GET['per_page'] ?? 7);
$filteredData = array_values(array_filter($dataPeminjaman, function (array $item) use ($search): bool {
    if ($search === '') {
        return true;
    }

    return stripos((string) ($item['nim'] ?? ''), $search) !== false
        || stripos((string) ($item['nama'] ?? ''), $search) !== false
        || stripos((string) ($item['buku'] ?? ''), $search) !== false;
}));

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

                    <form method="post" class="return-confirm-form">
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
