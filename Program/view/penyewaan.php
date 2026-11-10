<?php
$editData = $editId ? $penyewaan->getById($editId) : null;
// sewa yang sudah dikembalikan tidak boleh diedit
if ($editData && $editData['status'] !== 'Disewa') {
    $editData = null;
}
$list = $penyewaan->getAll();
$listPenyewa = $penyewa->getAll();
$listBuku = $buku->getAll();
$today = date('Y-m-d');
?>
<h3>Transaksi Penyewaan</h3>

<div class="form-card">
    <h4><?= $editData ? 'Edit Penyewaan #' . (int) $editData['id'] : 'Buat Penyewaan Baru' ?></h4>
    <form method="POST" action="index.php?page=penyewaan">
        <input type="hidden" name="entity" value="penyewaan">
        <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?= (int) $editData['id'] ?>">
        <?php endif; ?>
        <div class="form-grid">
            <div class="form-group">
                <label>Penyewa *</label>
                <select name="penyewa_id" required>
                    <option value="">-- Pilih Penyewa --</option>
                    <?php foreach ($listPenyewa as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= ($editData && $editData['penyewa_id'] == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Buku *</label>
                <select name="buku_id" required>
                    <option value="">-- Pilih Buku --</option>
                    <?php foreach ($listBuku as $a): ?>
                        <option value="<?= (int) $a['id'] ?>" <?= ($editData && $editData['buku_id'] == $a['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['judul']) ?> (stok <?= (int) $a['stok'] ?>, Rp <?= number_format($a['harga_per_hari'], 0, ',', '.') ?>/hari)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah *</label>
                <input type="number" name="jumlah" min="1" required value="<?= htmlspecialchars($editData['jumlah'] ?? '1') ?>">
            </div>
            <div class="form-group">
                <label>Tanggal Sewa *</label>
                <input type="date" name="tgl_sewa" required value="<?= htmlspecialchars($editData['tgl_sewa'] ?? $today) ?>">
            </div>
            <div class="form-group">
                <label>Rencana Tanggal Kembali *</label>
                <input type="date" name="tgl_kembali_rencana" required value="<?= htmlspecialchars($editData['tgl_kembali_rencana'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <?php if ($editData): ?>
                <button type="submit" name="action" value="update" class="btn btn-primary">Simpan Perubahan</button>
                <a href="?page=penyewaan" class="btn btn-secondary">Batal</a>
            <?php else: ?>
                <button type="submit" name="action" value="add" class="btn btn-success">+ Buat Penyewaan</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<h4>Daftar Penyewaan</h4>
<div class="table-responsive">
    <table>
        <thead>
            <tr><th style="width:40px">ID</th><th>Penyewa</th><th>Buku</th><th>Jumlah</th><th>Periode</th><th>Biaya</th><th>Status</th><th style="width:230px;text-align:center">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="8" class="empty">Belum ada transaksi penyewaan.</td></tr>
        <?php else: foreach ($list as $s):
            $aktif = $s['status'] === 'Disewa';
            $telat = $aktif && $s['tgl_kembali_rencana'] < $today;
        ?>
            <tr>
                <td><?= (int) $s['id'] ?></td>
                <td><?= htmlspecialchars($s['nama_penyewa']) ?></td>
                <td><strong><?= htmlspecialchars($s['judul']) ?></strong></td>
                <td><?= (int) $s['jumlah'] ?></td>
                <td>
                    <small><strong>Sewa:</strong> <?= date('d M Y', strtotime($s['tgl_sewa'])) ?></small><br>
                    <small><strong>Rencana:</strong> <?= date('d M Y', strtotime($s['tgl_kembali_rencana'])) ?></small>
                    <?php if ($s['tgl_kembali_aktual']): ?>
                        <br><small><strong>Kembali:</strong> <?= date('d M Y', strtotime($s['tgl_kembali_aktual'])) ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    Rp <?= number_format($s['total_biaya'], 0, ',', '.') ?>
                    <?php if ($s['denda'] > 0): ?>
                        <br><small class="text-danger">+ denda Rp <?= number_format($s['denda'], 0, ',', '.') ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($telat): ?>
                        <span class="badge badge-danger">Terlambat</span>
                    <?php elseif ($aktif): ?>
                        <span class="badge badge-warning">Disewa</span>
                    <?php else: ?>
                        <span class="badge badge-success">Dikembalikan</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <?php if ($aktif): ?>
                        <form method="POST" action="index.php?page=penyewaan" class="inline" onsubmit="return confirm('Proses pengembalian buku hari ini?');">
                            <input type="hidden" name="entity" value="penyewaan">
                            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                            <button type="submit" name="action" value="return" class="btn btn-success btn-sm">Kembalikan</button>
                        </form>
                        <a href="?page=penyewaan&edit=<?= (int) $s['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <?php endif; ?>
                    <form method="POST" action="index.php?page=penyewaan" class="inline" onsubmit="return confirm('Hapus transaksi ini?');">
                        <input type="hidden" name="entity" value="penyewaan">
                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                        <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
