<?php
$data = [
    ["nim"=>"123456","nama"=>"Fajar","buku"=>"Pemrograman Web","tgl"=>"01 Apr 2024","kembali"=>"10 Apr 2024","status"=>"konfirmasi"],
    ["nim"=>"123456","nama"=>"Fajar","buku"=>"Pemrograman Web","tgl"=>"01 Apr 2024","kembali"=>"10 Apr 2024","status"=>"selesai"]
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Perpustakaan</title>
    <link rel="stylesheet" href="stylereservasi.css">
</head>
<body>

<div class="container">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>ADMIN</h2>
        <ul>
            <li class="active">Dashboard</li>
            <li>Peminjaman</li>
            <li>Data Buku</li>
            <li>Laporan</li>
        </ul>
    </div>

    <!-- Main -->
    <div class="main">
        <div class="header">
            <h2>PERPUSTAKAAN POLIJE</h2>
        </div>

        <div class="content">
            <h3>Reservasi Buku</h3>

            <table>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Aksi</th>
                </tr>

                <?php foreach($data as $d): ?>
                <tr>
                    <td><?= $d['nim'] ?></td>
                    <td><?= $d['nama'] ?></td>
                    <td><?= $d['buku'] ?></td>
                    <td><?= $d['tgl'] ?></td>
                    <td><?= $d['kembali'] ?></td>
                    <td>
                        <?php if($d['status']=="konfirmasi"): ?>
                            <button class="btn green">Konfirmasi</button>
                        <?php else: ?>
                            <button class="btn blue">Selesai</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>
    </div>

</div>

</body>
</html>