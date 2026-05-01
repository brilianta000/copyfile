<?php

/*
|--------------------------------------------------------------------------
| READ DATA BUKU
|--------------------------------------------------------------------------
| File ini hanya menyiapkan data untuk tampilan databuku.php.
*/
$dataPeminjamanBuku = loadDataPeminjamanBuku($peminjamanFile);
$dataBuku = loadDataBuku($dataFile);
$dataBukuLengkap = ensureDataBukuHasBorrowedTitles($dataBuku, $dataPeminjamanBuku);

if ($dataBukuLengkap !== $dataBuku) {
    $dataBuku = $dataBukuLengkap;
    saveDataBuku($dataFile, $dataBuku);
}

// Ambil filter pencarian, kategori, dan jumlah data per halaman.
$search = trim($_GET['q'] ?? '');
$kategoriFilter = $_GET['kategori'] ?? 'Semua';
$perPage = normalizeBukuPerPage($_GET['per_page'] ?? 7);

$kategoriOptions = array_values(array_unique(array_map(fn($item) => $item['kategori'], $dataBuku)));
sort($kategoriOptions);

// Filter data berdasarkan kategori dan kata kunci.
$filteredData = array_values(array_filter($dataBuku, function (array $item) use ($search, $kategoriFilter): bool {
    if ($kategoriFilter !== 'Semua' && ($item['kategori'] ?? '') !== $kategoriFilter) {
        return false;
    }

    if ($search === '') {
        return true;
    }

    return stripos((string) ($item['judul'] ?? ''), $search) !== false
        || stripos((string) ($item['penulis'] ?? ''), $search) !== false
        || stripos((string) ($item['penerbit'] ?? ''), $search) !== false;
}));

// Hitung pagination untuk tabel Data Buku.
$totalData = count($filteredData);
$totalPages = max(1, (int) ceil($totalData / $perPage));
$currentPage = min(max(1, (int) ($_GET['page'] ?? 1)), $totalPages);
$offset = ($currentPage - 1) * $perPage;
$pageData = array_slice($filteredData, $offset, $perPage);
$startDisplay = $totalData > 0 ? $offset + 1 : 0;
$endDisplay = $totalData > 0 ? min($offset + $perPage, $totalData) : 0;
