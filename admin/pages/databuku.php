<div class="main-content">
    <div class="content-wrapper">

        <div class="page-header">
            <h2>Data Buku</h2>
        </div>

        <div class="card">

            <!-- Toolbar -->
            <div class="toolbar">
                <a href="tambahbuku.php" class="btn-add">+ Tambah Buku</a>

                <form method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Cari Buku..." value="<?= $search ?>">
                </form>

                <button class="btn-filter">Filter</button>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tahun</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = $offset + 1;
                        while($row = $result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['judul_buku'] ?></td>
                            <td><?= $row['penulis_buku'] ?></td>
                            <td><?= $row['penerbit'] ?></td>
                            <td><?= $row['tahun_terbit'] ?></td>
                            <td><?= $row['kategori'] ?></td>
                            <td><?= $row['stok'] ?></td>
                            <td>
                                <button onclick="edit(<?= $row['id'] ?>)">Edit</button>
                                <button onclick="detail(<?= $row['id'] ?>)">Detail</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="table-footer">
                <span>
                    Menampilkan <?= $offset+1 ?> - <?= min($offset+$limit, $totalData) ?> dari <?= $totalData ?> data
                </span>

                <div class="pagination">
                    <?php for($i=1;$i<=$totalPage;$i++): ?>
                        <a href="?p=<?= $i ?>&search=<?= $search ?>" 
                           class="<?= $i==$page?'active':'' ?>">
                           <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>

        </div>
    </div>
</div>