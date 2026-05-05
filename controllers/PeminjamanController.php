<?php

require_once __DIR__ . '/../models/Peminjaman.php';

class PeminjamanController
{
    public const MENU = 'peminjaman';

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

        $search = trim($_GET['q'] ?? '');
        $perPage = Peminjaman::normalizePerPage($_GET['per_page'] ?? 7);
        $dataPeminjaman = Peminjaman::loadActive($search);
        $filteredData = $dataPeminjaman;

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
            'opsiBuku' => Peminjaman::getOpsiBuku(),
        ];
    }

    public static function store(array $post): void
    {
        self::startSession();

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

        if ($buku !== '' && !Peminjaman::isBukuValid($buku)) {
            $errors[] = 'Buku yang dipilih tidak tersedia di daftar buku.';
        }

        if ($nim !== '' && Peminjaman::countAktifByNim($nim) >= 3) {
            $errors[] = 'Peminjaman gagal karena peminjam tersebut sedang meminjam 3 buku.';
        }

        if ($buku !== '' && Peminjaman::isBukuValid($buku)
            && Peminjaman::getSisaStokBuku($buku) < 1) {
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

        $adminId = (int) ($_SESSION['id_admin'] ?? $_SESSION['id_user'] ?? Peminjaman::firstAdminId());

        try {
            Peminjaman::createBorrow($nim, $nama, $buku, $adminId);
        } catch (Throwable $e) {
            $_SESSION['peminjaman_flash'] = [
                'open_popup' => true,
                'errors' => ['Peminjaman gagal disimpan ke database.'],
                'old_input' => $oldInput,
            ];
            self::redirectTo(['per_page' => Peminjaman::normalizePerPage($post['per_page'] ?? 7)]);
        }

        self::redirectTo(['per_page' => Peminjaman::normalizePerPage($post['per_page'] ?? 7)]);
    }

    public static function extend(array $post): void
    {
        Peminjaman::extend((int) ($post['id'] ?? 0));
        self::redirectBackToList($post);
    }

    public static function returnBook(array $post): void
    {
        Peminjaman::returnBook((int) ($post['id'] ?? 0));
        self::redirectBackToList($post);
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
