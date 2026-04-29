<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - Perpustakaan POLIJE</title>
    <link rel="stylesheet" href="tambahbuku.css">
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

        <section class="form-section">
            <div class="form-card">
                <div class="card-header">
                    <button class="back-btn" onclick="window.location.href='databuku.php'"><i class="fas fa-chevron-left"></i></button>
                    <h3>Tambah Buku</h3>
                </div>
                <hr>
                <form action="#" method="POST" enctype="multipart/form-data">
                    <h4 class="section-label">Input Data Buku</h4>
                    <div class="input-group">
                        <label>Judul Buku</label>
                        <input type="text" placeholder="Masukkan judul buku...">
                    </div>
                    <div class="input-group">
                        <label>Penerbit</label>
                        <input type="text" placeholder="Masukkan nama penerbit...">
                    </div>
                    <div class="grid-2">
                        <div class="input-group"><label>Tahun Terbit</label><input type="text" placeholder="cth: 2023"></div>
                        <div class="input-group"><label>Tempat Terbit</label><input type="text" placeholder="cth: Jakarta"></div>
                    </div>
                    <div class="input-group"><label>ISBN</label><input type="text" placeholder="cth: 978-3-16-148410-0"></div>

                    <label class="label-inline">Kategori :</label>
                    <div class="category-grid">
                        <label><input type="checkbox"> Investasi</label>
                        <label><input type="checkbox"> Sains</label>
                        <label><input type="checkbox"> Sejarah</label>
                        <label><input type="checkbox"> Teknologi</label>
                        <label><input type="checkbox"> Novel</label>
                        <label><input type="checkbox"> Psikologi</label>
                    </div>

                    <div class="input-group">
                        <label>Input Cover Buku</label>
                        <div class="upload-area" onclick="document.getElementById('cover-file').click()">
                            <p>Klik untuk upload cover</p>
                            <input type="file" id="cover-file" hidden>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Deskripsi/Sinopsis :</label>
                        <textarea rows="4"></textarea>
                    </div>

                    <button type="submit" class="btn-submit">Tambahkan</button>
                </form>
            </div>
        </section>
    </main>
</div>
</body>
</html>