<!-- Popup form tambah peminjaman baru -->
<div class="popup-overlay <?= $openPopup ? 'active' : ''; ?>" id="popupPeminjaman">
    <div class="popup-box">
        <div class="popup-header">
            <span>Tambah Peminjaman</span>
            <button type="button" class="popup-close" id="closePopupPeminjaman" aria-label="Tutup">&times;</button>
        </div>

        <form method="post" action="actions/peminjaman/store.php">
            <div class="popup-body">
                <?php if (!empty($errors)): ?>
                    <div class="popup-alert">
                        <?= e(implode(' ', $errors)); ?>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="action" value="add_peminjaman">
                <input type="hidden" name="per_page" value="<?= (int) ($perPage ?? 7); ?>">

                <!-- Kode anggota (NIM / kode_anggota) -->
                <div class="form-group">
                    <label for="popup_kode_anggota">Kode Anggota (NIM)</label>
                    <input
                        type="text"
                        id="popup_kode_anggota"
                        name="kode_anggota"
                        value="<?= e($oldInput['kode_anggota'] ?? ''); ?>"
                        autocomplete="off"
                        placeholder="Contoh: M001"
                        required
                    >
                </div>

                <!-- Dropdown buku (value = id_buku) -->
                <div class="form-group">
                    <label for="popup_buku">Buku</label>
                    <select id="popup_buku" name="id_buku" required>
                        <option value="">Pilih Buku</option>
                        <?php foreach ($opsiBuku as $opsi): ?>
                            <?php
                            $idBuku    = (int) ($opsi['id_buku'] ?? 0);
                            $judul     = $opsi['judul'] ?? '';
                            $stok      = (int) ($opsi['stok_tersedia'] ?? 0);
                            $selected  = ((int) ($oldInput['id_buku'] ?? 0) === $idBuku) ? 'selected' : '';
                            $disabled  = $stok < 1 ? 'disabled' : '';
                            ?>
                            <option value="<?= $idBuku; ?>" <?= $selected; ?> <?= $disabled; ?>>
                                <?= e($judul); ?> (Stok: <?= $stok; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tanggal otomatis (readonly) -->
                <div class="form-row">
                    <div class="form-date">
                        <label>Tanggal Pinjam</label>
                        <input type="text" value="<?= e(date('d-m-Y')); ?>" readonly>
                    </div>
                    <div class="form-date">
                        <label>Jatuh Tempo</label>
                        <input type="text" value="<?= e(date('d-m-Y', strtotime('+7 days'))); ?>" readonly>
                    </div>
                </div>

                <div class="popup-footer">
                    <button type="button" class="btn-batal" id="batalPopupPeminjaman">Batal</button>
                    <button type="submit" class="btn-simpan">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
