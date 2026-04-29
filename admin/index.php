<?php
$menu = $_GET['menu'] ?? 'akun';

$allowedMenus = [
    'akun' => [
        'file' => 'pages/akun.php',
        'styles' => ['../public/css/akun.css'],
    ],
    'peminjaman' => [
        'file' => 'pages/datapeminjam.php',
        'styles' => [
            '../public/css/datapeminjam.css',
            '../public/css/popuppeminjaman.css',
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
    ]
];

if (!isset($allowedMenus[$menu])) {
    $menu = 'akun';
}

$currentMenu = $menu;
$currentFile = $allowedMenus[$menu]['file'];
$currentStyles = $allowedMenus[$menu]['styles'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta charset="utf-8" />

    <!-- <link rel="stylesheet" href="css/tambah.css"> -->
    <link rel="stylesheet" href="../public/css/sidetop.css">

    <?php foreach ($currentStyles as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- ICON -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>

    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <main class="main-content">
        <?php include $currentFile; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>