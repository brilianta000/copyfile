<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Reservasi.php';

class ReservasiController
{
    public const MENU = 'reservasi';

    private static function getConn(): mysqli
    {
        static $conn = null;
        if ($conn === null) {
            $db   = new Database();
            $conn = $db->getConnection();
        }
        return $conn;
    }

    private static function model(): Reservasi
    {
        static $model = null;
        if ($model === null) {
            $model = new Reservasi(self::getConn());
        }
        return $model;
    }

    private static function getIdAdmin(): int
    {
        self::startSession();
        return (int) ($_SESSION['id_user'] ?? 0);
    }

    // =========================================================================
    // INDEX — data untuk view reservasi
    // =========================================================================

    public static function index(): array
    {
        self::startSession();

        // Tandai reservasi kadaluarsa otomatis setiap kali halaman dibuka
        self::model()->expireKadaluarsa();

        $search       = trim($_GET['q'] ?? '');
        $filterStatus = trim($_GET['status'] ?? '');
        $perPage      = Reservasi::normalizePerPage($_GET['per_page'] ?? 10);

        $dataReservasi = self::model()->getAll($search, $filterStatus);

        $totalData   = count($dataReservasi);
        $totalPages  = max(1, (int) ceil($totalData / $perPage));
        $currentPage = min(max(1, (int) ($_GET['page'] ?? 1)), $totalPages);
        $offset      = ($currentPage - 1) * $perPage;

        return [
            'dataReservasi'   => $dataReservasi,
            'pageData'        => array_slice($dataReservasi, $offset, $perPage),
            'search'          => $search,
            'filterStatus'    => $filterStatus,
            'perPage'         => $perPage,
            'totalData'       => $totalData,
            'totalPages'      => $totalPages,
            'currentPage'     => $currentPage,
            'offset'          => $offset,
            'startDisplay'    => $totalData > 0 ? $offset + 1 : 0,
            'endDisplay'      => $totalData > 0 ? min($offset + $perPage, $totalData) : 0,
            'paginationItems' => Reservasi::paginationItems($currentPage, $totalPages),
        ];
    }

    // =========================================================================
    // KONFIRMASI
    // =========================================================================

    public static function konfirmasi(array $post): void
    {
        self::startSession();
        $id = (int) ($post['id'] ?? 0);
        self::model()->konfirmasi($id, self::getIdAdmin());
        self::redirectBackToList($post);
    }

    // =========================================================================
    // BATALKAN
    // =========================================================================

    public static function batalkan(array $post): void
    {
        self::startSession();
        $id = (int) ($post['id'] ?? 0);
        self::model()->batalkan($id);
        self::redirectBackToList($post);
    }

    // =========================================================================
    // Redirect helpers
    // =========================================================================

    private static function redirectBackToList(array $post): void
    {
        $params = [
            'page'     => max(1, (int) ($post['page'] ?? 1)),
            'per_page' => Reservasi::normalizePerPage($post['per_page'] ?? 10),
        ];
        if (($s = trim($post['q'] ?? '')) !== '')       $params['q']      = $s;
        if (($f = trim($post['status'] ?? '')) !== '')  $params['status'] = $f;
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
