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
        $sql = "
            SELECT
                b.id_buku,
                b.isbn,
                b.judul,
                b.pengarang,
                b.tahun_terbit,
                b.stok_tersedia,
                b.total_stok,
                b.cover_buku,
                b.id_kategori,
                b.id_penerbit,
                k.nama_kategori,
                p.nama_penerbit,
                COALESCE(pinjam.total_dipinjam, 0) AS total_dipinjam
            FROM buku b
            INNER JOIN kategori k ON k.id_kategori = b.id_kategori
            INNER JOIN penerbit p ON p.id_penerbit = b.id_penerbit
            LEFT JOIN (
                SELECT id_buku, COUNT(*) AS total_dipinjam
                FROM peminjaman
                WHERE status_pinjam IN ('borrowed', 'overdue')
                GROUP BY id_buku
            ) pinjam ON pinjam.id_buku = b.id_buku
            ORDER BY b.id_buku DESC
        ";

        $result = $this->conn->query($sql);
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $this->mapRow($row);
        }

        return $data;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT b.*, k.nama_kategori, p.nama_penerbit, 0 AS total_dipinjam
            FROM buku b
            INNER JOIN kategori k ON k.id_kategori = b.id_kategori
            INNER JOIN penerbit p ON p.id_penerbit = b.id_penerbit
            WHERE b.id_buku = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->mapRow($row) : null;
    }

    public function create(array $data): int
    {
        $idKategori = $this->findOrCreateKategori($data['kategori']);
        $idPenerbit = $this->findOrCreatePenerbit($data['penerbit'], $data['tempat_terbit'] ?? '');
        $isbn = $data['isbn'] !== '' ? $data['isbn'] : null;
        $cover = $data['cover_buku'] !== '' ? $data['cover_buku'] : null;
        $tahun = (int) $data['tahun'];
        $stok = max(0, (int) $data['stok']);

        $stmt = $this->conn->prepare("
            INSERT INTO buku
                (isbn, judul, pengarang, tahun_terbit, stok_tersedia, total_stok, cover_buku, id_kategori, id_penerbit)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'sssiiisii',
            $isbn,
            $data['judul'],
            $data['penulis'],
            $tahun,
            $stok,
            $stok,
            $cover,
            $idKategori,
            $idPenerbit
        );
        $stmt->execute();

        return (int) $this->conn->insert_id;
    }

    public function update(int $id, array $data): void
    {
        $idKategori = $this->findOrCreateKategori($data['kategori']);
        $idPenerbit = $this->findOrCreatePenerbit($data['penerbit']);
        $isbn = ($data['isbn'] ?? '') !== '' ? $data['isbn'] : null;
        $tahun = (int) $data['tahun'];
        $totalStok = max(0, (int) $data['stok']);
        $dipinjam = $this->countDipinjamById($id);
        $stokTersedia = max(0, $totalStok - $dipinjam);

        $stmt = $this->conn->prepare("
            UPDATE buku
            SET isbn = ?,
                judul = ?,
                pengarang = ?,
                tahun_terbit = ?,
                stok_tersedia = ?,
                total_stok = ?,
                id_kategori = ?,
                id_penerbit = ?
            WHERE id_buku = ?
        ");
        $stmt->bind_param(
            'sssiiiiii',
            $isbn,
            $data['judul'],
            $data['penulis'],
            $tahun,
            $stokTersedia,
            $totalStok,
            $idKategori,
            $idPenerbit,
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
        return array_column($result->fetch_all(MYSQLI_ASSOC), 'nama_kategori');
    }

    public function countByTitle(string $judul, ?int $exceptId = null): int
    {
        if ($exceptId) {
            $stmt = $this->conn->prepare('SELECT COUNT(*) AS total FROM buku WHERE LOWER(judul) = LOWER(?) AND id_buku <> ?');
            $stmt->bind_param('si', $judul, $exceptId);
        } else {
            $stmt = $this->conn->prepare('SELECT COUNT(*) AS total FROM buku WHERE LOWER(judul) = LOWER(?)');
            $stmt->bind_param('s', $judul);
        }

        $stmt->execute();
        return (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }

    private function findOrCreateKategori(string $nama): int
    {
        $nama = trim($nama) !== '' ? trim($nama) : 'Lainnya';
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

    private function findOrCreatePenerbit(string $nama, string $alamat = ''): int
    {
        $nama = trim($nama) !== '' ? trim($nama) : '-';
        $stmt = $this->conn->prepare('SELECT id_penerbit FROM penerbit WHERE nama_penerbit = ? LIMIT 1');
        $stmt->bind_param('s', $nama);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            return (int) $row['id_penerbit'];
        }

        $alamat = trim($alamat) !== '' ? trim($alamat) : null;
        $stmt = $this->conn->prepare('INSERT INTO penerbit (nama_penerbit, alamat_penerbit) VALUES (?, ?)');
        $stmt->bind_param('ss', $nama, $alamat);
        $stmt->execute();

        return (int) $this->conn->insert_id;
    }

    private function countDipinjamById(int $id): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM peminjaman WHERE id_buku = ? AND status_pinjam IN ('borrowed', 'overdue')");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        return (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }

    private function mapRow(array $row): array
    {
        $stokTotal = (int) ($row['total_stok'] ?? 0);
        $stokTersedia = (int) ($row['stok_tersedia'] ?? 0);
        $dipinjam = (int) ($row['total_dipinjam'] ?? max(0, $stokTotal - $stokTersedia));

        return [
            'id' => (int) $row['id_buku'],
            'id_buku' => (int) $row['id_buku'],
            'isbn' => $row['isbn'] ?? '',
            'judul' => $row['judul'] ?? '',
            'penulis' => $row['pengarang'] ?? '',
            'pengarang' => $row['pengarang'] ?? '',
            'penerbit' => $row['nama_penerbit'] ?? '',
            'tahun' => $row['tahun_terbit'] ?? '',
            'kategori' => $row['nama_kategori'] ?? '',
            'stok' => $stokTotal,
            'total_stok' => $stokTotal,
            'stok_tersedia' => $stokTersedia,
            'dipinjam' => $dipinjam,
            'cover' => $row['cover_buku'] ?? '',
        ];
    }
}
