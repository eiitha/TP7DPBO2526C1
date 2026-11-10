<?php
$editData = $editId ? $penyewa->getById($editId) : null;
$list = $penyewa->getAll();
?>
<h3>Data Penyewa</h3>

<div class="form-card">
    <h4><?= $editData ? 'Edit Penyewa' : 'Tambah Penyewa Baru' ?></h4>
    <form method="POST" action="index.php?page=penyewa">
        <input type="hidden" name="entity" value="penyewa">
        <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?= (int) $editData['id'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-group">
                <label>Nama *</label>
                <input type="text" name="nama" required value="<?= htmlspecialchars($editData['nama'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($editData['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($editData['no_hp'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <input type="text" name="alamat" value="<?= htmlspecialchars($editData['alamat'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <?php if ($editData): ?>
                <button type="submit" name="action" value="update" class="btn btn-primary">Simpan Perubahan</button>
                <a href="?page=penyewa" class="btn btn-secondary">Batal</a>
            <?php else: ?>
                <button type="submit" name="action" value="add" class="btn btn-success">+ Tambah Penyewa</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<h4>Daftar Penyewa</h4>
<div class="table-responsive">
    <table>
        <thead>
            <tr><th style="width:50px">ID</th><th>Nama</th><th>Email</th><th>No. HP</th><th>Alamat</th><th style="width:170px;text-align:center">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="6" class="empty">Belum ada penyewa.</td></tr>
        <?php else: foreach ($list as $p): ?>
            <tr>
                <td><?= (int) $p['id'] ?></td>
                <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                <td><?= htmlspecialchars($p['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['no_hp'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['alamat'] ?? '') ?></td>
                <td class="actions">
                    <a href="?page=penyewa&edit=<?= (int) $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <form method="POST" action="index.php?page=penyewa" class="inline" onsubmit="return confirm('Hapus penyewa ini?');">
                        <input type="hidden" name="entity" value="penyewa">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
