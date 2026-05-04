<?php

class Peminjaman
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    // -------------------------------------------------------------------------
    // Helper tanggal
    // -------------------------------------------------------------------------

    public static function todayDate(): string
    {
        return date('Y-m-d');
    }

    public static function defaultTanggalKembali(): string
    {
        return date('Y-m-d', strtotime('+7 days'));
    }

    public static function tambahTujuhHari(string $tanggal): string
    {
        try {
            return (new DateTimeImmutable($tanggal))->modify('+7 days')->format('Y-m-d');
        } catch (Exception $e) {
            return date('Y-m-d', strtotime('+7 days'));
        }
    }

    // -------------------------------------------------------------------------
    // READ
    // -------------------------------------------------------------------------

    public function getAktif(string $search = ''): array
    {
        if ($search !== '') {
            $like = '%' . $search . '%';
            $stmt = $this->conn->prepare(
                'SELECT * FROM peminjaman
                 WHERE returned_at IS NULL
                   AND (nim LIKE ? OR nama LIKE ? OR buku LIKE ?)
                 ORDER BY created_at DESC'
            );
            $stmt->bind_param('sss', $like, $like, $like);
        } else {
            $stmt = $this->conn->prepare(
                'SELECT * FROM peminjaman
                 WHERE returned_at IS NULL
                 ORDER BY created_at DESC'
            );
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countAktifByNim(string $nim): int
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM peminjaman WHERE nim = ? AND returned_at IS NULL'
        );
        $stmt->bind_param('s', $nim);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_row()[0];
    }

    public function countAktifByBuku(string $buku): int
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM peminjaman WHERE buku = ? AND returned_at IS NULL'
        );
        $stmt->bind_param('s', $buku);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_row()[0];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM peminjaman WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // -------------------------------------------------------------------------
    // CREATE
    // -------------------------------------------------------------------------

    public function store(
        string $nim,
        string $nama,
        string $buku,
        string $tanggalPinjam,
        string $tanggalKembali
    ): int {
        $stmt = $this->conn->prepare(
            'INSERT INTO peminjaman (nim, nama, buku, tanggal_pinjam, tanggal_kembali)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssss', $nim, $nama, $buku, $tanggalPinjam, $tanggalKembali);
        $stmt->execute();
        return (int) $this->conn->insert_id;
    }

    // -------------------------------------------------------------------------
    // UPDATE: Perpanjang
    // -------------------------------------------------------------------------

    public function perpanjang(int $id): bool
    {
        $item = $this->findById($id);

        if ($item === null) {
            return false;
        }

        if (!empty($item['returned_at']) || !empty($item['extended_at'])) {
            return false;
        }

        $tanggalBaru = self::tambahTujuhHari((string) $item['tanggal_kembali']);
        $today       = self::todayDate();

        $stmt = $this->conn->prepare(
            'UPDATE peminjaman
             SET tanggal_kembali = ?, extended_at = ?
             WHERE id = ? AND returned_at IS NULL AND extended_at IS NULL'
        );
        $stmt->bind_param('ssi', $tanggalBaru, $today, $id);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    // -------------------------------------------------------------------------
    // UPDATE: Kembalikan
    // -------------------------------------------------------------------------

    public function kembalikan(int $id): bool
    {
        $today = self::todayDate();

        $stmt = $this->conn->prepare(
            'UPDATE peminjaman
             SET returned_at = ?
             WHERE id = ? AND returned_at IS NULL'
        );
        $stmt->bind_param('si', $today, $id);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    // -------------------------------------------------------------------------
    // Stok buku (tetap dari JSON karena buku belum migrasi ke DB)
    // -------------------------------------------------------------------------

    public static function getDaftarBuku(string $dataBukuFile): array
    {
        $daftarBuku = [];

        if (!file_exists($dataBukuFile)) {
            return $daftarBuku;
        }

        $data = json_decode((string) file_get_contents($dataBukuFile), true);

        foreach ((array) $data as $item) {
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
        return self::getDaftarBuku($dataBukuFile)[$buku] ?? 0;
    }

    public function getSisaStok(string $dataBukuFile, string $buku): int
    {
        $stokTotal = self::getStokBuku($dataBukuFile, $buku);
        $dipinjam  = $this->countAktifByBuku($buku);
        return max(0, $stokTotal - $dipinjam);
    }

    public function getOpsiBuku(string $dataBukuFile): array
    {
        $opsi = [];

        foreach (self::getDaftarBuku($dataBukuFile) as $judul => $stokTotal) {
            $opsi[] = [
                'judul'      => $judul,
                'stok'       => $this->getSisaStok($dataBukuFile, $judul),
                'stok_total' => $stokTotal,
            ];
        }

        return $opsi;
    }

    // -------------------------------------------------------------------------
    // Helper status & meta
    // -------------------------------------------------------------------------

    public static function canPerpanjang(array $item): bool
    {
        return empty($item['returned_at']) && empty($item['extended_at']);
    }

    public static function hitungMeta(array $item): array
    {
        try {
            $today          = new DateTimeImmutable('today');
            $tanggalKembali = new DateTimeImmutable($item['tanggal_kembali']);
            $returnedAt     = !empty($item['returned_at'])
                ? new DateTimeImmutable($item['returned_at'])
                : null;
        } catch (Exception $e) {
            return ['status' => 'Dipinjam', 'terlambat' => '-', 'denda' => 'Rp 0', 'late_days' => 0];
        }

        $pembanding = $returnedAt ?: $today;
        $lateDays   = $pembanding > $tanggalKembali
            ? (int) $tanggalKembali->diff($pembanding)->format('%a')
            : 0;

        if ($returnedAt) {
            return [
                'status'    => 'Dikembalikan',
                'terlambat' => $lateDays > 0 ? $lateDays . ' hari' : '-',
                'denda'     => 'Rp 0',
                'late_days' => 0,
            ];
        }

        $status = $today > $tanggalKembali ? 'Terlambat' : 'Dipinjam';

        return [
            'status'    => $status,
            'terlambat' => $lateDays > 0 ? $lateDays . ' hari' : '-',
            'denda'     => 'Rp ' . number_format($lateDays * 500, 0, ',', '.'),
            'late_days' => $lateDays,
        ];
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

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
        $items       = [];
        $lastWasDots = false;

        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === 1 || $i === $totalPages || abs($i - $currentPage) <= 1) {
                $items[]     = $i;
                $lastWasDots = false;
                continue;
            }

            if (!$lastWasDots) {
                $items[]     = '...';
                $lastWasDots = true;
            }
        }

        return $items;
    }
}
