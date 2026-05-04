<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Peminjaman.php';

class PeminjamanController
{
    public const MENU = 'peminjaman';

    private static function getConn(): mysqli
    {
        static $conn = null;
        if ($conn === null) {
            $db   = new Database();
            $conn = $db->getConnection();
        }
        return $conn;
    }

    private static function model(): Peminjaman
    {
        static $model = null;
        if ($model === null) {
            $model = new Peminjaman(self::getConn());
        }
        return $model;
    }

    /**
     * Ambil id_admin yang sedang login dari session.
     * Login memakai tabel admin (id_admin disimpan di $_SESSION['id_user']).
     */
    private static function getIdAdmin(): int
    {
        self::startSession();
        return (int) ($_SESSION['id_user'] ?? 0);
    }

    // =========================================================================
    // INDEX — data untuk view datapeminjaman.php
    // =========================================================================

    public static function index(): array
    {
        self::startSession();

        $flash = $_SESSION['peminjaman_flash'] ?? [];
        unset($_SESSION['peminjaman_flash']);

        $openPopup = !empty($flash['open_popup']);
        $errors    = $flash['errors'] ?? [];
        $oldInput  = $flash['old_input'] ?? [
            'kode_anggota' => '',
            'id_buku'      => '',
        ];

        $search  = trim($_GET['q'] ?? '');
        $perPage = Peminjaman::normalizePerPage($_GET['per_page'] ?? 7);

        $dataPeminjaman = self::model()->getAktif($search);

        $totalData   = count($dataPeminjaman);
        $totalPages  = max(1, (int) ceil($totalData / $perPage));
        $currentPage = min(max(1, (int) ($_GET['page'] ?? 1)), $totalPages);
        $offset      = ($currentPage - 1) * $perPage;

        return [
            'openPopup'       => $openPopup,
            'errors'          => $errors,
            'oldInput'        => $oldInput,
            'dataPeminjaman'  => $dataPeminjaman,
            'search'          => $search,
            'perPage'         => $perPage,
            'totalData'       => $totalData,
            'totalPages'      => $totalPages,
            'currentPage'     => $currentPage,
            'offset'          => $offset,
            'pageData'        => array_slice($dataPeminjaman, $offset, $perPage),
            'startDisplay'    => $totalData > 0 ? $offset + 1 : 0,
            'endDisplay'      => $totalData > 0 ? min($offset + $perPage, $totalData) : 0,
            'paginationItems' => Peminjaman::paginationItems($currentPage, $totalPages),
            'opsiBuku'        => self::model()->getOpsiBuku(),
        ];
    }

    // =========================================================================
    // STORE — tambah peminjaman baru → INSERT
    // =========================================================================

    public static function store(array $post): void
    {
        self::startSession();

        $kodeAnggota = trim($post['kode_anggota'] ?? '');
        $idBuku      = (int) ($post['id_buku'] ?? 0);
        $idAdmin     = self::getIdAdmin();
        $errors      = [];
        $oldInput    = ['kode_anggota' => $kodeAnggota, 'id_buku' => $idBuku];

        // Validasi input wajib
        if ($kodeAnggota === '' || $idBuku === 0) {
            $errors[] = 'Semua field wajib diisi.';
        }

        // Cari anggota
        $anggota = null;
        if ($kodeAnggota !== '') {
            $anggota = self::model()->findAnggotaByKode($kodeAnggota);
            if ($anggota === null) {
                $errors[] = 'Kode anggota tidak ditemukan atau tidak aktif.';
            }
        }

        // Cari buku
        $buku = null;
        if ($idBuku > 0) {
            $buku = self::model()->findBuku($idBuku);
            if ($buku === null) {
                $errors[] = 'Buku tidak ditemukan.';
            } elseif ((int) $buku['stok_tersedia'] < 1) {
                $errors[] = 'Stok buku "' . htmlspecialchars($buku['judul'], ENT_QUOTES) . '" sedang habis.';
            }
        }

        // Cek limit 3 peminjaman aktif per anggota
        if ($anggota !== null && empty($errors)) {
            $aktif = self::model()->countAktifByAnggota((int) $anggota['id_anggota']);
            if ($aktif >= 3) {
                $errors[] = 'Anggota ini sudah meminjam 3 buku sekaligus.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['peminjaman_flash'] = [
                'open_popup' => true,
                'errors'     => $errors,
                'old_input'  => $oldInput,
            ];
            self::redirectTo(['per_page' => Peminjaman::normalizePerPage($post['per_page'] ?? 7)]);
        }

        $jatuhTempo = Peminjaman::defaultTanggalJatuhTempo();
        self::model()->store(
            (int) $anggota['id_anggota'],
            $idBuku,
            $idAdmin,
            $jatuhTempo
        );

        self::redirectTo(['per_page' => Peminjaman::normalizePerPage($post['per_page'] ?? 7)]);
    }

    // =========================================================================
    // EXTEND — perpanjang → UPDATE tanggal_jatuh_tempo
    // =========================================================================

    public static function extend(array $post): void
    {
        self::startSession();
        $id = (int) ($post['id'] ?? 0);
        self::model()->perpanjang($id);
        self::redirectBackToList($post);
    }

    // =========================================================================
    // RETURN — kembalikan → UPDATE tanggal_kembali + status
    // =========================================================================

    public static function returnBook(array $post): void
    {
        self::startSession();
        $id = (int) ($post['id'] ?? 0);
        self::model()->kembalikan($id);
        self::redirectBackToList($post);
    }

    // =========================================================================
    // Redirect helpers
    // =========================================================================

    private static function redirectBackToList(array $post): void
    {
        $params = [
            'page'     => max(1, (int) ($post['page'] ?? 1)),
            'per_page' => Peminjaman::normalizePerPage($post['per_page'] ?? 7),
        ];
        $search = trim($post['q'] ?? '');
        if ($search !== '') $params['q'] = $search;
        self::redirectTo($params);
    }

    public static function redirectTo(array $params = []): void
    {
        $params      = array_merge(['menu' => self::MENU], $params);
        $projectRoot = str_replace('\\', '/', dirname(__DIR__));
        $docRoot     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        $basePath    = rtrim(str_replace($docRoot, '', $projectRoot), '/');
        header('Location: ' . $basePath . '/admin/?' . http_build_query($params));
        exit;
    }

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
    }
}

// =============================================================================
// Helper functions global (dipanggil dari view)
// =============================================================================

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('todayDate')) {
    function todayDate(): string { return Peminjaman::todayDate(); }
}

if (!function_exists('defaultTanggalKembali')) {
    function defaultTanggalKembali(): string { return Peminjaman::defaultTanggalJatuhTempo(); }
}

if (!function_exists('getCurrentMenuPeminjaman')) {
    function getCurrentMenuPeminjaman(): string { return PeminjamanController::MENU; }
}

if (!function_exists('formatTanggal')) {
    function formatTanggal($date): string
    {
        if (empty($date)) return '-';
        $ts = strtotime((string) $date);
        return $ts ? date('d M Y', $ts) : '-';
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
    function getPeminjamanPerPageOptions(): array { return Peminjaman::perPageOptions(); }
}

if (!function_exists('buildPageUrl')) {
    function buildPageUrl(int $page, string $search, int $perPage): string
    {
        $params = ['menu' => getCurrentMenuPeminjaman(), 'page' => $page, 'per_page' => $perPage];
        if ($search !== '') $params['q'] = $search;
        return '?' . http_build_query($params);
    }
}
