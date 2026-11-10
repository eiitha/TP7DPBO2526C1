<?php
// mode edit kalau ada ?edit=ID
$editData = $editId ? $kategori->getById($editId) : null;
$list = $kategori->getAllWithCount();
?>
<h3>Kategori Buku</h3>

<div class="form-card">
    <h4><?= $editData ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h4>
    <form method="POST" action="index.php?page=kategori">
        <input type="hidden" name="entity" value="kategori">
        <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?= (int) $editData['id'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-group">
                <label>Nama Kategori *</label>
                <input type="text" name="nama_kategori" required value="<?= htmlspecialchars($editData['nama_kategori'] ?? '') ?>" placeholder="Contoh: Tenda">
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <input type="text" name="deskripsi" value="<?= htmlspecialchars($editData['deskripsi'] ?? '') ?>" placeholder="Keterangan singkat">
            </div>
        </div>
        <div class="form-actions">
            <?php if ($editData): ?>
                <button type="submit" name="action" value="update" class="btn btn-primary">Simpan Perubahan</button>
                <a href="?page=kategori" class="btn btn-secondary">Batal</a>
            <?php else: ?>
                <button type="submit" name="action" value="add" class="btn btn-success">+ Tambah Kategori</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<h4>Daftar Kategori</h4>
<div class="table-responsive">
    <table>
        <thead>
            <tr><th style="width:50px">ID</th><th>Nama Kategori</th><th>Deskripsi</th><th>Jumlah Buku</th><th style="width:170px;text-align:center">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="5" class="empty">Belum ada kategori.</td></tr>
        <?php else: foreach ($list as $k): ?>
            <tr>
                <td><?= (int) $k['id'] ?></td>
                <td><strong><?= htmlspecialchars($k['nama_kategori']) ?></strong></td>
                <td><?= htmlspecialchars($k['deskripsi'] ?? '') ?></td>
                <td><span class="badge badge-info"><?= (int) $k['jumlah_buku'] ?> buku</span></td>
                <td class="actions">
                    <a href="?page=kategori&edit=<?= (int) $k['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <form method="POST" action="index.php?page=kategori" class="inline" onsubmit="return confirm('Hapus kategori ini?');">
                        <input type="hidden" name="entity" value="kategori">
                        <input type="hidden" name="id" value="<?= (int) $k['id'] ?>">
                        <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
