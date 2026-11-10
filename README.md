# TP7DPBO2526C1

## Janji

Saya Jillena Surbakti dengan NIM 2501485 mengerjakan Tugas Praktikum 7 pada Mata Kuliah Desain dan Pemrograman Berorientasi Objek (DPBO) untuk keberkahan-Nya maka saya tidak melakukan kecurangan seperti yang telah dispesifikasikan. Aamiin

## PustakaRental: Sistem Penyewaan Buku

Aplikasi web **PHP Native (OOP) + PDO** untuk mengelola usaha rental buku fiktif bernama **PustakaRental**. Sistem mencatat kategori buku, buku (judul, penulis, stok, harga sewa), data penyewa, serta transaksi penyewaan. Stok buku **berkurang otomatis** saat disewa, **bertambah kembali** saat dikembalikan, dan **denda keterlambatan dihitung otomatis**.

## Tema Website

Tema: **manajemen penyewaan buku (novel, komik, buku pengembangan diri, teknologi)**. Staf rental dapat:

- Mengelola **kategori** buku (Novel, Komik, Pengembangan Diri, Teknologi).
- Mengelola **buku**: judul, penulis, kategori, stok tersedia, harga sewa per hari, denda telat per hari, dan **sampul** (upload ke folder `img/`).
- Mengelola **penyewa** (nama, email, no. HP, alamat).
- Membuat **transaksi penyewaan** (penyewa + buku + jumlah + periode), memprosesnya saat **pengembalian**, dan memantau sewa yang **terlambat**.

## Fitur Utama

CRUD lengkap untuk keempat tabel:

| Menu | Fungsi |
|------|--------|
| **Kategori** | Tambah, lihat (beserta jumlah buku), edit, hapus kategori. |
| **Buku** | CRUD buku + upload foto. Stok `0` ditandai badge **Habis**. |
| **Penyewa** | CRUD data penyewa, validasi format email. |
| **Penyewaan** | Buat, edit (khusus sewa aktif), kembalikan, dan hapus transaksi. |

Logika bisnis (di `class/Penyewaan.php`):

- **Validasi stok:** sewa ditolak bila `jumlah` melebihi stok tersedia.
- **Total biaya otomatis:** `harga_per_hari x jumlah x lama sewa` (minimal 1 hari).
- **Stok otomatis:** dikurangi saat sewa dibuat; dikembalikan saat dikembalikan atau saat sewa aktif dihapus; disesuaikan ulang saat sewa diedit.
- **Denda otomatis:** `hari telat x denda_per_hari buku x jumlah`, dihitung saat tombol **Kembalikan** ditekan.
- **Badge Terlambat** untuk sewa aktif yang lewat rencana tanggal kembali.
- **Database transaction** (`beginTransaction`/`commit`/`rollBack`) + `SELECT ... FOR UPDATE` agar stok dan transaksi selalu konsisten.
- **Perlindungan foreign key:** menghapus data yang masih dipakai (mis. kategori yang punya buku) ditolak dengan pesan yang jelas.

Aspek keamanan: **seluruh query memakai prepared statement** (tidak ada `query()` mentah), emulasi prepare PDO dimatikan, output dibersihkan dengan `htmlspecialchars`, aksi hapus/ubah hanya lewat **POST**, dan upload foto divalidasi ekstensi, ukuran (maks 2 MB), serta isi file (`getimagesize`).

## Struktur Proyek

`class/` berperan sebagai **Model**, `view/` sebagai **View**, dan `index.php` sebagai **Controller/router**.

```
TP7DPBO2526C1/
├── Dokumentasi/
│   ├── diagram.png
│   ├── Dashboard.mp4
│   ├── CRUDPenyewa.mp4
│   ├── CRUDKategori.mp4
│   ├── CRUDBuku.mp4
│   └── CRUDPenyewaan.mp4
├── Program/
│   ├── class/
│   │   ├── BaseModel.php
│   │   ├── Kategori.php
│   │   ├── Buku.php
│   │   ├── Penyewa.php
│   │   ├── Penyewaan.php
│   │   └── ImageUploader.php
│   ├── config/
│   │   └── db.php
│   ├── database/
│   │   └── db_rental_buku.sql
│   ├── img/
│   │   └── default.jpg
│   ├── view/
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── home.php
│   │   ├── kategori.php
│   │   ├── buku.php
│   │   ├── penyewa.php
│   │   └── penyewaan.php
│   ├── index.php
│   └── style.css
└── README.md
```

## Konsep OOP yang Digunakan

- **Abstraction:** `BaseModel` adalah abstract class dengan method abstrak `create()` dan `update()` yang wajib diimplementasikan tiap model.
- **Inheritance:** `Kategori`, `Buku`, `Penyewa`, dan `Penyewaan` mewarisi `BaseModel`, sehingga koneksi database dan operasi umum tidak ditulis berulang.
- **Polymorphism:** `Buku::getAll()` dan `Penyewaan::getAll()` meng-*override* `getAll()` milik induk dengan query `JOIN`; `Penyewaan::delete()` meng-*override* `delete()` agar stok ikut dikembalikan.
- **Encapsulation:** `$db` dan `$table` bersifat `protected`; method bantu (`validate`, `lockBuku`, `ubahStok`, dst.) bersifat `private`.
- **Single Responsibility:** satu class per tabel, upload foto dipisah ke `ImageUploader`, koneksi database dipisah ke `Database`.

## Relasi Diagram

<img width="1260" height="384" alt="Screenshot (656)" src="https://github.com/user-attachments/assets/49d0fd28-f1f3-4be8-9cd9-e8526631d972" />


## Database

File lengkap: [`Program/database/db_rental_buku.sql`](Program/database/db_rental_buku.sql). Database bernama **`db_rental_buku`**, terdiri dari **4 tabel** dengan **3 foreign key**.

**1. `kategori`**: master kategori buku.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | INT, PK, AUTO_INCREMENT | ID kategori |
| `nama_kategori` | VARCHAR(100), NOT NULL, UNIQUE | Nama kategori |
| `deskripsi` | VARCHAR(255) | Keterangan |

**2. `buku`**: data buku.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | INT, PK, AUTO_INCREMENT | ID buku |
| `kategori_id` | INT, NOT NULL, **FK → kategori(id)** | Kategori buku |
| `judul` | VARCHAR(150), NOT NULL | Judul buku |
| `penulis` | VARCHAR(100), NOT NULL | Penulis buku |
| `stok` | INT, NOT NULL, default 0 | Stok **tersedia** saat ini |
| `harga_per_hari` | INT, NOT NULL | Harga sewa per hari |
| `denda_per_hari` | INT, NOT NULL, default 0 | Denda telat per hari |
| `img` | VARCHAR(255), default `img/default.jpg` | Path foto di folder `img/` |

**3. `penyewa`**: data pelanggan.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | INT, PK, AUTO_INCREMENT | ID penyewa |
| `nama` | VARCHAR(100), NOT NULL | Nama |
| `email` | VARCHAR(100) | Email |
| `no_hp` | VARCHAR(20) | Nomor telepon |
| `alamat` | VARCHAR(255) | Alamat |

**4. `penyewaan`**: tabel transaksi yang menghubungkan `penyewa` dan `buku`.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | INT, PK, AUTO_INCREMENT | ID transaksi |
| `penyewa_id` | INT, NOT NULL, **FK → penyewa(id)** | Penyewa |
| `buku_id` | INT, NOT NULL, **FK → buku(id)** | Buku yang disewa |
| `jumlah` | INT, NOT NULL | Jumlah unit |
| `tgl_sewa` | DATE, NOT NULL | Tanggal mulai sewa |
| `tgl_kembali_rencana` | DATE, NOT NULL | Rencana tanggal kembali |
| `tgl_kembali_aktual` | DATE, NULL | Tanggal kembali sebenarnya |
| `total_biaya` | INT, NOT NULL | Biaya sewa (otomatis) |
| `denda` | INT, NOT NULL, default 0 | Denda keterlambatan (otomatis) |
| `status` | ENUM('Disewa','Dikembalikan') | Status transaksi |

Ketiga foreign key memakai `ON DELETE RESTRICT`: data induk yang masih dipakai tidak dapat dihapus (aplikasi menampilkan pesan penjelasan). Untuk menghapus kategori/buku/penyewa yang punya riwayat, hapus dulu transaksi atau buku yang terkait.

## Alur Kode (Flow)

### Alur umum

1. **Semua request masuk lewat `index.php`.** File ini memulai `session_start()`, memuat semua class, lalu membuat objek `Kategori`, `Buku`, `Penyewa`, `Penyewaan`, dan `ImageUploader`.
2. **Koneksi database** dibuat class `Database` (`config/db.php`) dengan PDO (mode exception, fetch assoc). `BaseModel` memanggilnya di constructor, jadi semua model otomatis terkoneksi.
3. **Aksi (Create/Update/Delete/Kembalikan) dikirim lewat form POST** dengan field tersembunyi `entity` (tabel) dan tombol `action` (`add`, `update`, `delete`, `return`). `index.php` mengambil input, merapikan (`trim`, cast `int`), lalu memanggil method model yang sesuai.
4. Model mengembalikan `['status' => bool, 'message' => string]`. Hasilnya disimpan di `$_SESSION` lalu halaman **di-redirect** (pola Post/Redirect/Get, sehingga refresh tidak mengirim ulang form).
5. **Rendering halaman (GET):** `?page=` (default `home`, hanya nilai dalam whitelist) menentukan view yang di-include di dalam layout header, navigasi, notifikasi, dan footer.
6. **View** mengambil data lewat model (`getAll()`), menampilkannya dalam tabel, dan memakai satu form untuk tambah maupun edit. Bila URL berisi `?edit=ID`, form diisi data dari `getById()`.

### Alur CRUD per tabel

| Aksi | Pemicu | Method yang dipanggil |
|------|--------|-----------------------|
| **Create** | Submit form tambah (`action=add`) | `create()` |
| **Read** | Buka `?page=...` | `getAll()` / `getById()` |
| **Update** | Klik **Edit**, ubah, submit (`action=update`) | `update()` |
| **Delete** | Klik **Hapus**, konfirmasi `confirm()` (`action=delete`) | `delete()` |

### Alur khusus: Penyewaan

```
Buat sewa  ──> cek stok ──(kurang)──> ditolak
                  │
                  └─(cukup)─> INSERT penyewaan + stok buku DIKURANGI  [dalam 1 transaksi DB]

Kembalikan ──> hitung hari telat = tgl hari ini - tgl_kembali_rencana
               denda = telat x denda_per_hari x jumlah
               status = Dikembalikan + stok buku DITAMBAH kembali

Edit sewa  ──> stok lama dikembalikan ─> cek stok untuk data baru
               (gagal = ROLLBACK, stok & data kembali seperti semula)

Hapus sewa ──> bila masih "Disewa", stok dikembalikan dulu, baru dihapus
```

Setiap langkah yang mengubah stok dibungkus **database transaction** dan baris buku dikunci dengan `SELECT ... FOR UPDATE`. Method stok ditulis langsung di `Penyewaan` (bukan lewat class `Buku`) karena transaksi hanya berlaku pada **satu koneksi** PDO.

### Alur khusus: Buku (upload foto)

1. Form buku memakai `enctype="multipart/form-data"`.
2. `ImageUploader::upload()` memeriksa ekstensi (`jpg/jpeg/png/webp/gif`), ukuran (maks 2 MB), dan isi file (`getimagesize`).
3. Nama file dibersihkan dan diberi awalan timestamp (mis. `1794312837_sampul.jpg`), lalu disimpan di `img/`.
4. Path `img/nama_file` disimpan di kolom `buku.img`. Tanpa upload dipakai `img/default.jpg` (tambah) atau foto lama lewat input hidden `existing_image` (edit).


## Dokumentasi

### Dashboard


https://github.com/user-attachments/assets/cf4789e6-f31f-46e4-8761-769b4f26315a



### CRUD Penyewa


https://github.com/user-attachments/assets/b0ecf5a5-1515-42e1-a7ff-c0c53014140d



### CRUD Kategori


https://github.com/user-attachments/assets/a97130fe-47a7-46f5-b460-2d2d042d6ee4



### CRUD Buku


https://github.com/user-attachments/assets/b79f55f8-bffa-4893-a064-978de3308b7b



### CRUD Penyewaan


https://github.com/user-attachments/assets/3fa61999-c18e-4b15-b19a-8cd35adc158b



```
