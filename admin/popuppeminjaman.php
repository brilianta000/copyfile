<div class="popup-overlay <?= $openPopup ? 'active' : ''; ?>" id="popupPeminjaman">
    <div class="popup-box">
        <div class="popup-header">
            <span>Tambah Peminjaman</span>
            <button type="button" class="popup-close" id="closePopupPeminjaman" aria-label="Tutup">&times;</button>
        </div>

        <form method="post">
            <div class="popup-body">
                <?php if (!empty($errors)): ?>
                    <div class="popup-alert">
                        <?= e(implode(' ', $errors)); ?>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="action" value="add_peminjaman">

                <div class="form-group">
                    <label for="popup_nim">NIM</label>
                    <input
                        type="text"
                        id="popup_nim"
                        name="nim"
                        value="<?= e($oldInput['nim'] ?? ''); ?>"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="popup_nama">Nama</label>
                    <input
                        type="text"
                        id="popup_nama"
                        name="nama"
                        value="<?= e($oldInput['nama'] ?? ''); ?>"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="popup_buku">Buku</label>
                    <select id="popup_buku" name="buku" required>
                        <option value="">Pilih Buku</option>
                        <?php foreach ($opsiBuku as $opsi): ?>
                            <?php
                            $judul = $opsi['judul'] ?? '';
                            $stok = (int) ($opsi['stok'] ?? 0);
                            $selected = (($oldInput['buku'] ?? '') === $judul) ? 'selected' : '';
                            ?>
                            <option value="<?= e($judul); ?>" <?= $selected; ?>>
                                <?= e($judul); ?> (Stok: <?= $stok; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php
                $tglPinjamValue = !empty($oldInput['tgl_pinjam']) ? $oldInput['tgl_pinjam'] : todayDate();
                $tglKembaliValue = !empty($oldInput['tgl_kembali']) ? $oldInput['tgl_kembali'] : defaultTanggalKembali();
                ?>

                <div class="form-row">
                    <div class="form-date">
                        <label for="popup_tgl_pinjam_view">Tanggal Pinjam</label>
                        <input
                            type="text"
                            id="popup_tgl_pinjam_view"
                            value="<?= e(date('d-m-Y', strtotime($tglPinjamValue))); ?>"
                            readonly
                        >
                    </div>

                    <div class="form-date">
                        <label for="popup_tgl_kembali_view">Tanggal Kembali</label>
                        <input
                            type="text"
                            id="popup_tgl_kembali_view"
                            value="<?= e(date('d-m-Y', strtotime($tglKembaliValue))); ?>"
                            readonly
                        >
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
