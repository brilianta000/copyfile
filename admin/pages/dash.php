<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Peminjaman.php';

$conn = (new Database())->getConnection();
Peminjaman::updateOverdueStatuses();

function dashScalar(mysqli $conn, string $sql): int
{
    $result = $conn->query($sql);
    $row = $result ? $result->fetch_assoc() : [];

    return (int) array_values($row ?: [0])[0];
}

$total_buku = dashScalar($conn, 'SELECT COUNT(*) FROM buku');
$total_stok = dashScalar($conn, 'SELECT COALESCE(SUM(total_stok), 0) FROM buku');
$tersedia = dashScalar($conn, 'SELECT COALESCE(SUM(stok_tersedia), 0) FROM buku');
$dipinjam = dashScalar($conn, "SELECT COUNT(*) FROM peminjaman WHERE status_pinjam IN ('borrowed', 'overdue')");
$terlambat = dashScalar($conn, "SELECT COUNT(*) FROM peminjaman WHERE status_pinjam = 'overdue'");
$total_anggota = dashScalar($conn, 'SELECT COUNT(*) FROM anggota');

$logs_recent = [];
$result = $conn->query(
    "SELECT p.id_peminjaman, p.tanggal_pinjam, p.tanggal_jatuh_tempo,
            p.tanggal_kembali, p.status_pinjam,
            a.nama_anggota, b.judul
     FROM peminjaman p
     JOIN anggota a ON a.id_anggota = p.id_anggota
     JOIN buku b ON b.id_buku = p.id_buku
     WHERE p.laporan_hidden_at IS NULL
     ORDER BY p.id_peminjaman DESC
     LIMIT 10"
);

while ($result && $row = $result->fetch_assoc()) {
    $status = 'Belum Kembali';

    if (($row['status_pinjam'] ?? '') === 'returned' || !empty($row['tanggal_kembali'])) {
        $status = 'Dikembalikan';
    } elseif (($row['status_pinjam'] ?? '') === 'overdue') {
        $status = 'Terlambat';
    }

    $logs_recent[] = [
        'source_id' => 'pjm_db_' . (int) ($row['id_peminjaman'] ?? 0),
        'peminjam' => $row['nama_anggota'] ?? '',
        'judul_buku' => $row['judul'] ?? '',
        'tgl_pinjam' => $row['tanggal_pinjam'] ?? '',
        'tgl_jatuh_tempo' => $row['tanggal_jatuh_tempo'] ?? '',
        'status' => $status,
    ];
}

$bulan_labels = [];
$bulan_data = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("-$i months");
    $key = date('Y-m', $ts);
    $label = date('M', $ts);
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM peminjaman WHERE DATE_FORMAT(tanggal_pinjam, '%Y-%m') = ?");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $bulan_labels[] = $label;
    $bulan_data[] = (int) ($row['total'] ?? 0);
}
$chart_max = max(max($bulan_data), 1);

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
$admin_nama = $_SESSION['nama'] ?? 'Administrator';
$admin_jabatan = $_SESSION['jabatan'] ?? 'Admin Perpustakaan';
?>

<div class="dash-container">

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-value"><?= $total_buku ?></span>
                    <span class="stat-label">Total Judul Buku</span>
                </div>
                <div class="stat-icon icon-blue"><i class="bi bi-book-half"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-value"><?= $tersedia ?></span>
                    <span class="stat-label">Tersedia</span>
                </div>
                <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-value"><?= $dipinjam ?></span>
                    <span class="stat-label">Sedang Dipinjam</span>
                </div>
                <div class="stat-icon icon-orange"><i class="bi bi-bookmark-check"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-value"><?= $terlambat ?></span>
                    <span class="stat-label">Terlambat</span>
                </div>
                <div class="stat-icon icon-red"><i class="bi bi-exclamation-circle"></i></div>
            </div>
        </div>

        <div class="dash-main">

            <div class="card card-log">
                <div class="card-header-row">
                    <h6 class="card-title">Transaksi Terbaru</h6>
                    <span class="date-badge"><?= date('d M Y') ?></span>
                </div>
                <?php if (empty($logs_recent)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Belum ada transaksi.</p>
                </div>
                <?php else: ?>
                <div class="table-wrap">
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Peminjam</th>
                                <th>Judul Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs_recent as $log): ?>
                            <?php
                                $statusClass = match($log['status']) {
                                    'Dikembalikan' => 'badge-kembali',
                                    'Terlambat' => 'badge-terlambat',
                                    default => 'badge-pinjam',
                                };
                                $idShort = substr((string) $log['source_id'], 0, 14) . '...';
                            ?>
                            <tr>
                                <td class="id-col" title="<?= htmlspecialchars((string) $log['source_id']) ?>">
                                    <?= htmlspecialchars($idShort) ?>
                                </td>
                                <td><?= htmlspecialchars((string) $log['peminjam']) ?></td>
                                <td><?= htmlspecialchars((string) $log['judul_buku']) ?></td>
                                <td><?= htmlspecialchars((string) ($log['tgl_pinjam'] ?: '-')) ?></td>
                                <td><?= htmlspecialchars((string) ($log['tgl_jatuh_tempo'] ?: '-')) ?></td>
                                <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars((string) $log['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <div class="dash-right">

                <div class="card card-chart">
                    <h6 class="card-title">Peminjaman 6 Bulan Terakhir</h6>
                    <div class="chart-bars">
                        <?php foreach ($bulan_labels as $i => $lbl): ?>
                        <?php $pct = $chart_max > 0 ? round(($bulan_data[$i] / $chart_max) * 100) : 0; ?>
                        <div class="chart-col">
                            <div class="chart-bar-wrap">
                                <div class="chart-bar" style="height:<?= max($pct, 4) ?>%">
                                    <?php if ($bulan_data[$i] > 0): ?>
                                    <span class="bar-val"><?= $bulan_data[$i] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="bar-label"><?= htmlspecialchars($lbl) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card card-admin">
                    <div class="admin-avatar"><i class="bi bi-person-circle"></i></div>
                    <div class="admin-info">
                        <span class="admin-name"><?= htmlspecialchars($admin_nama) ?></span>
                        <span class="admin-role"><?= htmlspecialchars($admin_jabatan) ?></span>
                    </div>
                    <div class="admin-stats">
                        <div class="admin-stat-item">
                            <span class="admin-stat-val"><?= $dipinjam + dashScalar($conn, "SELECT COUNT(*) FROM peminjaman WHERE status_pinjam = 'returned'") ?></span>
                            <span class="admin-stat-lbl">Total Transaksi</span>
                        </div>
                        <div class="admin-stat-item">
                            <span class="admin-stat-val"><?= $total_anggota ?></span>
                            <span class="admin-stat-lbl">Anggota</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

</div>
