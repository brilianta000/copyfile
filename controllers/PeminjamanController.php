<?php

require_once __DIR__ . '/../models/Peminjaman.php';

class PeminjamanController
{
    public const MENU = 'peminjaman';

    public static function dataFile(): string
    {
        return __DIR__ . '/../admin/pages/data_peminjaman.json';
    }

    public static function laporanFile(): string
    {
        return __DIR__ . '/../admin/pages/data_laporan_transaksi.json';
    }

    public static function dataBukuFile(): string
    {
        return __DIR__ . '/../admin/pages/data_buku.json';
    }

    public static function index(): array
    {
        self::startSession();

        $flash = $_SESSION['peminjaman_flash'] ?? [];
        unset($_SESSION['peminjaman_flash']);

        $openPopup = !empty($flash['open_popup']);
        $errors = $flash['errors'] ?? [];
        $oldInput = $flash['old_input'] ?? [
            'nim' => '',
            'nama' => '',
            'buku' => '',
            'tgl_pinjam' => '',
            'tgl_kembali' => '',
        ];

        $dataPeminjamanSemua = Peminjaman::load(self::dataFile());
        $laporanTransaksi = Peminjaman::loadLaporanTransaksi(self::laporanFile());

        [$laporanTransaksi, $laporanChanged] = Peminjaman::sinkronkanKeLaporan($dataPeminjamanSemua, $laporanTransaksi);
        $dataPeminjaman = array_values(array_filter($dataPeminjamanSemua, [Peminjaman::class, 'isAktif']));
        $peminjamanChanged = count($dataPeminjaman) !== count($dataPeminjamanSemua);

        if ($laporanChanged) {
            Peminjaman::saveLaporanTransaksi(self::laporanFile(), $laporanTransaksi);
        }

        if ($peminjamanChanged) {
            Peminjaman::save(self::dataFile(), $dataPeminjaman);
        }

        $search = trim($_GET['q'] ?? '');
        $perPage = Peminjaman::normalizePerPage($_GET['per_page'] ?? 7);
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

        return [
            'openPopup' => $openPopup,
            'errors' => $errors,
            'oldInput' => $oldInput,
            'dataPeminjaman' => $dataPeminjaman,
            'search' => $search,
            'perPage' => $perPage,
            'filteredData' => $filteredData,
            'totalData' => $totalData,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'offset' => $offset,
            'pageData' => array_slice($filteredData, $offset, $perPage),
            'startDisplay' => $totalData > 0 ? $offset + 1 : 0,
            'endDisplay' => $totalData > 0 ? min($offset + $perPage, $totalData) : 0,
            'paginationItems' => Peminjaman::paginationItems($currentPage, $totalPages),
            'opsiBuku' => Peminjaman::getOpsiBuku(self::dataBukuFile(), $dataPeminjaman),
        ];
    }

    public static function store(array $post): void
    {
        self::startSession();

        $dataPeminjaman = array_values(array_filter(Peminjaman::load(self::dataFile()), [Peminjaman::class, 'isAktif']));
        $nim = trim($post['nim'] ?? '');
        $nama = trim($post['nama'] ?? '');
        $buku = trim($post['buku'] ?? '');
        $tglPinjam = Peminjaman::todayDate();
        $tglKembali = Peminjaman::defaultTanggalKembali();
        $errors = [];
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

        if ($buku !== '' && !Peminjaman::isBukuValid(self::dataBukuFile(), $buku)) {
            $errors[] = 'Buku yang dipilih tidak tersedia di daftar buku.';
        }

        if ($nim !== '' && Peminjaman::countAktifByNim($dataPeminjaman, $nim) >= 3) {
            $errors[] = 'Peminjaman gagal karena peminjam tersebut sedang meminjam 3 buku.';
        }

        if ($buku !== '' && Peminjaman::isBukuValid(self::dataBukuFile(), $buku)
            && Peminjaman::getSisaStokBuku(self::dataBukuFile(), $dataPeminjaman, $buku) < 1) {
            $errors[] = 'Peminjaman gagal karena stok buku "' . e($buku) . '" sedang habis.';
        }

        if (!empty($errors)) {
            $_SESSION['peminjaman_flash'] = [
                'open_popup' => true,
                'errors' => $errors,
                'old_input' => $oldInput,
            ];
            self::redirectTo(['per_page' => Peminjaman::normalizePerPage($post['per_page'] ?? 7)]);
        }

        $newData = [
            'id' => Peminjaman::createId(),
            'nim' => $nim,
            'nama' => $nama,
            'buku' => $buku,
            'tanggal_pinjam' => $tglPinjam,
            'tanggal_kembali' => $tglKembali,
            'returned_at' => null,
        ];

        array_unshift($dataPeminjaman, $newData);
        Peminjaman::save(self::dataFile(), $dataPeminjaman);

        $laporanTransaksi = Peminjaman::loadLaporanTransaksi(self::laporanFile());
        array_unshift($laporanTransaksi, Peminjaman::buatItemLaporan($newData, Peminjaman::nextLaporanId($laporanTransaksi)));
        Peminjaman::saveLaporanTransaksi(self::laporanFile(), $laporanTransaksi);

        self::redirectTo(['per_page' => Peminjaman::normalizePerPage($post['per_page'] ?? 7)]);
    }

    public static function extend(array $post): void
    {
        $dataPeminjaman = array_values(array_filter(Peminjaman::load(self::dataFile()), [Peminjaman::class, 'isAktif']));
        $laporanTransaksi = Peminjaman::loadLaporanTransaksi(self::laporanFile());
        $id = $post['id'] ?? '';
        $dataBerubah = false;

        foreach ($dataPeminjaman as $index => $item) {
            if (($item['id'] ?? '') !== $id || !Peminjaman::canPerpanjang($item)) {
                continue;
            }

            $item['tanggal_kembali'] = Peminjaman::tambahTujuhHari((string) ($item['tanggal_kembali'] ?? Peminjaman::todayDate()));
            $item['extended_at'] = Peminjaman::todayDate();
            $dataPeminjaman[$index] = $item;
            self::upsertLaporan($laporanTransaksi, $item);
            $dataBerubah = true;
            break;
        }

        if ($dataBerubah) {
            Peminjaman::save(self::dataFile(), $dataPeminjaman);
            Peminjaman::saveLaporanTransaksi(self::laporanFile(), $laporanTransaksi);
        }

        self::redirectBackToList($post);
    }

    public static function returnBook(array $post): void
    {
        $dataPeminjaman = array_values(array_filter(Peminjaman::load(self::dataFile()), [Peminjaman::class, 'isAktif']));
        $laporanTransaksi = Peminjaman::loadLaporanTransaksi(self::laporanFile());
        $id = $post['id'] ?? '';
        $dataBerubah = false;

        foreach ($dataPeminjaman as $index => $item) {
            if (($item['id'] ?? '') !== $id) {
                continue;
            }

            $item['returned_at'] = Peminjaman::todayDate();
            self::upsertLaporan($laporanTransaksi, $item);
            unset($dataPeminjaman[$index]);
            $dataBerubah = true;
            break;
        }

        if ($dataBerubah) {
            Peminjaman::save(self::dataFile(), array_values($dataPeminjaman));
            Peminjaman::saveLaporanTransaksi(self::laporanFile(), $laporanTransaksi);
        }

        self::redirectBackToList($post);
    }

    private static function upsertLaporan(array &$laporanTransaksi, array $item): void
    {
        $laporanIndex = Peminjaman::cariIndexLaporanBySourceId($laporanTransaksi, $item['id']);
        $laporanItem = Peminjaman::buatItemLaporan(
            $item,
            $laporanIndex !== null
                ? ($laporanTransaksi[$laporanIndex]['id'] ?? Peminjaman::nextLaporanId($laporanTransaksi))
                : Peminjaman::nextLaporanId($laporanTransaksi)
        );

        if ($laporanIndex === null) {
            array_unshift($laporanTransaksi, $laporanItem);
        } else {
            $laporanTransaksi[$laporanIndex] = $laporanItem;
        }
    }

    private static function redirectBackToList(array $post): void
    {
        $redirectParams = [
            'page' => max(1, (int) ($post['page'] ?? 1)),
            'per_page' => Peminjaman::normalizePerPage($post['per_page'] ?? 7),
        ];

        $search = trim($post['q'] ?? '');
        if ($search !== '') {
            $redirectParams['q'] = $search;
        }

        self::redirectTo($redirectParams);
    }

    public static function redirectTo(array $params = []): void
    {
        $params = array_merge(['menu' => self::MENU], $params);
        header('Location: ../../?' . http_build_query($params));
        exit;
    }

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }
}

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('todayDate')) {
    function todayDate(): string
    {
        return Peminjaman::todayDate();
    }
}

if (!function_exists('defaultTanggalKembali')) {
    function defaultTanggalKembali(): string
    {
        return Peminjaman::defaultTanggalKembali();
    }
}

if (!function_exists('getCurrentMenuPeminjaman')) {
    function getCurrentMenuPeminjaman(): string
    {
        return PeminjamanController::MENU;
    }
}

if (!function_exists('formatTanggal')) {
    function formatTanggal($date): string
    {
        if (empty($date)) {
            return '-';
        }

        $timestamp = strtotime((string) $date);
        return $timestamp ? date('d M Y', $timestamp) : '-';
    }
}

if (!function_exists('hitungMetaPeminjaman')) {
    function hitungMetaPeminjaman(array $item): array
    {
        return Peminjaman::hitungMeta($item);
    }
}

if (!function_exists('canPerpanjangPeminjaman')) {
    function canPerpanjangPeminjaman(array $item): bool
    {
        return Peminjaman::canPerpanjang($item);
    }
}

if (!function_exists('tambahTujuhHariPeminjaman')) {
    function tambahTujuhHariPeminjaman(string $tanggal): string
    {
        return Peminjaman::tambahTujuhHari($tanggal);
    }
}

if (!function_exists('getPeminjamanPerPageOptions')) {
    function getPeminjamanPerPageOptions(): array
    {
        return Peminjaman::perPageOptions();
    }
}

if (!function_exists('buildPageUrl')) {
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
}
