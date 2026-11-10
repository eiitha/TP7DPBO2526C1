<?php
$editData = $editId ? $buku->getById($editId) : null;
$list = $buku->getAll();
$listKategori = $kategori->getAll();
$currentImg = $editData['img'] ?? 'img/default.jpg';
?>
<h3>Data Buku Buku</h3>

<div class="form-card">
    <h4><?= $editData ? 'Edit Buku' : 'Tambah Buku Baru' ?></h4>
    <form method="POST" action="index.php?page=buku" enctype="multipart/form-data">
        <input type="hidden" name="entity" value="buku">
        <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?= (int) $editData['id'] ?>">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($currentImg) ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-group">
                <label>Judul Buku *</label>
                <input type="text" name="judul" required value="<?= htmlspecialchars($editData['judul'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Penulis *</label>
                <input type="text" name="penulis" required value="<?= htmlspecialchars($editData['penulis'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Kategori *</label>
                <select name="kategori_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($listKategori as $k): ?>
                        <option value="<?= (int) $k['id'] ?>" <?= ($editData && $editData['kategori_id'] == $k['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Stok Tersedia *</label>
                <input type="number" name="stok" min="0" required value="<?= htmlspecialchars($editData['stok'] ?? '0') ?>">
            </div>
            <div class="form-group">
                <label>Harga per Hari (Rp) *</label>
                <input type="number" name="harga_per_hari" min="0" required value="<?= htmlspecialchars($editData['harga_per_hari'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Denda Telat per Hari (Rp) *</label>
                <input type="number" name="denda_per_hari" min="0" required value="<?= htmlspecialchars($editData['denda_per_hari'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Sampul Buku (disimpan ke folder img/)</label>
                <input type="file" name="image_file" accept="image/*">
                <?php if ($editData): ?>
                    <small>Foto saat ini: <code><?= htmlspecialchars($currentImg) ?></code> (pilih file baru untuk mengganti)</small>
                <?php else: ?>
                    <small>JPG/PNG/WEBP/GIF maks 2 MB. Kosong = <code>img/default.jpg</code>.</small>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-actions">
            <?php if ($editData): ?>
                <button type="submit" name="action" value="update" class="btn btn-primary">Simpan Perubahan</button>
                <a href="?page=buku" class="btn btn-secondary">Batal</a>
            <?php else: ?>
                <button type="submit" name="action" value="add" class="btn btn-success">+ Tambah Buku</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<h4>Daftar Buku</h4>
<div class="table-responsive">
    <table>
        <thead>
            <tr><th style="width:50px">ID</th><th style="width:90px">Foto</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Stok</th><th>Harga/Hari</th><th>Denda/Hari</th><th style="width:170px;text-align:center">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="9" class="empty">Belum ada buku.</td></tr>
        <?php else: foreach ($list as $a): ?>
            <tr>
                <td><?= (int) $a['id'] ?></td>
                <td><img class="thumb" src="<?= htmlspecialchars($a['img']) ?>" alt="Foto <?= htmlspecialchars($a['judul']) ?>" onerror="this.src='img/default.jpg';"></td>
                <td><strong><?= htmlspecialchars($a['judul']) ?></strong></td>
                <td><?= htmlspecialchars($a['penulis']) ?></td>
                <td><span class="badge badge-info"><?= htmlspecialchars($a['nama_kategori']) ?></span></td>
                <td>
                    <?php if ($a['stok'] <= 0): ?>
                        <span class="badge badge-danger">Habis</span>
                    <?php else: ?>
                        <span class="badge badge-success"><?= (int) $a['stok'] ?></span>
                    <?php endif; ?>
                </td>
                <td>Rp <?= number_format($a['harga_per_hari'], 0, ',', '.') ?></td>
                <td>Rp <?= number_format($a['denda_per_hari'], 0, ',', '.') ?></td>
                <td class="actions">
                    <a href="?page=buku&edit=<?= (int) $a['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <form method="POST" action="index.php?page=buku" class="inline" onsubmit="return confirm('Hapus buku ini?');">
                        <input type="hidden" name="entity" value="buku">
                        <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                        <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
