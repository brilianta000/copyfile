<?php

// Inisialisasi session admin bila belum aktif.
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$menu = $_GET['menu'] ?? 'dashboard';

// Daftar menu yang boleh dirender oleh router admin.
$allowedMenus = [
    'akun' => [
        'file' => 'pages/akun.php',
        'styles' => ['../public/css/akun.css'],
    ],
    'peminjaman' => [
        'file' => 'pages/datapeminjaman.php',
        'styles' => [
            '../public/css/datapeminjam.css',
            '../public/css/popuppeminjaman.css',
        ],
    ],
    'databuku' => [
        'file' => 'pages/databuku.php',
        'styles' => [
            '../public/css/databuku.css',
        ],
    ],
    'laporan' => [
        'file' => 'pages/laporantransaksi.php',
        'styles' => [
            '../public/css/laporantransaksi.css',
        ],
    ],
    'tambahbuku' => [
        'file' => 'pages/tambah.php',
        'styles' => [
            '../public/css/tambah.css'
        ]
    ],
    'dashboard' => [
        'file' => 'pages/dash.php',
        'styles' => [
            '../public/css/dash.css'
        ]
    ],
    'reservasi' => [
        'file'   => 'pages/reservasi.php',
        'styles' => [
            '../public/css/reservasi.css',
        ],
    ],
];

// Fallback menu jika query menu tidak dikenal.
if (!isset($allowedMenus[$menu])) {
    $menu = 'dashboard';
}

$currentMenu = $menu;
$currentFile = $allowedMenus[$menu]['file'];
$currentStyles = $allowedMenus[$menu]['styles'];

// Membuat URL CSS lokal dengan versi filemtime agar cache browser ikut diperbarui.
function localCssHref(string $href): string
{
    $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $href));
    $version = $path && file_exists($path) ? filemtime($path) : time();

    return $href . '?v=' . $version;
}

// Jalur khusus logout akun agar redirect terjadi sebelum layout HTML dirender.
if ($currentMenu === 'akun' && isset($_GET['logout'])) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    header('Location: ../user/beranda.php');
    exit;
}

// Jalur khusus export laporan agar tidak ikut merender layout admin.
if ($currentMenu === 'laporan' && ($_GET['action'] ?? '') === 'export') {
    include $currentFile;
    exit;
}

// Jalur khusus POST laporan agar redirect hapus data terjadi sebelum layout HTML dirender.
if ($currentMenu === 'laporan' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    include $currentFile;
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta charset="utf-8" />

    <!-- <link rel="stylesheet" href="css/tambah.css"> -->
    <link rel="stylesheet" href="<?= htmlspecialchars(localCssHref('../public/css/sidetop.css'), ENT_QUOTES, 'UTF-8'); ?>">

    <?php foreach ($currentStyles as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars(localCssHref($style), ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>
<body>

    <!-- Layout navigasi utama admin -->
    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <!-- Konten halaman aktif sesuai menu -->
    <main class="main-content">
        <?php include $currentFile; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
