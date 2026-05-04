-- Migration: Tabel peminjaman
-- Jalankan di db_perpustakaan

CREATE TABLE IF NOT EXISTS peminjaman (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nim         VARCHAR(20)  NOT NULL,
    nama        VARCHAR(100) NOT NULL,
    buku        VARCHAR(255) NOT NULL,
    tanggal_pinjam  DATE     NOT NULL,
    tanggal_kembali DATE     NOT NULL,
    extended_at DATE         DEFAULT NULL,
    returned_at DATE         DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nim (nim),
    INDEX idx_returned (returned_at),
    INDEX idx_buku (buku(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
