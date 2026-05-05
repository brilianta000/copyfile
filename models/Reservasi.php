<?php

class Reservasi
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    // =========================================================================
    // READ
    // =========================================================================

    /**
     * Ambil semua reservasi dengan JOIN ke anggota, buku, admin.
     */
    public function getAll(string $search = '', string $filterStatus = ''): array
    {
        $sql = 'SELECT
                    r.id_reservasi,
                    r.tanggal_reservasi,
                    r.tanggal_kadaluarsa,
                    r.status_reservasi,
                    a.id_anggota,
                    a.kode_anggota,
                    a.nama_anggota,
                    b.id_buku,
                    b.judul AS judul_buku,
                    adm.nama_admin
                FROM reservasi r
                JOIN anggota a ON r.id_anggota = a.id_anggota
                JOIN buku    b ON r.id_buku    = b.id_buku
                LEFT JOIN admin adm ON r.id_admin = adm.id_admin
                WHERE 1=1';

        $params = [];
        $types  = '';

        if ($filterStatus !== '') {
            $sql    .= ' AND r.status_reservasi = ?';
            $params[] = $filterStatus;
            $types  .= 's';
        }

        if ($search !== '') {
            $like     = '%' . $search . '%';
            $sql     .= ' AND (a.kode_anggota LIKE ? OR a.nama_anggota LIKE ? OR b.judul LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types   .= 'sss';
        }

        $sql .= ' ORDER BY r.created_at DESC';

        $stmt = $this->conn->prepare($sql);

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT r.*, a.kode_anggota, a.nama_anggota, b.judul AS judul_buku
             FROM reservasi r
             JOIN anggota a ON r.id_anggota = a.id_anggota
             JOIN buku    b ON r.id_buku    = b.id_buku
             WHERE r.id_reservasi = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // =========================================================================
    // UPDATE STATUS
    // =========================================================================

    /**
     * Konfirmasi reservasi — status: pending → confirmed, isi id_admin.
     */
    public function konfirmasi(int $id, int $idAdmin): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE reservasi
             SET status_reservasi = "confirmed", id_admin = ?
             WHERE id_reservasi = ? AND status_reservasi = "pending"'
        );
        $stmt->bind_param('ii', $idAdmin, $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Batalkan reservasi — status: pending/confirmed → cancelled.
     */
    public function batalkan(int $id): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE reservasi
             SET status_reservasi = "cancelled"
             WHERE id_reservasi = ? AND status_reservasi IN ("pending", "confirmed")'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Tandai reservasi yang sudah lewat kadaluarsa → expired.
     */
    public function expireKadaluarsa(): void
    {
        $today = date('Y-m-d');
        $stmt  = $this->conn->prepare(
            'UPDATE reservasi
             SET status_reservasi = "expired"
             WHERE tanggal_kadaluarsa < ? AND status_reservasi IN ("pending","confirmed")'
        );
        $stmt->bind_param('s', $today);
        $stmt->execute();
    }

    // =========================================================================
    // PAGINATION
    // =========================================================================

    public static function perPageOptions(): array { return [5, 10, 15, 20]; }

    public static function normalizePerPage($value, int $default = 10): int
    {
        $value = (int) $value;
        return in_array($value, self::perPageOptions(), true) ? $value : $default;
    }

    public static function paginationItems(int $currentPage, int $totalPages): array
    {
        $items = [];
        $last  = false;
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i === 1 || $i === $totalPages || abs($i - $currentPage) <= 1) {
                $items[] = $i;
                $last    = false;
            } elseif (!$last) {
                $items[] = '...';
                $last    = true;
            }
        }
        return $items;
    }
}
