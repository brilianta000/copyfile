<?php

/*
|--------------------------------------------------------------------------
| PROSES DELETE DATA BUKU
|--------------------------------------------------------------------------
| File ini disiapkan untuk proses hapus buku jika tombol hapus dipakai.
*/
// Menentukan lokasi file JSON Data Buku untuk proses hapus.
function deleteBukuDataFile(): string
{
    return __DIR__ . '/../data_buku.json';
}

// Membaca data buku untuk proses hapus.
function loadDeleteBukuData(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

// Menyimpan data buku setelah proses hapus.
function saveDeleteBukuData(string $file, array $data): void
{
    file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

// Memvalidasi jumlah data per halaman setelah hapus.
function normalizeDeleteBukuPerPage($value, int $default = 7): int
{
    $value = (int) $value;
    return in_array($value, [5, 7, 10, 15, 20], true) ? $value : $default;
}

// Membuat URL kembali setelah proses hapus buku.
function buildDeleteBukuUrl(int $page, string $search, string $kategori, int $perPage): string
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

// Mengarahkan kembali setelah proses hapus buku.
function redirectDeleteBuku(string $query): void
{
    header('Location: ../../index.php' . $query);
    exit;
}

// Tolak akses selain submit hapus buku.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['action'] ?? '') !== 'delete_buku') {
    redirectDeleteBuku('?menu=databuku');
}

// Baca data dan siapkan URL kembali sesuai filter aktif.
$dataFile = deleteBukuDataFile();
$dataBuku = loadDeleteBukuData($dataFile);
$perPage = normalizeDeleteBukuPerPage($_POST['per_page'] ?? 7);
$redirectUrl = buildDeleteBukuUrl(
    max(1, (int) ($_POST['page'] ?? 1)),
    trim($_POST['q'] ?? ''),
    $_POST['kategori_filter'] ?? 'Semua',
    $perPage
);
$id = (int) ($_POST['id'] ?? 0);

// Hapus data berdasarkan ID buku.
$dataBuku = array_values(array_filter($dataBuku, function (array $item) use ($id): bool {
    return (int) ($item['id'] ?? 0) !== $id;
}));

saveDeleteBukuData($dataFile, $dataBuku);
redirectDeleteBuku($redirectUrl);
