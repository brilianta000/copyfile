<?php

class Peminjaman
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    // =========================================================================
    // HELPER TANGGAL
    // =========================================================================

    public static function todayDate(): string
    {
        return date('Y-m-d');
    }

    public static function defaultTanggalJatuhTempo(): string
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

    // =========================================================================
    // ANGGOTA
    // =========================================================================

    /**
     * Cari anggota berdasarkan kode_anggota (NIM).
     */
    public function findAnggotaByKode(string $kode): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM anggota WHERE kode_anggota = ? AND status_anggota = "active" LIMIT 1'
        );
        $stmt->bind_param('s', $kode);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // =========================================================================
    // BUKU
    // =========================================================================

    /**
     * Ambil semua buku beserta stok_tersedia > 0, untuk dropdown form.
     */
    public function getOpsiBuku(): array
    {
        $stmt = $this->conn->prepare(
            'SELECT id_buku, judul, stok_tersedia FROM buku ORDER BY judul ASC'
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Cari satu buku berdasarkan id_buku.
     */
    public function findBuku(int $idBuku): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT * FROM buku WHERE id_buku = ? LIMIT 1'
        );
        $stmt->bind_param('i', $idBuku);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Kurangi stok_tersedia buku saat dipinjam.
     */
    private function kurangiStok(int $idBuku): void
    {
        $stmt = $this->conn->prepare(
            'UPDATE buku SET stok_tersedia = stok_tersedia - 1
             WHERE id_buku = ? AND stok_tersedia > 0'
        );
        $stmt->bind_param('i', $idBuku);
        $stmt->execute();
    }

    /**
     * Tambah stok_tersedia buku saat dikembalikan.
     */
    private function tambahStok(int $idBuku): void
    {
        $stmt = $this->conn->prepare(
            'UPDATE buku SET stok_tersedia = stok_tersedia + 1
             WHERE id_buku = ? AND stok_tersedia < total_stok'
        );
        $stmt->bind_param('i', $idBuku);
        $stmt->execute();
    }

    // =========================================================================
    // READ PEMINJAMAN
    // =========================================================================

    /**
     * Ambil semua peminjaman aktif (status bukan returned) dengan JOIN ke anggota & buku.
     */
    public function getAktif(string $search = ''): array
    {
        $sql = 'SELECT
                    p.id_peminjaman,
                    p.tanggal_pinjam,
                    p.tanggal_jatuh_tempo,
                    p.tanggal_kembali,
                    p.status_pinjam,
                    p.catatan,
                    a.id_anggota,
                    a.kode_anggota,
                    a.nama_anggota,
                    b.id_buku,
                    b.judul   AS judul_buku,
                    b.stok_tersedia
                FROM peminjaman p
                JOIN anggota a ON p.id_anggota = a.id_anggota
                JOIN buku    b ON p.id_buku    = b.id_buku
                WHERE p.status_pinjam != "returned"';

        if ($search !== '') {
            $like = '%' . $search . '%';
            $stmt = $this->conn->prepare($sql . '
                AND (a.kode_anggota LIKE ? OR a.nama_anggota LIKE ? OR b.judul LIKE ?)
                ORDER BY p.created_at DESC');
            $stmt->bind_param('sss', $like, $like, $like);
        } else {
            $stmt = $this->conn->prepare($sql . ' ORDER BY p.created_at DESC');
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Hitung peminjaman aktif milik satu anggota.
     */
    public function countAktifByAnggota(int $idAnggota): int
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM peminjaman
             WHERE id_anggota = ? AND status_pinjam != "returned"'
        );
        $stmt->bind_param('i', $idAnggota);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_row()[0];
    }

    /**
     * Ambil satu baris peminjaman.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT p.*, b.id_buku, b.judul AS judul_buku
             FROM peminjaman p
             JOIN buku b ON p.id_buku = b.id_buku
             WHERE p.id_peminjaman = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // =========================================================================
    // CREATE — tambah peminjaman baru
    // =========================================================================

    /**
     * INSERT peminjaman + kurangi stok buku.
     * Return id_peminjaman yang baru dibuat.
     */
    public function store(int $idAnggota, int $idBuku, int $idAdmin, string $tanggalJatuhTempo): int
    {
        $today = self::todayDate();

        $stmt = $this->conn->prepare(
            'INSERT INTO peminjaman
                (id_anggota, id_buku, id_admin, tanggal_pinjam, tanggal_jatuh_tempo, status_pinjam)
             VALUES (?, ?, ?, ?, ?, "borrowed")'
        );
        $stmt->bind_param('iiiss', $idAnggota, $idBuku, $idAdmin, $today, $tanggalJatuhTempo);
        $stmt->execute();

        $newId = (int) $this->conn->insert_id;

        // Kurangi stok buku
        $this->kurangiStok($idBuku);

        return $newId;
    }

    // =========================================================================
    // UPDATE — perpanjang
    // =========================================================================

    /**
     * Perpanjang jatuh tempo +7 hari.
     * Hanya bisa jika status masih borrowed/overdue dan belum pernah diperpanjang.
     * (Deteksi "sudah diperpanjang" via catatan — sederhana dan tidak butuh kolom baru)
     */
    public function perpanjang(int $id): bool
    {
        $item = $this->findById($id);

        if ($item === null || $item['status_pinjam'] === 'returned') {
            return false;
        }

        // Cek apakah sudah pernah diperpanjang (simpan flag di kolom catatan)
        if (!empty($item['catatan']) && str_contains((string) $item['catatan'], '[PERPANJANG]')) {
            return false;
        }

        $jatuhTempoBaru = self::tambahTujuhHari((string) $item['tanggal_jatuh_tempo']);
        $catatan        = trim(($item['catatan'] ?? '') . ' [PERPANJANG]');

        $stmt = $this->conn->prepare(
            'UPDATE peminjaman
             SET tanggal_jatuh_tempo = ?,
                 catatan             = ?,
                 status_pinjam       = "borrowed"
             WHERE id_peminjaman = ? AND status_pinjam != "returned"'
        );
        $stmt->bind_param('ssi', $jatuhTempoBaru, $catatan, $id);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    // =========================================================================
    // UPDATE — kembalikan
    // =========================================================================

    /**
     * Tandai buku dikembalikan:
     * - SET tanggal_kembali = today, status_pinjam = returned
     * - Tambah stok buku
     * - Jika terlambat, buat/update baris denda
     */
    public function kembalikan(int $id): bool
    {
        $item = $this->findById($id);

        if ($item === null || $item['status_pinjam'] === 'returned') {
            return false;
        }

        $today = self::todayDate();

        $stmt = $this->conn->prepare(
            'UPDATE peminjaman
             SET tanggal_kembali = ?,
                 status_pinjam   = "returned"
             WHERE id_peminjaman = ? AND status_pinjam != "returned"'
        );
        $stmt->bind_param('si', $today, $id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            return false;
        }

        // Tambah stok buku kembali
        $this->tambahStok((int) $item['id_buku']);

        // Hitung dan catat denda jika terlambat
        $this->prosesDesda($id, $item['tanggal_jatuh_tempo'], $today);

        return true;
    }

    /**
     * Buat atau perbarui baris denda jika ada keterlambatan.
     */
    private function prosesDesda(int $idPeminjaman, string $jatuhTempo, string $tanggalKembali): void
    {
        try {
            $jt       = new DateTimeImmutable($jatuhTempo);
            $kembali  = new DateTimeImmutable($tanggalKembali);
        } catch (Exception $e) {
            return;
        }

        if ($kembali <= $jt) {
            return; // Tidak terlambat
        }

        $hariTerlambat = (int) $jt->diff($kembali)->format('%a');

        // Ambil tarif denda dari tabel pengaturan
        $stmtTarif = $this->conn->prepare(
            'SELECT nilai FROM pengaturan WHERE kunci = "tarif_denda_per_hari" LIMIT 1'
        );
        $stmtTarif->execute();
        $rowTarif  = $stmtTarif->get_result()->fetch_row();
        $tarif     = $rowTarif ? (float) $rowTarif[0] : 1000.0;

        $jumlahDenda = $hariTerlambat * $tarif;

        // Upsert ke tabel denda
        $stmt = $this->conn->prepare(
            'INSERT INTO denda (id_peminjaman, hari_terlambat, jumlah_denda, status_denda)
             VALUES (?, ?, ?, "unpaid")
             ON DUPLICATE KEY UPDATE
                hari_terlambat = VALUES(hari_terlambat),
                jumlah_denda   = VALUES(jumlah_denda)'
        );
        $stmt->bind_param('iid', $idPeminjaman, $hariTerlambat, $jumlahDenda);
        $stmt->execute();
    }

    // =========================================================================
    // STATUS & META (untuk tampilan tabel)
    // =========================================================================

    public static function canPerpanjang(array $item): bool
    {
        if ($item['status_pinjam'] === 'returned') {
            return false;
        }
        return !str_contains((string) ($item['catatan'] ?? ''), '[PERPANJANG]');
    }

    public static function hitungMeta(array $item): array
    {
        try {
            $today      = new DateTimeImmutable('today');
            $jatuhTempo = new DateTimeImmutable($item['tanggal_jatuh_tempo']);
            $kembali    = !empty($item['tanggal_kembali'])
                ? new DateTimeImmutable($item['tanggal_kembali'])
                : null;
        } catch (Exception $e) {
            return ['status' => 'Dipinjam', 'terlambat' => '-', 'denda' => 'Rp 0', 'late_days' => 0];
        }

        if ($kembali !== null) {
            $lateDays = $kembali > $jatuhTempo
                ? (int) $jatuhTempo->diff($kembali)->format('%a')
                : 0;
            return [
                'status'    => 'Dikembalikan',
                'terlambat' => $lateDays > 0 ? $lateDays . ' hari' : '-',
                'denda'     => 'Rp 0',
                'late_days' => 0,
            ];
        }

        $lateDays = $today > $jatuhTempo
            ? (int) $jatuhTempo->diff($today)->format('%a')
            : 0;
        $status   = $today > $jatuhTempo ? 'Terlambat' : 'Dipinjam';

        return [
            'status'    => $status,
            'terlambat' => $lateDays > 0 ? $lateDays . ' hari' : '-',
            'denda'     => 'Rp ' . number_format($lateDays * 1000, 0, ',', '.'),
            'late_days' => $lateDays,
        ];
    }

    // =========================================================================
    // PAGINATION
    // =========================================================================

    public static function perPageOptions(): array { return [5, 7, 10, 15, 20]; }

    public static function normalizePerPage($value, int $default = 7): int
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
