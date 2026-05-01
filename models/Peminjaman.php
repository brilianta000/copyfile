<?php

class Peminjaman
{
    public static function createId(): string
    {
        return uniqid('pjm_', true);
    }

    public static function todayDate(): string
    {
        return date('Y-m-d');
    }

    public static function defaultTanggalKembali(): string
    {
        return date('Y-m-d', strtotime('+7 days'));
    }

    public static function seed(): array
    {
        $today = new DateTimeImmutable('today');
        $rel = function (string $modifier) use ($today): string {
            return $today->modify($modifier)->format('Y-m-d');
        };

        return [
            ['id' => self::createId(), 'nim' => '123456', 'nama' => 'Fajar', 'buku' => 'Pemrograman Web', 'tanggal_pinjam' => $rel('-3 days'), 'tanggal_kembali' => $rel('+6 days'), 'returned_at' => null],
            ['id' => self::createId(), 'nim' => '123457', 'nama' => 'Dina', 'buku' => 'Basis Data', 'tanggal_pinjam' => $rel('-6 days'), 'tanggal_kembali' => $rel('+2 days'), 'returned_at' => null],
            ['id' => self::createId(), 'nim' => '123458', 'nama' => 'Budi', 'buku' => 'Jaringan Komputer', 'tanggal_pinjam' => $rel('-12 days'), 'tanggal_kembali' => $rel('-2 days'), 'returned_at' => null],
            ['id' => self::createId(), 'nim' => '123459', 'nama' => 'Andi', 'buku' => 'Sistem Informasi', 'tanggal_pinjam' => $rel('-18 days'), 'tanggal_kembali' => $rel('-10 days'), 'returned_at' => $rel('-8 days')],
            ['id' => self::createId(), 'nim' => '123460', 'nama' => 'Rani', 'buku' => 'Pemrograman Java', 'tanggal_pinjam' => $rel('-24 days'), 'tanggal_kembali' => $rel('-16 days'), 'returned_at' => $rel('-16 days')],
            ['id' => self::createId(), 'nim' => '123461', 'nama' => 'Eko', 'buku' => 'Teknik Elektro', 'tanggal_pinjam' => $rel('-14 days'), 'tanggal_kembali' => $rel('-3 days'), 'returned_at' => null],
            ['id' => self::createId(), 'nim' => '123462', 'nama' => 'Widya', 'buku' => 'Manajemen Keuangan', 'tanggal_pinjam' => $rel('-4 days'), 'tanggal_kembali' => $rel('+5 days'), 'returned_at' => null],
        ];
    }

    public static function ensureDirectoryExists(string $file): void
    {
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    public static function readJsonArray(string $file, array $fallback = []): array
    {
        if (!file_exists($file)) {
            return $fallback;
        }

        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) ? $data : $fallback;
    }

    public static function writeJsonArray(string $file, array $data): void
    {
        self::ensureDirectoryExists($file);
        file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public static function load(string $file): array
    {
        if (!file_exists($file)) {
            $defaultData = self::seed();
            self::save($file, $defaultData);
            return $defaultData;
        }

        return self::readJsonArray($file, self::seed());
    }

    public static function save(string $file, array $data): void
    {
        self::writeJsonArray($file, $data);
    }

    public static function loadLaporanTransaksi(string $file): array
    {
        return self::readJsonArray($file);
    }

    public static function saveLaporanTransaksi(string $file, array $data): void
    {
        self::writeJsonArray($file, $data);
    }

    public static function getDaftarBuku(string $dataBukuFile): array
    {
        $daftarBuku = [];

        foreach (self::readJsonArray($dataBukuFile) as $item) {
            $judul = trim((string) ($item['judul'] ?? ''));

            if ($judul !== '') {
                $daftarBuku[$judul] = max(0, (int) ($item['stok'] ?? 0));
            }
        }

        return $daftarBuku;
    }

    public static function isBukuValid(string $dataBukuFile, string $buku): bool
    {
        return array_key_exists($buku, self::getDaftarBuku($dataBukuFile));
    }

    public static function getStokBuku(string $dataBukuFile, string $buku): int
    {
        $daftarBuku = self::getDaftarBuku($dataBukuFile);
        return $daftarBuku[$buku] ?? 0;
    }

    public static function isAktif(array $item): bool
    {
        return empty($item['returned_at']);
    }

    public static function canPerpanjang(array $item): bool
    {
        return self::isAktif($item) && empty($item['extended_at']);
    }

    public static function tambahTujuhHari(string $tanggal): string
    {
        try {
            return (new DateTimeImmutable($tanggal))->modify('+7 days')->format('Y-m-d');
        } catch (Exception $e) {
            return date('Y-m-d', strtotime('+7 days'));
        }
    }

    public static function countAktifByNim(array $data, string $nim): int
    {
        $total = 0;
        $nim = trim($nim);

        foreach ($data as $item) {
            if (self::isAktif($item) && trim((string) ($item['nim'] ?? '')) === $nim) {
                $total++;
            }
        }

        return $total;
    }

    public static function countAktifByBuku(array $data, string $buku): int
    {
        $total = 0;
        $buku = trim($buku);

        foreach ($data as $item) {
            if (self::isAktif($item) && trim((string) ($item['buku'] ?? '')) === $buku) {
                $total++;
            }
        }

        return $total;
    }

    public static function getSisaStokBuku(string $dataBukuFile, array $data, string $buku): int
    {
        if (!self::isBukuValid($dataBukuFile, $buku)) {
            return 0;
        }

        return max(0, self::getStokBuku($dataBukuFile, $buku) - self::countAktifByBuku($data, $buku));
    }

    public static function getOpsiBuku(string $dataBukuFile, array $dataPeminjaman): array
    {
        $opsi = [];

        foreach (self::getDaftarBuku($dataBukuFile) as $judul => $stokTotal) {
            $opsi[] = [
                'judul' => $judul,
                'stok' => self::getSisaStokBuku($dataBukuFile, $dataPeminjaman, $judul),
                'stok_total' => $stokTotal,
            ];
        }

        return $opsi;
    }

    public static function nextLaporanId(array $data): int
    {
        $max = 0;

        foreach ($data as $item) {
            $max = max($max, (int) ($item['id'] ?? 0));
        }

        return $max + 1;
    }

    public static function buildStatusLaporan(string $jatuhTempo, string $tglKembali = ''): string
    {
        if ($tglKembali !== '') {
            return 'Dikembalikan';
        }

        return strtotime(self::todayDate()) > strtotime($jatuhTempo) ? 'Terlambat' : 'Belum Kembali';
    }

    public static function cariIndexLaporanBySourceId(array $laporanData, string $sourceId): ?int
    {
        foreach ($laporanData as $index => $item) {
            if (($item['source_id'] ?? '') === $sourceId) {
                return $index;
            }
        }

        return null;
    }

    public static function buatItemLaporan(array $item, ?int $laporanId = null): array
    {
        $tglKembaliReal = !empty($item['returned_at']) ? $item['returned_at'] : '';

        return [
            'id' => $laporanId,
            'source_id' => $item['id'],
            'tanggal' => $item['tanggal_pinjam'],
            'peminjam' => $item['nama'],
            'judul_buku' => $item['buku'],
            'tgl_pinjam' => $item['tanggal_pinjam'],
            'tgl_jatuh_tempo' => $item['tanggal_kembali'],
            'tgl_kembali' => $tglKembaliReal,
            'status' => self::buildStatusLaporan($item['tanggal_kembali'], $tglKembaliReal),
        ];
    }

    public static function sinkronkanKeLaporan(array $dataPeminjaman, array $laporanData): array
    {
        $changed = false;

        foreach ($dataPeminjaman as $item) {
            if (empty($item['id'])) {
                continue;
            }

            $index = self::cariIndexLaporanBySourceId($laporanData, $item['id']);
            $laporanItem = self::buatItemLaporan($item);

            if ($index === null) {
                $laporanItem['id'] = self::nextLaporanId($laporanData);
                array_unshift($laporanData, $laporanItem);
                $changed = true;
                continue;
            }

            $laporanItem['id'] = $laporanData[$index]['id'] ?? self::nextLaporanId($laporanData);

            if ($laporanData[$index] != $laporanItem) {
                $laporanData[$index] = $laporanItem;
                $changed = true;
            }
        }

        return [$laporanData, $changed];
    }

    public static function hitungMeta(array $item): array
    {
        try {
            $today = new DateTimeImmutable('today');
            $tanggalKembali = new DateTimeImmutable($item['tanggal_kembali']);
            $returnedAt = !empty($item['returned_at']) ? new DateTimeImmutable($item['returned_at']) : null;
        } catch (Exception $e) {
            return ['status' => 'Dipinjam', 'terlambat' => '-', 'denda' => 'Rp 0', 'late_days' => 0];
        }

        $pembanding = $returnedAt ?: $today;
        $lateDays = $pembanding > $tanggalKembali ? (int) $tanggalKembali->diff($pembanding)->format('%a') : 0;

        if ($returnedAt) {
            $status = 'Dikembalikan';
        } elseif ($today > $tanggalKembali) {
            $status = 'Terlambat';
        } else {
            $status = 'Dipinjam';
        }

        return [
            'status' => $status,
            'terlambat' => $lateDays > 0 ? $lateDays . ' hari' : '-',
            'denda' => 'Rp ' . number_format($lateDays * 500, 0, ',', '.'),
            'late_days' => $lateDays,
        ];
    }

    public static function perPageOptions(): array
    {
        return [5, 7, 10, 15, 20];
    }

    public static function normalizePerPage($value, int $default = 7): int
    {
        $value = (int) $value;
        return in_array($value, self::perPageOptions(), true) ? $value : $default;
    }

    public static function paginationItems(int $currentPage, int $totalPages): array
    {
        $items = [];
        $lastWasDots = false;

        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === 1 || $i === $totalPages || abs($i - $currentPage) <= 1) {
                $items[] = $i;
                $lastWasDots = false;
                continue;
            }

            if (!$lastWasDots) {
                $items[] = '...';
                $lastWasDots = true;
            }
        }

        return $items;
    }
}
