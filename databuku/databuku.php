<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - Perpustakaan POLIJE</title>
    <link rel="stylesheet" href="databuku.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
    <nav class="sidebar">
        <div class="brand">
            <div class="logo-placeholder"><i class="fas fa-graduation-cap"></i></div>
            <h2>ADMIN</h2>
        </div>
        <ul class="nav-links">
            <li><a href="#"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="#"><i class="far fa-check-circle"></i> PEMINJAMAN</a></li>
            <li class="active"><a href="databuku.php"><i class="fas fa-book"></i> DATA BUKU</a></li>
            <li><a href="#"><i class="far fa-file-alt"></i> LAPORAN</a></li>
        </ul>
    </nav>

    <main class="content">
        <header>
            <h1 class="title-header">PERPUSTAKAAN POLIJE</h1>
            <div class="user-profile"><i class="fas fa-user-circle"></i></div>
        </header>

        <section class="main-card">
            <div class="header-table">
                <button class="back-btn"><i class="fas fa-chevron-left"></i></button>
                <h2>Data Buku</h2>
            </div>

            <div class="toolbar">
                <a href="tambahbuku.php" class="btn-add"><i class="fas fa-plus"></i> Tambah Buku</a>
                <div class="search-wrapper">
                    <input type="text" placeholder="Cari Buku...">
                    <i class="fas fa-search"></i>
                </div>
                <button class="btn-filter">Filter <i class="fas fa-chevron-down"></i></button>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tahun</th>
                            <th>Kategori :</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $page = isset($_GET['p']) ? $_GET['p'] : 1;
                        if($page == 1) {
                            $data = [
                                ["1.", "Laskar Pelangi", "Andrea Hirata", "Bentang Pustaka", "2005", "Fiksi", "24"],
                                ["2.", "Bumi Manusia", "Pramoedya Ananta Toer", "Hasta Mitra", "1980", "Fiksi Sejarah", "12"],
                                ["3.", "Filosofi Teras", "Henry Manampiring", "Kompas", "2018", "Non-Fiksi", "45"],
                                ["4.", "Atomic Habits", "James Clear", "Gramedia", "2019", "Edukasi", "89"],
                                ["5.", "Laut Bercerita", "Leila S. Chudori", "KPG", "2017", "Fiksi Sejarah", "18"],
                                ["6.", "Cantik Itu Luka", "Eka Kurniawan", "Gramedia", "2002", "Fiksi", "7"]
                            ];
                        } else {
                            $data = [
                                ["7.", "Negeri 5 Menara", "A. Fuadi", "Gramedia", "2009", "Fiksi", "30"],
                                ["8.", "Pulang", "Tere Liye", "Republika", "2015", "Novel", "15"]
                            ];
                        }
                        foreach($data as $row): ?>
                        <tr>
                            <td><?= $row[0] ?></td>
                            <td><?= $row[1] ?></td>
                            <td><?= $row[2] ?></td>
                            <td><?= $row[3] ?></td>
                            <td><?= $row[4] ?></td>
                            <td><?= $row[5] ?></td>
                            <td><?= $row[6] ?></td>
                            <td class="actions">
                                <button class="action-edit" onclick="openModal('modalEdit')"><i class="fas fa-pencil-alt"></i> Edit</button>
                                <button class="action-detail" onclick="openModal('modalDetail')"><i class="fas fa-search"></i> Detail</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-footer">
                <p>Menampilkan 1-7 dari 16 data</p>
                <div class="nav-page">
                    <a href="databuku.php?p=1" class="arrow"><i class="fas fa-chevron-left"></i></a>
                    <a href="databuku.php?p=1" class="page-num <?= $page==1?'active':'' ?>">1</a>
                    <a href="databuku.php?p=2" class="page-num <?= $page==2?'active':'' ?>">2</a>
                    <a href="#" class="page-num">5</a>
                    <span>...</span>
                    <a href="databuku.php?p=2" class="arrow"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </section>
    </main>
</div>

<div id="modalEdit" class="modal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Data Buku</h3>
            <span class="close" onclick="closeModal('modalEdit')">&times;</span>
        </div>
        <form>
            <div class="field"><label>Judul Buku</label><input type="text" value="Laskar Pelangi"></div>
            <div class="field"><label>Penulis</label><input type="text" value="Andrea Hirata"></div>
            <div class="field"><label>Penerbit</label><input type="text" value="Bentang Pustaka"></div>
            <div class="flex-row">
                <div class="field"><label>Tahun</label><input type="text" value="2005"></div>
                <div class="field">
                    <label>Kategori :</label>
                    <div class="modal-category-grid">
                        <label><input type="checkbox" checked> Fiksi</label>
                        <label><input type="checkbox"> Edukasi</label>
                        <label><input type="checkbox"> Novel</label>
                        <label><input type="checkbox"> Sejarah</label>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-del">Hapus Buku</button>
                <div class="right-btns">
                    <button type="button" class="btn-off" onclick="closeModal('modalEdit')">Batal</button>
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="modalDetail" class="modal">
    <div class="modal-box detail-box">
        <div class="modal-header">
            <h3>Detail Buku</h3>
            <span class="close" onclick="closeModal('modalDetail')">&times;</span>
        </div>
        <div class="detail-content">
            <div class="detail-item"><span>Judul Buku</span> : Laskar Pelangi</div>
            <div class="detail-item"><span>Penulis</span> : Andrea Hirata</div>
            <div class="detail-item"><span>Penerbit</span> : Bentang Pustaka</div>
            <div class="detail-item"><span>Tahun</span> : 2005</div>
            <div class="detail-item"><span>Kategori :</span> : Fiksi</div>
            <br>
            <div class="detail-item"><span>Stok Total</span> : 24</div>
            <div class="detail-item"><span>Dipinjam</span> : 5</div>
            <div class="detail-item"><span>Tersedia</span> : 19</div>
            <div class="detail-item"><span>Status</span> : <span class="badge-status">Tersedia</span></div>
        </div>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    window.onclick = function(event) {
        if (event.target.className === 'modal') event.target.style.display = "none";
    }
</script>
</body>
</html>