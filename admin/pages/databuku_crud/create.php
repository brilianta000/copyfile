<?php

/*
|--------------------------------------------------------------------------
| PROSES CREATE DATA BUKU
|--------------------------------------------------------------------------
| File ini menerima POST dari tambah.php dan menyimpan buku baru.
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menentukan lokasi file JSON Data Buku untuk proses tambah.
function createBukuDataFile(): string
{
    return __DIR__ . '/../data_buku.json';
}

// Membaca data buku untuk proses tambah.
function loadCreateBukuData(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

// Menyimpan data buku setelah proses tambah.
function saveCreateBukuData(string $file, array $data): void
{
    file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// Membuat ID berikutnya untuk buku baru.
function nextCreateBukuId(array $data): int
{
    $maxId = 0;

    foreach ($data as $item) {
        $maxId = max($maxId, (int) ($item['id'] ?? 0));
    }

    return $maxId + 1;
}

// Mengarahkan kembali setelah proses tambah buku.
function redirectCreateBuku(string $query): void
{
    header('Location: ../../index.php' . $query);
    exit;
}

// Tolak akses langsung tanpa submit form.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirectCreateBuku('?menu=tambahbuku');
}

// Ambil input form untuk divalidasi dan disimpan.
$oldTambahBuku = [
    'judul' => trim($_POST['judul'] ?? ''),
    'penulis' => trim($_POST['penulis'] ?? ''),
    'penerbit' => trim($_POST['penerbit'] ?? ''),
    'tahun' => trim($_POST['tahun'] ?? ''),
    'tempat_terbit' => trim($_POST['tempat_terbit'] ?? ''),
    'isbn' => trim($_POST['isbn'] ?? ''),
    'kategori' => array_values(array_filter((array) ($_POST['kategori'] ?? []))),
    'sinopsis' => trim($_POST['sinopsis'] ?? ''),
    'stok' => max(0, (int) ($_POST['stok'] ?? 0)),
];

// Validasi field wajib dan aturan stok.
$errorsTambahBuku = [];

if ($oldTambahBuku['judul'] === '') {
    $errorsTambahBuku[] = 'Judul buku wajib diisi.';
}

if ($oldTambahBuku['penulis'] === '') {
    $errorsTambahBuku[] = 'Penulis wajib diisi.';
}

if ($oldTambahBuku['penerbit'] === '') {
    $errorsTambahBuku[] = 'Penerbit wajib diisi.';
}

if ($oldTambahBuku['tahun'] === '' || !ctype_digit($oldTambahBuku['tahun'])) {
    $errorsTambahBuku[] = 'Tahun terbit wajib berupa angka.';
}

if (empty($oldTambahBuku['kategori'])) {
    $errorsTambahBuku[] = 'Pilih minimal satu kategori.';
}

if ($oldTambahBuku['stok'] < 1) {
    $errorsTambahBuku[] = 'Stok buku minimal 1.';
}

// Baca data lama untuk cek duplikasi judul.
$dataBukuFile = createBukuDataFile();
$dataBuku = loadCreateBukuData($dataBukuFile);

foreach ($dataBuku as $item) {
    if (strcasecmp(trim((string) ($item['judul'] ?? '')), $oldTambahBuku['judul']) === 0) {
        $errorsTambahBuku[] = 'Judul buku sudah ada di Data Buku.';
        break;
    }
}

if (!empty($errorsTambahBuku)) {
    $_SESSION['tambah_buku_errors'] = $errorsTambahBuku;
    $_SESSION['tambah_buku_old'] = $oldTambahBuku;
    redirectCreateBuku('?menu=tambahbuku');
}

// Tambahkan buku baru setelah validasi berhasil.
$dataBuku[] = [
    'id' => nextCreateBukuId($dataBuku),
    'judul' => $oldTambahBuku['judul'],
    'penulis' => $oldTambahBuku['penulis'],
    'penerbit' => $oldTambahBuku['penerbit'],
    'tahun' => (int) $oldTambahBuku['tahun'],
    'kategori' => implode(', ', $oldTambahBuku['kategori']),
    'stok' => $oldTambahBuku['stok'],
    'tempat_terbit' => $oldTambahBuku['tempat_terbit'],
    'isbn' => $oldTambahBuku['isbn'],
    'sinopsis' => $oldTambahBuku['sinopsis'],
    'cover' => '',
];

saveCreateBukuData($dataBukuFile, $dataBuku);
unset($_SESSION['tambah_buku_errors'], $_SESSION['tambah_buku_old']);

redirectCreateBuku('?menu=databuku');
