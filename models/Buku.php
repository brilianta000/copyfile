<?php

require_once __DIR__ . '/../config/database.php';

class Buku
{
    private mysqli $conn;

    public function __construct(?mysqli $conn = null)
    {
        $this->conn = $conn ?: (new Database())->getConnection();
    }

    public function all(): array
    {
        $sql = "SELECT b.id_buku, b.isbn, b.judul, b.pengarang, b.tahun_terbit,
                       b.stok_tersedia, b.total_stok, b.cover_buku,
                       k.nama_kategori, p.nama_penerbit
                FROM buku b
                JOIN kategori k ON k.id_kategori = b.id_kategori
                JOIN penerbit p ON p.id_penerbit = b.id_penerbit
                ORDER BY b.id_buku DESC";

        $result = $this->conn->query($sql);
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $this->mapRow($row);
        }

        return $rows;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT b.id_buku, b.isbn, b.judul, b.pengarang, b.tahun_terbit,
                    b.stok_tersedia, b.total_stok, b.cover_buku,
                    k.nama_kategori, p.nama_penerbit
             FROM buku b
             JOIN kategori k ON k.id_kategori = b.id_kategori
             JOIN penerbit p ON p.id_penerbit = b.id_penerbit
             WHERE b.id_buku = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->mapRow($row) : null;
    }

    public function create(array $data): int
    {
        $kategoriId = $this->findOrCreateKategori((string) ($data['kategori'] ?? 'Umum'));
        $penerbitId = $this->findOrCreatePenerbit((string) ($data['penerbit'] ?? 'Tidak diketahui'));
        $isbn = $this->nullableString($data['isbn'] ?? null);
        $judul = trim((string) ($data['judul'] ?? ''));
        $pengarang = trim((string) ($data['penulis'] ?? $data['pengarang'] ?? ''));
        $tahun = $this->nullableYear($data['tahun'] ?? null);
        $stok = max(0, (int) ($data['stok'] ?? $data['total_stok'] ?? 0));
        $cover = $this->nullableString($data['cover_buku'] ?? $data['cover'] ?? null);

        $stmt = $this->conn->prepare(
            "INSERT INTO buku
                (isbn, judul, pengarang, tahun_terbit, stok_tersedia, total_stok, cover_buku, id_kategori, id_penerbit)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'sssiiisii',
            $isbn,
            $judul,
            $pengarang,
            $tahun,
            $stok,
            $stok,
            $cover,
            $kategoriId,
            $penerbitId
        );
        $stmt->execute();

        return (int) $this->conn->insert_id;
    }

    public function update(int $id, array $data): void
    {
        $current = $this->find($id);

        if (!$current) {
            return;
        }

        $kategori = trim((string) ($data['kategori'] ?? '')) ?: (string) $current['kategori'];
        $kategoriId = $this->findOrCreateKategori($kategori);
        $penerbitId = $this->findOrCreatePenerbit((string) ($data['penerbit'] ?? $current['penerbit']));
        $isbn = $this->nullableString($data['isbn'] ?? $current['isbn']);
        $judul = trim((string) ($data['judul'] ?? $current['judul']));
        $pengarang = trim((string) ($data['penulis'] ?? $data['pengarang'] ?? $current['penulis']));
        $tahun = $this->nullableYear($data['tahun'] ?? $current['tahun']);
        $totalStok = max(0, (int) ($data['stok'] ?? $current['total_stok']));
        $dipinjam = $this->activeBorrowCountById($id);
        $stokTersedia = max(0, $totalStok - $dipinjam);
        $cover = $this->nullableString($data['cover_buku'] ?? $data['cover'] ?? $current['cover']);

        $stmt = $this->conn->prepare(
            "UPDATE buku
             SET isbn = ?, judul = ?, pengarang = ?, tahun_terbit = ?,
                 stok_tersedia = ?, total_stok = ?, cover_buku = ?,
                 id_kategori = ?, id_penerbit = ?
             WHERE id_buku = ?"
        );
        $stmt->bind_param(
            'sssiiisiii',
            $isbn,
            $judul,
            $pengarang,
            $tahun,
            $stokTersedia,
            $totalStok,
            $cover,
            $kategoriId,
            $penerbitId,
            $id
        );
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->conn->prepare('DELETE FROM buku WHERE id_buku = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function kategoriOptions(): array
    {
        $result = $this->conn->query('SELECT nama_kategori FROM kategori ORDER BY nama_kategori ASC');
        $kategori = [];

        while ($row = $result->fetch_assoc()) {
            $nama = trim((string) ($row['nama_kategori'] ?? ''));

            if ($nama !== '') {
                $kategori[] = $nama;
            }
        }

        return $kategori;
    }

    public function countByTitle(string $judul, ?int $exceptId = null): int
    {
        $judul = trim($judul);

        if ($exceptId) {
            $stmt = $this->conn->prepare('SELECT COUNT(*) AS total FROM buku WHERE LOWER(judul) = LOWER(?) AND id_buku <> ?');
            $stmt->bind_param('si', $judul, $exceptId);
        } else {
            $stmt = $this->conn->prepare('SELECT COUNT(*) AS total FROM buku WHERE LOWER(judul) = LOWER(?)');
            $stmt->bind_param('s', $judul);
        }

        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return (int) ($row['total'] ?? 0);
    }

    private function findOrCreateKategori(string $nama): int
    {
        $nama = trim($nama) ?: 'Umum';
        $stmt = $this->conn->prepare('SELECT id_kategori FROM kategori WHERE nama_kategori = ? LIMIT 1');
        $stmt->bind_param('s', $nama);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            return (int) $row['id_kategori'];
        }

        $stmt = $this->conn->prepare('INSERT INTO kategori (nama_kategori) VALUES (?)');
        $stmt->bind_param('s', $nama);
        $stmt->execute();

        return (int) $this->conn->insert_id;
    }

    private function findOrCreatePenerbit(string $nama): int
    {
        $nama = trim($nama) ?: 'Tidak diketahui';
        $stmt = $this->conn->prepare('SELECT id_penerbit FROM penerbit WHERE nama_penerbit = ? LIMIT 1');
        $stmt->bind_param('s', $nama);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            return (int) $row['id_penerbit'];
        }

        $stmt = $this->conn->prepare('INSERT INTO penerbit (nama_penerbit) VALUES (?)');
        $stmt->bind_param('s', $nama);
        $stmt->execute();

        return (int) $this->conn->insert_id;
    }

    private function activeBorrowCountById(int $id): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM peminjaman
             WHERE id_buku = ? AND status_pinjam IN ('borrowed', 'overdue')"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return (int) ($row['total'] ?? 0);
    }

    private function nullableString($value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }

    private function nullableYear($value): ?int
    {
        $value = trim((string) ($value ?? ''));
        return ctype_digit($value) ? (int) $value : null;
    }

    private function mapRow(array $row): array
    {
        $id = (int) ($row['id_buku'] ?? 0);
        $totalStok = max(0, (int) ($row['total_stok'] ?? 0));
        $stokTersedia = max(0, (int) ($row['stok_tersedia'] ?? 0));
        $dipinjam = max(0, $totalStok - $stokTersedia);
        $cover = (string) ($row['cover_buku'] ?? '');

        return [
            'id' => $id,
            'id_buku' => $id,
            'isbn' => $row['isbn'] ?? '',
            'judul' => $row['judul'] ?? '',
            'penulis' => $row['pengarang'] ?? '',
            'pengarang' => $row['pengarang'] ?? '',
            'penerbit' => $row['nama_penerbit'] ?? '',
            'tahun' => $row['tahun_terbit'] ?? '',
            'tahun_terbit' => $row['tahun_terbit'] ?? '',
            'kategori' => $row['nama_kategori'] ?? '',
            'stok' => $totalStok,
            'total_stok' => $totalStok,
            'stok_tersedia' => $stokTersedia,
            'dipinjam' => $dipinjam,
            'cover' => $cover,
            'cover_buku' => $cover,
        ];
    }
}
