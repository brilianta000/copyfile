 <!-- ===================== MAIN ===================== -->
  <div class="page-wrapper">

    <!-- PROFIL MAHASISWA -->
    <div class="section-title">Profil Mahasiswa</div>
    <div class="profile-card">
      <div class="profile-name">Andini Dia</div>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge-info">
          <i class="bi bi-person-badge"></i> NIM: 1928374650
        </span>
        <span class="badge-info">
          <i class="bi bi-mortarboard"></i> Sistem Informasi
        </span>
      </div>
    </div>

    <!-- BUKU YANG DIPINJAM -->
    <div class="section-title">Buku yang Dipinjam</div>

    <!-- Book 1 — Terlambat -->
    <div class="book-card">
      <div class="d-flex gap-3 align-items-start">
        <div class="book-cover"></div>
        <div class="flex-fill">
          <div class="d-flex justify-content-between align-items-start">
            <div class="book-title">Negeri Para Bedebah</div>
            <span class="badge-fine-blue">
              <i class="bi bi-receipt"></i> Denda: Rp1.500
            </span>
          </div>
          <div class="fine-box">
            <div>
              <span class="fine-label">Perhitungan denda</span>
              <span class="fine-calc">Rp500 × 1 hari keterlambatan</span>
            </div>
            <span class="badge-late">
              <i class="bi bi-exclamation-circle-fill"></i> Terlambat: 3 Hari
            </span>
          </div>
        </div>
      </div>
      <div class="date-row">
        <div>
          <div class="date-label">TGL PINJAM</div>
          <div class="date-val"><i class="bi bi-calendar3"></i> 12 Okt 2023</div>
        </div>
      </div>
    </div>

    <!-- Book 2 — Tepat Waktu -->
    <div class="book-card">
      <div class="d-flex gap-3 align-items-start">
        <div class="book-cover"></div>
        <div class="flex-fill">
          <div class="d-flex justify-content-between align-items-start">
            <div class="book-title">Sistem Basis Data Lanjut</div>
            <span class="badge-fine-blue">
              <i class="bi bi-receipt"></i> Denda: Rp0
            </span>
          </div>
          <div class="fine-box">
            <div>
              <span class="fine-label">Perhitungan denda</span>
              <span class="fine-calc">Rp500 × 1 hari keterlambatan</span>
            </div>
            <span class="badge-ontime">
              <i class="bi bi-check-circle-fill"></i> Batas: 5 hari
            </span>
          </div>
        </div>
      </div>
      <div class="date-row">
        <div>
          <div class="date-label">TGL PINJAM</div>
          <div class="date-val"><i class="bi bi-calendar3"></i> 12 Okt 2023</div>
        </div>
      </div>
    </div>

    <!-- Skeleton placeholder -->
    <div class="skeleton-card">
      <div class="skeleton-line"></div>
    </div>

    <!-- RESERVASI BUKU -->
    <div class="section-title">Reservasi Buku</div>
    <div class="reservasi-card">
      <div class="d-flex gap-3 align-items-start">
        <div class="book-cover"></div>
        <div class="flex-fill">
          <div class="d-flex justify-content-between align-items-start">
            <div class="book-title">Sistem Basis Data Lanjut</div>
            <span class="badge-waiting">Menunggu konfirmasi</span>
          </div>
          <div class="date-row" style="border-top: 1px dashed #e8e8e8; margin-top: 0.6rem; padding-top: 0.6rem;">
            <div>
              <div class="date-label">KEDALUARSA</div>
              <div class="date-val"><i class="bi bi-calendar3"></i> 08 Nov 2023</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- RIWAYAT PEMINJAMAN -->
    <div class="section-title">Riwayat Peminjaman</div>

    <!-- Stat Cards -->
    <div class="stat-row">
      <div class="stat-card">
        <div class="stat-label">Total Buku Dipinjam</div>
        <div class="stat-value">12</div>
        <div class="stat-sub">2 buku dipinjam semester ini</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Riwayat Keterlambatan</div>
        <div class="stat-value">3x</div>
        <div class="stat-sub">Perlu perhatian saat pengembalian</div>
      </div>
      <div class="stat-card-blue">
        <div class="stat-label-white">Total Denda</div>
        <div class="stat-value-white">Rp18.000</div>
        <div class="stat-sub-white">1 tagihan belum dibayar</div>
      </div>
    </div>

    <!-- History Book 1 — Terlambat, Belum Dibayar -->
    <div class="book-card">
      <div class="d-flex gap-3 align-items-start">
        <div class="book-cover"></div>
        <div class="flex-fill">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
            <div class="book-title">Negeri Para Bedebah</div>
            <div class="d-flex flex-column align-items-end gap-1">
              <span class="badge-late">
                <i class="bi bi-exclamation-circle-fill"></i> Terlambat: 3 Hari
              </span>
              <span class="badge-fine-blue">
                <i class="bi bi-receipt"></i> Denda: Rp18.000
              </span>
            </div>
          </div>
          <div class="fine-box">
            <div>
              <span class="fine-label">Perhitungan denda</span>
              <span class="fine-calc">Rp500 × 1 hari keterlambatan</span>
            </div>
            <span class="badge-unpaid">
              <i class="bi bi-x-circle-fill"></i> Belum Dibayar
            </span>
          </div>
        </div>
      </div>
      <div class="date-row">
        <div>
          <div class="date-label">TGL PINJAM</div>
          <div class="date-val"><i class="bi bi-calendar3"></i> 12 Okt 2023</div>
        </div>
        <i class="bi bi-arrow-right" style="color:#999; font-size:0.75rem;"></i>
        <div>
          <div class="date-label">TGL KEMBALI</div>
          <div class="date-val"><i class="bi bi-calendar3"></i> 22 Okt 2023</div>
        </div>
      </div>
    </div>

    <!-- History Book 2 — Tepat Waktu -->
    <div class="book-card">
      <div class="d-flex gap-3 align-items-start">
        <div class="book-cover"></div>
        <div class="flex-fill">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
            <div class="book-title">Sistem Basis Data Lanjut</div>
            <div class="d-flex flex-column align-items-end gap-1">
              <span class="badge-ontime">
                <i class="bi bi-check-circle-fill"></i> Terlambat: 0 Hari
              </span>
              <span class="badge-fine-blue">
                <i class="bi bi-receipt"></i> Denda: Rp0
              </span>
            </div>
          </div>
        </div>
      </div>
      <div class="date-row">
        <div>
          <div class="date-label">TGL PINJAM</div>
          <div class="date-val"><i class="bi bi-calendar3"></i> 01 Nov 2023</div>
        </div>
        <i class="bi bi-arrow-right" style="color:#999; font-size:0.75rem;"></i>
        <div>
          <div class="date-label">TGL KEMBALI</div>
          <div class="date-val"><i class="bi bi-calendar3"></i> 08 Nov 2023</div>
        </div>
      </div>
    </div>

  </div><!-- /page-wrapper -->
