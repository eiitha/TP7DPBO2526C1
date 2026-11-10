<?php
require_once __DIR__ . '/BaseModel.php';

// model tabel kategori
class Kategori extends BaseModel {
    protected $table = 'kategori';

    public function create(array $data) {
        if ($data['nama_kategori'] === '') {
            return $this->fail('Nama kategori wajib diisi!');
        }
        try {
            $stmt = $this->db->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
            $stmt->execute([$data['nama_kategori'], $data['deskripsi']]);
            return $this->ok('Kategori berhasil ditambahkan!');
        } catch (PDOException $e) {
            return $this->fail($e->getCode() === '23000' ? 'Nama kategori sudah ada!' : 'Gagal menyimpan kategori.');
        }
    }

    public function update($id, array $data) {
        if ($data['nama_kategori'] === '') {
            return $this->fail('Nama kategori wajib diisi!');
        }
        try {
            $stmt = $this->db->prepare("UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$data['nama_kategori'], $data['deskripsi'], $id]);
            return $this->ok('Kategori berhasil diperbarui!');
        } catch (PDOException $e) {
            return $this->fail($e->getCode() === '23000' ? 'Nama kategori sudah ada!' : 'Gagal memperbarui kategori.');
        }
    }

    // daftar kategori + jumlah jenis buku di dalamnya
    public function getAllWithCount() {
        $stmt = $this->db->prepare("
            SELECT k.*, COUNT(a.id) AS jumlah_buku
            FROM kategori k
            LEFT JOIN buku a ON a.kategori_id = k.id
            GROUP BY k.id
            ORDER BY k.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
