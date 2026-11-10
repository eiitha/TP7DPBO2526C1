<?php
// titik masuk aplikasi: router + pemroses aksi (controller sederhana)
session_start();
date_default_timezone_set('Asia/Jakarta');

require_once 'class/Kategori.php';
require_once 'class/Buku.php';
require_once 'class/Penyewa.php';
require_once 'class/Penyewaan.php';
require_once 'class/ImageUploader.php';

// bikin objek tiap model; view memakai objek-objek ini
$kategori  = new Kategori();
$buku      = new Buku();
$penyewa   = new Penyewa();
$penyewaan = new Penyewaan();
$uploader  = new ImageUploader();

// ---------- helper kecil ----------
function str($key) {
    return trim($_POST[$key] ?? '');
}

function num($key) {
    return (int) ($_POST[$key] ?? 0);
}

// simpan hasil aksi ke session lalu redirect (pola Post/Redirect/Get)
function flashRedirect(array $result, $page) {
    $_SESSION['msg'] = $result['message'];
    $_SESSION['msg_type'] = $result['status'] ? 'success' : 'danger';
    header("Location: index.php?page=" . $page);
    exit;
}

// ---------- PROSES AKSI (hanya lewat POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entity = $_POST['entity'] ?? '';
    $action = $_POST['action'] ?? '';
    $id     = num('id');

    switch ($entity) {
        case 'kategori':
            $data = ['nama_kategori' => str('nama_kategori'), 'deskripsi' => str('deskripsi')];
            if ($action === 'add')        flashRedirect($kategori->create($data), 'kategori');
            if ($action === 'update')     flashRedirect($kategori->update($id, $data), 'kategori');
            if ($action === 'delete')     flashRedirect($kategori->delete($id), 'kategori');
            break;

        case 'penyewa':
            $data = ['nama' => str('nama'), 'email' => str('email'), 'no_hp' => str('no_hp'), 'alamat' => str('alamat')];
            if ($action === 'add')        flashRedirect($penyewa->create($data), 'penyewa');
            if ($action === 'update')     flashRedirect($penyewa->update($id, $data), 'penyewa');
            if ($action === 'delete')     flashRedirect($penyewa->delete($id), 'penyewa');
            break;

        case 'buku':
            $data = [
                'kategori_id'    => num('kategori_id'),
                'judul'      => str('judul'),
                'penulis'    => str('penulis'),
                'stok'           => num('stok'),
                'harga_per_hari' => num('harga_per_hari'),
                'denda_per_hari' => num('denda_per_hari'),
                'img'            => 'img/default.jpg',
            ];
            if ($action === 'add') {
                $data['img'] = $uploader->upload('image_file', 'img/default.jpg');
                flashRedirect($buku->create($data), 'buku');
            }
            if ($action === 'update') {
                // kalau tidak upload foto baru, pakai foto lama
                $data['img'] = $uploader->upload('image_file', str('existing_image') ?: 'img/default.jpg');
                flashRedirect($buku->update($id, $data), 'buku');
            }
            if ($action === 'delete')     flashRedirect($buku->delete($id), 'buku');
            break;

        case 'penyewaan':
            $data = [
                'penyewa_id'          => num('penyewa_id'),
                'buku_id'             => num('buku_id'),
                'jumlah'              => num('jumlah'),
                'tgl_sewa'            => str('tgl_sewa'),
                'tgl_kembali_rencana' => str('tgl_kembali_rencana'),
            ];
            if ($action === 'add')        flashRedirect($penyewaan->create($data), 'penyewaan');
            if ($action === 'update')     flashRedirect($penyewaan->update($id, $data), 'penyewaan');
            if ($action === 'return')     flashRedirect($penyewaan->kembalikan($id), 'penyewaan');
            if ($action === 'delete')     flashRedirect($penyewaan->delete($id), 'penyewaan');
            break;
    }
    // entity/action tidak dikenal
    flashRedirect(['status' => false, 'message' => 'Aksi tidak dikenali.'], 'home');
}

// ---------- TAMPILKAN HALAMAN (GET) ----------
$pages = ['home', 'penyewaan', 'buku', 'kategori', 'penyewa'];
$page = $_GET['page'] ?? 'home';
if (!in_array($page, $pages, true)) {
    $page = 'notfound';
}
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PustakaRental - Sewa Buku Buku</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'view/header.php'; ?>

        <nav>
            <ul>
                <li><a href="?page=home" class="<?= $page === 'home' ? 'active' : '' ?>">Beranda</a></li>
                <li><a href="?page=penyewaan" class="<?= $page === 'penyewaan' ? 'active' : '' ?>">Penyewaan</a></li>
                <li><a href="?page=buku" class="<?= $page === 'buku' ? 'active' : '' ?>">Buku</a></li>
                <li><a href="?page=kategori" class="<?= $page === 'kategori' ? 'active' : '' ?>">Kategori</a></li>
                <li><a href="?page=penyewa" class="<?= $page === 'penyewa' ? 'active' : '' ?>">Penyewa</a></li>
            </ul>
        </nav>

        <main>
            <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?= htmlspecialchars($_SESSION['msg_type'] ?? 'info') ?>">
                    <?= htmlspecialchars($_SESSION['msg']) ?>
                </div>
                <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
            <?php endif; ?>

            <?php
            if ($page === 'notfound') {
                echo '<p>Halaman tidak ditemukan.</p>';
            } else {
                include 'view/' . $page . '.php';
            }
            ?>
        </main>

        <?php include 'view/footer.php'; ?>
    </div>
</body>
</html>
