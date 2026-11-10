DROP DATABASE IF EXISTS db_rental_buku;
CREATE DATABASE db_rental_buku;
USE db_rental_buku;

-- 1. Tabel Kategori (master, dipakai oleh buku)
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE,
    deskripsi VARCHAR(255)
);

-- 2. Tabel Buku (FK -> kategori, punya kolom img untuk foto sampul)
CREATE TABLE buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NOT NULL,
    judul VARCHAR(150) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    harga_per_hari INT NOT NULL,
    denda_per_hari INT NOT NULL DEFAULT 0,
    img VARCHAR(255) NOT NULL DEFAULT 'img/default.jpg',
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT
);

-- 3. Tabel Penyewa (master)
CREATE TABLE penyewa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    no_hp VARCHAR(20),
    alamat VARCHAR(255)
);

-- 4. Tabel Penyewaan (transaksi, FK -> penyewa dan buku)
CREATE TABLE penyewaan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    penyewa_id INT NOT NULL,
    buku_id INT NOT NULL,
    jumlah INT NOT NULL,
    tgl_sewa DATE NOT NULL,
    tgl_kembali_rencana DATE NOT NULL,
    tgl_kembali_aktual DATE NULL,
    total_biaya INT NOT NULL DEFAULT 0,
    denda INT NOT NULL DEFAULT 0,
    status ENUM('Disewa', 'Dikembalikan') NOT NULL DEFAULT 'Disewa',
    FOREIGN KEY (penyewa_id) REFERENCES penyewa(id) ON DELETE RESTRICT,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE RESTRICT
);

-- Seed data
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Novel', 'Fiksi dan novel populer'),
('Komik', 'Komik dan manga'),
('Pengembangan Diri', 'Buku self improvement'),
('Teknologi', 'Pemrograman dan komputer');

-- stok = stok yang TERSEDIA saat ini (sudah dikurangi sewa aktif di bawah)
INSERT INTO buku (kategori_id, judul, penulis, stok, harga_per_hari, denda_per_hari, img) VALUES
(1, 'Laskar Pelangi', 'Andrea Hirata', 3, 3000, 2000, 'img/default.jpg'),
(1, 'Bumi', 'Tere Liye', 4, 3000, 2000, 'img/default.jpg'),
(2, 'Detective Conan Vol. 1', 'Gosho Aoyama', 5, 2000, 1000, 'img/default.jpg'),
(3, 'Atomic Habits', 'James Clear', 2, 4000, 3000, 'img/default.jpg'),
(4, 'Clean Code', 'Robert C. Martin', 1, 5000, 4000, 'img/default.jpg');

INSERT INTO penyewa (nama, email, no_hp, alamat) VALUES
('Raka Pratama', 'raka@gmail.com', '081211112222', 'Jl. Dago No. 10, Bandung'),
('Dinda Lestari', 'dinda@gmail.com', '082133334444', 'Jl. Setiabudi No. 5, Bandung'),
('Fajar Nugraha', 'fajar@gmail.com', '083155556666', 'Jl. Cihampelas No. 21, Bandung');

-- 2 sewa aktif (Laskar Pelangi x1, Atomic Habits x1) + 1 sudah dikembalikan (telat 1 hari)
-- total_biaya = harga_per_hari x jumlah x lama sewa
INSERT INTO penyewaan (penyewa_id, buku_id, jumlah, tgl_sewa, tgl_kembali_rencana, tgl_kembali_aktual, total_biaya, denda, status) VALUES
(1, 1, 1, '2026-11-08', '2026-11-12', NULL, 12000, 0, 'Disewa'),
(2, 4, 1, '2026-11-09', '2026-11-13', NULL, 16000, 0, 'Disewa'),
(3, 3, 1, '2026-11-01', '2026-11-04', '2026-11-05', 6000, 1000, 'Dikembalikan');
