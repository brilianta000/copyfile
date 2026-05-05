<?php

require_once __DIR__ . '/../config/database.php';

class Peminjaman
{
    private static ?mysqli $conn = null;

    public static function db(): mysqli
    {
        if (!self::$conn) {
            self::$conn = (new Database())->getConnection();
        }

        return self::$conn;
    }

    public static function todayDate(): string
    {
        return date('Y-m-d');
    }

    public static function defaultTanggalKembali(): string
    {
        return date('Y-m-d', strtotime('+7 days'));
    }

    public static function updateOverdueStatuses(): void
    {
        self::db()->query(
            "UPDATE peminjaman
             SET status_pinjam = 'overdue'
             WHERE status_pinjam = 'borrowed'
               AND tanggal_kembali IS NULL
               AND tanggal_jatuh_tempo < CURDATE()"
        );
    }

    public static function loadActive(string $search = ''): array
    {
        self::updateOverdueStatuses();

        $sql = "SELECT p.id_peminjaman, p.tanggal_pinjam, p.tanggal_jatuh_tempo,
                       p.tanggal_kembali, p.status_pinjam, p.extended_at,
                       a.kode_anggota, a.nama_anggota, b.judul
                FROM peminjaman p
                JOIN anggota a ON a.id_anggota = p.id_anggota
                JOIN buku b ON b.id_buku = p.id_buku
                WHERE p.status_pinjam IN ('borrowed', 'overdue')";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (a.kode_anggota LIKE ? OR a.nama_anggota LIKE ? OR b.judul LIKE ?)";
            $like = '%' . $search . '%';
            $params = [$like, $like, $like];
        }

        $sql .= ' ORDER BY p.id_peminjaman DESC';

        if ($params) {
            $stmt = self::db()->prepare($sql);
            $stmt->bind_param('sss', ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = self::db()->query($sql);
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = self::mapPeminjamanRow($row);
        }

        return $rows;
    }

    public static function getOpsiBuku(): array
    {
        $result = self::db()->query(
            "SELECT judul, stok_tersedia, total_stok
             FROM buku
             ORDER BY judul ASC"
        );
        $opsi = [];

        while ($row = $result->fetch_assoc()) {
            $opsi[] = [
                'judul' => $row['judul'] ?? '',
                'stok' => max(0, (int) ($row['stok_tersedia'] ?? 0)),
                'stok_total' => max(0, (int) ($row['total_stok'] ?? 0)),
            ];
        }

        return $opsi;
    }

    public static function isBukuValid(string $buku): bool
    {
        return self::findBukuByJudul($buku) !== null;
    }

    public static function getSisaStokBuku(string $buku): int
    {
        $row = self::findBukuByJudul($buku);
        return $row ? max(0, (int) $row['stok_tersedia']) : 0;
    }

    public static function countAktifByNim(string $nim): int
    {
        $stmt = self::db()->prepare(
            "SELECT COUNT(*) AS total
             FROM peminjaman p
             JOIN anggota a ON a.id_anggota = p.id_anggota
             WHERE a.kode_anggota = ?
               AND p.status_pinjam IN ('borrowed', 'overdue')"
        );
        $stmt->bind_param('s', $nim);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return (int) ($row['total'] ?? 0);
    }

    public static function createBorrow(string $nim, string $nama, string $buku, int $adminId): void
    {
        $conn = self::db();
        $book = self::findBukuByJudul($buku);

        if (!$book || (int) $book['stok_tersedia'] < 1) {
            throw new RuntimeException('Stok buku tidak tersedia.');
        }

        $conn->begin_transaction();

        try {
            $anggotaId = self::findOrCreateAnggota($nim, $nama);
            $bookId = (int) $book['id_buku'];
            $tglPinjam = self::todayDate();
            $tglJatuhTempo = self::defaultTanggalKembali();
            $status = 'borrowed';

            $stmt = $conn->prepare(
                "INSERT INTO peminjaman
                    (id_anggota, id_buku, id_admin, tanggal_pinjam, tanggal_jatuh_tempo, status_pinjam)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('iiisss', $anggotaId, $bookId, $adminId, $tglPinjam, $tglJatuhTempo, $status);
            $stmt->execute();

            $stmt = $conn->prepare(
                "UPDATE buku
                 SET stok_tersedia = GREATEST(stok_tersedia - 1, 0)
                 WHERE id_buku = ?"
            );
            $stmt->bind_param('i', $bookId);
            $stmt->execute();

            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public static function extend(int $id): void
    {
        $stmt = self::db()->prepare(
            "SELECT tanggal_jatuh_tempo
             FROM peminjaman
             WHERE id_peminjaman = ?
               AND status_pinjam IN ('borrowed', 'overdue')
               AND extended_at IS NULL"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            return;
        }

        $newDueDate = self::tambahTujuhHari((string) $row['tanggal_jatuh_tempo']);
        $extendedAt = self::todayDate();
        $status = strtotime($newDueDate) < strtotime(self::todayDate()) ? 'overdue' : 'borrowed';

        $stmt = self::db()->prepare(
            "UPDATE peminjaman
             SET tanggal_jatuh_tempo = ?, extended_at = ?, status_pinjam = ?
             WHERE id_peminjaman = ?"
        );
        $stmt->bind_param('sssi', $newDueDate, $extendedAt, $status, $id);
        $stmt->execute();
    }

    public static function returnBook(int $id): void
    {
        $conn = self::db();
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare(
                "SELECT id_buku
                 FROM peminjaman
                 WHERE id_peminjaman = ?
                   AND status_pinjam IN ('borrowed', 'overdue')
                 FOR UPDATE"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            if (!$row) {
                $conn->commit();
                return;
            }

            $bookId = (int) $row['id_buku'];
            $today = self::todayDate();
            $status = 'returned';

            $stmt = $conn->prepare(
                "UPDATE peminjaman
                 SET tanggal_kembali = ?, status_pinjam = ?
                 WHERE id_peminjaman = ?"
            );
            $stmt->bind_param('ssi', $today, $status, $id);
            $stmt->execute();

            $stmt = $conn->prepare(
                "UPDATE buku
                 SET stok_tersedia = LEAST(stok_tersedia + 1, total_stok)
                 WHERE id_buku = ?"
            );
            $stmt->bind_param('i', $bookId);
            $stmt->execute();

            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public static function hideReports(array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));

        if (!$ids) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = self::db()->prepare(
            "UPDATE peminjaman
             SET laporan_hidden_at = NOW()
             WHERE id_peminjaman IN ($placeholders)"
        );
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
    }

    public static function reportRows(string $statusFilter, string $startDate, string $endDate, string $keyword, bool $includeHidden = false): array
    {
        self::updateOverdueStatuses();

        $sql = "SELECT p.id_peminjaman, p.tanggal_pinjam, p.tanggal_jatuh_tempo,
                       p.tanggal_kembali, p.status_pinjam,
                       a.kode_anggota, a.nama_anggota, b.judul,
                       d.hari_terlambat, d.jumlah_denda
                FROM peminjaman p
                JOIN anggota a ON a.id_anggota = p.id_anggota
                JOIN buku b ON b.id_buku = p.id_buku
                LEFT JOIN denda d ON d.id_peminjaman = p.id_peminjaman
                WHERE 1 = 1";
        $types = '';
        $params = [];

        if (!$includeHidden) {
            $sql .= ' AND p.laporan_hidden_at IS NULL';
        }

        if ($startDate !== '') {
            $sql .= ' AND p.tanggal_pinjam >= ?';
            $types .= 's';
            $params[] = $startDate;
        }

        if ($endDate !== '') {
            $sql .= ' AND p.tanggal_pinjam <= ?';
            $types .= 's';
            $params[] = $endDate;
        }

        if ($keyword !== '') {
            $sql .= ' AND (a.kode_anggota LIKE ? OR a.nama_anggota LIKE ? OR b.judul LIKE ?)';
            $like = '%' . $keyword . '%';
            $types .= 'sss';
            array_push($params, $like, $like, $like);
        }

        $sql .= ' ORDER BY p.id_peminjaman DESC';

        if ($params) {
            $stmt = self::db()->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = self::db()->query($sql);
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $mapped = self::mapReportRow($row);

            if ($statusFilter !== 'Semua' && $mapped['status'] !== $statusFilter) {
                continue;
            }

            $rows[] = $mapped;
        }

        return $rows;
    }

    public static function hitungMeta(array $item): array
    {
        $jatuhTempo = (string) ($item['tanggal_jatuh_tempo'] ?? $item['tanggal_kembali'] ?? '');
        $returnedAt = (string) ($item['returned_at'] ?? '');
        $status = self::statusLabel((string) ($item['status_pinjam'] ?? 'borrowed'), $returnedAt, $jatuhTempo);
        $lateDays = self::lateDays($jatuhTempo, $returnedAt);

        return [
            'status' => $status === 'Belum Kembali' ? 'Dipinjam' : $status,
            'terlambat' => $lateDays > 0 ? $lateDays . ' hari' : '-',
            'denda' => 'Rp ' . number_format($lateDays * 500, 0, ',', '.'),
            'late_days' => $lateDays,
        ];
    }

    public static function canPerpanjang(array $item): bool
    {
        return empty($item['returned_at']) && empty($item['extended_at']);
    }

    public static function tambahTujuhHari(string $tanggal): string
    {
        try {
            return (new DateTimeImmutable($tanggal))->modify('+7 days')->format('Y-m-d');
        } catch (Exception $e) {
            return self::defaultTanggalKembali();
        }
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

    public static function firstAdminId(): int
    {
        $result = self::db()->query('SELECT id_admin FROM admin ORDER BY id_admin ASC LIMIT 1');
        $row = $result->fetch_assoc();

        return max(1, (int) ($row['id_admin'] ?? 1));
    }

    private static function findBukuByJudul(string $judul): ?array
    {
        $stmt = self::db()->prepare('SELECT id_buku, stok_tersedia FROM buku WHERE judul = ? LIMIT 1');
        $stmt->bind_param('s', $judul);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    private static function findOrCreateAnggota(string $nim, string $nama): int
    {
        $stmt = self::db()->prepare('SELECT id_anggota, nama_anggota FROM anggota WHERE kode_anggota = ? LIMIT 1');
        $stmt->bind_param('s', $nim);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            if (trim((string) $row['nama_anggota']) !== $nama) {
                $stmt = self::db()->prepare('UPDATE anggota SET nama_anggota = ? WHERE id_anggota = ?');
                $id = (int) $row['id_anggota'];
                $stmt->bind_param('si', $nama, $id);
                $stmt->execute();
            }

            return (int) $row['id_anggota'];
        }

        $stmt = self::db()->prepare('INSERT INTO anggota (kode_anggota, nama_anggota) VALUES (?, ?)');
        $stmt->bind_param('ss', $nim, $nama);
        $stmt->execute();

        return (int) self::db()->insert_id;
    }

    private static function mapPeminjamanRow(array $row): array
    {
        return [
            'id' => (string) ($row['id_peminjaman'] ?? ''),
            'id_peminjaman' => (int) ($row['id_peminjaman'] ?? 0),
            'nim' => $row['kode_anggota'] ?? '',
            'nama' => $row['nama_anggota'] ?? '',
            'buku' => $row['judul'] ?? '',
            'tanggal_pinjam' => $row['tanggal_pinjam'] ?? '',
            'tanggal_kembali' => $row['tanggal_jatuh_tempo'] ?? '',
            'tanggal_jatuh_tempo' => $row['tanggal_jatuh_tempo'] ?? '',
            'returned_at' => $row['tanggal_kembali'] ?? null,
            'extended_at' => $row['extended_at'] ?? null,
            'status_pinjam' => $row['status_pinjam'] ?? 'borrowed',
        ];
    }

    private static function mapReportRow(array $row): array
    {
        $status = self::statusLabel((string) ($row['status_pinjam'] ?? ''), (string) ($row['tanggal_kembali'] ?? ''), (string) ($row['tanggal_jatuh_tempo'] ?? ''));
        $lateDays = self::lateDays((string) ($row['tanggal_jatuh_tempo'] ?? ''), (string) ($row['tanggal_kembali'] ?? ''));
        $denda = isset($row['jumlah_denda']) ? (float) $row['jumlah_denda'] : ($lateDays * 500);

        return [
            'id' => (int) ($row['id_peminjaman'] ?? 0),
            'source_id' => 'pjm_db_' . (int) ($row['id_peminjaman'] ?? 0),
            'tanggal' => $row['tanggal_pinjam'] ?? '',
            'peminjam' => $row['nama_anggota'] ?? '',
            'judul_buku' => $row['judul'] ?? '',
            'tgl_pinjam' => $row['tanggal_pinjam'] ?? '',
            'tgl_jatuh_tempo' => $row['tanggal_jatuh_tempo'] ?? '',
            'tgl_kembali' => $row['tanggal_kembali'] ?? '',
            'status' => $status,
            'hari_terlambat' => $lateDays,
            'denda_nominal' => $denda,
        ];
    }

    private static function statusLabel(string $status, string $tanggalKembali, string $jatuhTempo): string
    {
        if ($status === 'returned' || $tanggalKembali !== '') {
            return 'Dikembalikan';
        }

        if ($status === 'overdue' || ($jatuhTempo !== '' && strtotime($jatuhTempo) < strtotime(self::todayDate()))) {
            return 'Terlambat';
        }

        return 'Belum Kembali';
    }

    private static function lateDays(string $jatuhTempo, string $tanggalKembali = ''): int
    {
        if ($jatuhTempo === '') {
            return 0;
        }

        $endDate = $tanggalKembali !== '' ? $tanggalKembali : self::todayDate();
        $jatuhTempoTime = strtotime($jatuhTempo);
        $endTime = strtotime($endDate);

        if (!$jatuhTempoTime || !$endTime || $endTime <= $jatuhTempoTime) {
            return 0;
        }

        return (int) floor(($endTime - $jatuhTempoTime) / 86400);
    }
}
