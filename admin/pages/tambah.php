  <!-- ===================== BREADCRUMB — FIXED ===================== -->
  <div class="breadcrumb-bar">
    <a href="#" class="breadcrumb-back-btn">
      <i class="bi bi-chevron-left"></i>
    </a>
    <span class="breadcrumb-title">Tambah Buku</span>
  </div>

  <!-- ===================== MAIN CONTENT ===================== -->
  <main class="tambah-content">
    <form method="POST" action="none">

        <!-- Panel Utama -->

        <div class="panel-card mb-3">
        <div class="panel-title">Input Data Buku</div>

        <label class="form-label-custom">Judul Buku</label>
        <input class="form-input-custom" type="text" placeholder="Masukkan judul buku..." />

        <label class="form-label-custom">Penerbit</label>
        <input class="form-input-custom" type="text" placeholder="Masukkan nama penerbit..." />

        <label class="form-label-custom">Tahun Terbit</label>
        <input class="form-input-custom" type="text" placeholder="cth: 2023" />

        <label class="form-label-custom">Tempat Terbit</label>
        <input class="form-input-custom" type="text" placeholder="cth: Jakarta" />

        <label class="form-label-custom">ISBN</label>
        <input class="form-input-custom" type="text" placeholder="cth: 978-3-16-148410-0" />

        <span class="kategori-label">Kategori :</span>
        <div class="kategori-grid">
            <label class="kategori-item">
            <input type="checkbox" class="kategori-checkbox" />
            <span class="kategori-text">Investasi</span>
            </label>
            <label class="kategori-item">
            <input type="checkbox" class="kategori-checkbox" />
            <span class="kategori-text">Sains</span>
            </label>
            <label class="kategori-item">
            <input type="checkbox" class="kategori-checkbox" />
            <span class="kategori-text">Sejarah</span>
            </label>
            <label class="kategori-item">
            <input type="checkbox" class="kategori-checkbox" />
            <span class="kategori-text">Teknologi</span>
            </label>
            <label class="kategori-item">
            <input type="checkbox" class="kategori-checkbox" />
            <span class="kategori-text">Novel</span>
            </label>
            <label class="kategori-item">
            <input type="checkbox" class="kategori-checkbox" />
            <span class="kategori-text">Psikologi</span>
            </label>
        </div>

        <span class="cover-section-title">Input Cover Buku</span>
        <div class="cover-upload-area">
            <span class="cover-upload-hint">Klik untuk upload cover</span>
        </div>

        <label class="synopsis-label">Deskripsi/Sinopsis :</label>
        <textarea class="synopsis-area" placeholder="Masukkan deskripsi atau sinopsis buku..."></textarea>

        <button class="btn-tambahkan">Tambahkan</button>
        </div>

        <!-- Panel Copy Buku -->
        <div class="panel-card">
        <div class="copy-panel-title">Copy Buku</div>
        <button class="btn-tambah-copy">Tambah</button>
        <div class="copy-list-row">
            <span class="copy-badge">b001</span>
            <span class="copy-badge">b002</span>
            <span class="copy-badge">b003</span>
            <span class="copy-badge">b004</span>
            <span class="copy-badge">b005</span>
        </div>
        </div>
    </form>

  </main>