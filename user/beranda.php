<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Perpustakaan Polije</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../public/css/style.css?v=2">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- HERO -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-text">
            <h1>Selamat Datang di <br><span>Perpustakaan Polije</span></h1>
            <p>Temukan ribuan judul buku, e-book, jurnal, dan referensi lainnya
untuk mendukung proses belajar mengajar. Temukan inspirasi
dan pengetahuan tanpa batas.
            
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" placeholder="Cari judul buku atau penulis...">
                <button class="btn-search">Cari Buku</button>
            </div>
        </div>

        <div class="hero-image">
            <img src="gambar/bg-bku.png" alt="">
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="container features">
   <div class="feature-card">
        <div class="icon-box blue"><img src="gambar/koleksi.png"></div>
        <h3>Koleksi Lengkap</h3>
        <p>Ribuan buku, e-book, jurnal, dan e-proceding siap mendukung belajar.</p>
   </div>

   <div class="feature-card">
        <div class="icon-box blue"><img src="gambar/akses.png"></div>
        <h3>Akses E-Journal</h3>
        <p>Akses eksklusif ke berbagai
jurnal nasional maupun
internasional ternama
   </div>

   <div class="feature-card">
        <div class="icon-box blue"><img src="gambar/wifi.png"></div>
        <h3>Fasilitas Nyaman</h3>
        <p>Ruang baca tenang dilengkapi
WiFi kecepatan tinggi dan area
diskusi.</p>
   </div>

   <div class="feature-card">
        <div class="icon-box blue"><img src="gambar/layanan.png"></div>
        <h3>Peminjaman Mudah</h3>
        <p>Sistem peminjaman buku yang
mudah, cepat, dan terintegrasi
secara digital.</p>
   </div>
</section>

<!-- KOLEKSI TERBARU -->
<section class="container book-section">
    <div class="section-header">
        <h2>Koleksi Terbaru</h2>
        <a href="#" class="view-all">Lihat Semua <i class="fas fa-chevron-right" style="font-size: 10px;"></i></a>
    </div>

    <div class="book-grid">

        <!-- 6 ITEM -->
        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>The Quiet</h4><p>Erika</p></div></div>

        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>Filosofi Teras</h4><p>Henry</p></div></div>

        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>Bumi Manusia</h4><p>Pramoedya</p></div></div>

        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>Atomic Habits</h4><p>James</p></div></div>

        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>Laskar Pelangi</h4><p>Andrea Hirata</p></div></div>

        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>Pulang</h4><p>Tere Liye</p></div></div>

    </div>
</section>

<!-- TERPOPULER -->
<section class="container book-section">
    <div class="section-header">
        <h2>Buku Terpopuler</h2>
        <a href="#" class="view-all">Lihat Semua <i class="fas fa-chevron-right" style="font-size: 10px;"></i></a>
    </div>

    <div class="book-grid">

        <!-- 6 ITEM -->
        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>Negeri Bedebah</h4><p>Tere Liye</p></div></div>

        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>Pulang</h4><p>Tere Liye</p></div></div>

        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>Seni Bodo Amat</h4><p>Mark Manson</p></div></div>

        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>NKCTHI</h4><p>Marchella</p></div></div>

        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>Rich Dad Poor Dad</h4><p>Kiyosaki</p></div></div>

        <div class="book-card"><div class="book-cover"><img src="gambar/buku.png"></div><div class="book-info"><h4>Think Big</h4><p>Ben Carson</p></div></div>

    </div>
</section>

<?php include 'foot.php'; ?>

</body>
</html>