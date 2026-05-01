<?php

/*
|--------------------------------------------------------------------------
| PROSES UPDATE DATA BUKU
|--------------------------------------------------------------------------
| File ini menerima POST edit dari modal Data Buku.
*/
// Menentukan lokasi file JSON Data Buku untuk proses edit.
function updateBukuDataFile(): string
{
    return __DIR__ . '/../data_buku.json';
}

// Membaca data buku untuk proses edit.
function loadUpdateBukuData(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

// Menyimpan data buku setelah proses edit.
function saveUpdateBukuData(string $file, array $data): void
{
    file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// Memvalidasi jumlah data per halaman setelah edit.
function normalizeUpdateBukuPerPage($value, int $default = 7): int
{
    $value = (int) $value;
    return in_array($value, [5, 7, 10, 15, 20], true) ? $value : $default;
}

// Membuat URL kembali setelah proses edit buku.
function buildUpdateBukuUrl(int $page, string $search, string $kategori, int $perPage): string
{
    $params = ['menu' => 'databuku', 'page' => $page, 'per_page' => $perPage];

    if ($search !== '') {
        $params['q'] = $search;
    }

    if ($kategori !== 'Semua') {
        $params['kategori'] = $kategori;
    }

    return '?' . http_build_query($params);
}

// Mengarahkan kembali setelah proses edit buku.
function redirectUpdateBuku(string $query): void
{
    header('Location: ../../index.php' . $query);
    exit;
}

// Tolak akses selain submit edit buku.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['action'] ?? '') !== 'edit_buku') {
    redirectUpdateBuku('?menu=databuku');
}

// Baca data dan siapkan URL kembali sesuai filter aktif.
$dataFile = updateBukuDataFile();
$dataBuku = loadUpdateBukuData($dataFile);
$perPage = normalizeUpdateBukuPerPage($_POST['per_page'] ?? 7);
$redirectUrl = buildUpdateBukuUrl(
    max(1, (int) ($_POST['page'] ?? 1)),
    trim($_POST['q'] ?? ''),
    $_POST['kategori_filter'] ?? 'Semua',
    $perPage
);

// Cari buku berdasarkan ID lalu update nilainya.
$id = (int) ($_POST['id'] ?? 0);

foreach ($dataBuku as $index => $item) {
    if ((int) ($item['id'] ?? 0) !== $id) {
        continue;
    }

    $dataBuku[$index] = array_merge($item, [
        'id' => $id,
        'judul' => trim($_POST['judul'] ?? ''),
        'penulis' => trim($_POST['penulis'] ?? ''),
        'penerbit' => trim($_POST['penerbit'] ?? ''),
        'tahun' => (int) ($_POST['tahun'] ?? 0),
        'kategori' => trim($_POST['kategori'] ?? ''),
        'stok' => max(0, (int) ($_POST['stok'] ?? 0)),
    ]);
    saveUpdateBukuData($dataFile, $dataBuku);
    break;
}

redirectUpdateBuku($redirectUrl);
