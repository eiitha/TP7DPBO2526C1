<?php
// ringkasan data untuk kartu dashboard
$totalAktif    = $penyewaan->countAktif();
$totalTerlambat = $penyewaan->countTerlambat();
$listBuku      = $buku->getAll();
$stokHabis     = array_filter($listBuku, fn($a) => $a['stok'] <= 0);
?>
<div class="hero-banner">
    <h2>Selamat Datang di PustakaRental</h2>
    <p>Kelola penyewaan buku: stok berkurang otomatis saat disewa, bertambah saat dikembalikan, dan denda keterlambatan dihitung otomatis.</p>
</div>

<h3>Ringkasan</h3>
<div class="menu-grid">
    <div class="menu-card" style="border-top-color:#16a34a;">
        <h4>Sewa Aktif</h4>
        <div class="stat-badge"><?= $totalAktif ?> transaksi</div>
        <a href="?page=penyewaan" class="btn btn-success btn-block">Kelola Penyewaan &raquo;</a>
    </div>
    <div class="menu-card" style="border-top-color:#dc2626;">
        <h4>Terlambat Kembali</h4>
        <div class="stat-badge"><?= $totalTerlambat ?> transaksi</div>
        <a href="?page=penyewaan" class="btn btn-danger btn-block">Lihat Penyewaan &raquo;</a>
    </div>
    <div class="menu-card" style="border-top-color:#f59e0b;">
        <h4>Jenis Buku</h4>
        <div class="stat-badge"><?= count($listBuku) ?> buku (<?= count($stokHabis) ?> stok habis)</div>
        <a href="?page=buku" class="btn btn-warning btn-block">Kelola Buku &raquo;</a>
    </div>
    <div class="menu-card" style="border-top-color:#2563eb;">
        <h4>Kategori &amp; Penyewa</h4>
        <div class="stat-badge"><?= $kategori->count() ?> kategori, <?= $penyewa->count() ?> penyewa</div>
        <a href="?page=penyewa" class="btn btn-primary btn-block">Kelola Penyewa &raquo;</a>
    </div>
</div>
