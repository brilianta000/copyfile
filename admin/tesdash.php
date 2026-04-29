<?php
// ====== DATA DUMMY (nanti bisa dari database) ======
$total_buku = 168;
$tersedia = 158;
$dipinjam = 10;

$logs = [
    ["id" => "e00000", "nama" => "nama saya", "judul" => "Algoritma", "status" => "Pinjam"],
    ["id" => "e00011", "nama" => "John", "judul" => "Novel", "status" => "Pinjam"],
    ["id" => "e00022", "nama" => "Wick", "judul" => "Database", "status" => "Kembali"],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/dash.css">
</head>
<body>

<div class="container my-4">

    <!-- STAT CARD -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <h3><?= $total_buku ?></h3>
                <p>Total buku</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card">
                <h3><?= $tersedia ?></h3>
                <p>Tersedia</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card">
                <h3><?= $dipinjam ?></h3>
                <p>Dipinjam</p>
            </div>
        </div>
    </div>

    <div class="row g-3">

        <!-- TABLE LOG -->
        <div class="col-md-8">
            <div class="card p-3">
                <h5 class="mb-3">Log Hari Ini</h5>

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Judul</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td><?= $log['nama'] ?></td>
                            <td><?= $log['judul'] ?></td>
                            <td>
                                <?php if ($log['status'] == "Pinjam"): ?>
                                    <span class="badge bg-success">Pinjam</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Kembali</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="col-md-4">

            <!-- CHART (dummy bar) -->
            <div class="card p-3 mb-3">
                <h6>Grafik peminjaman</h6>
                <div class="chart-box">
                    <div class="bar" style="height: 80px;"></div>
                    <div class="bar dark" style="height: 50px;"></div>
                    <div class="bar light" style="height: 25px;"></div>
                </div>
            </div>

            <!-- ADMIN -->
            <div class="card admin-card p-3">
                <h6>Nama admin</h6>
                <small>Jabatan</small>
            </div>

        </div>
    </div>

</div>

</body>
</html>