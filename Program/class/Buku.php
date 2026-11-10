<?php
require_once __DIR__ . '/BaseModel.php';

// model tabel buku (punya foreign key ke kategori)
class Buku extends BaseModel {
    protected $table = 'buku';

    private function validate(array $d) {
        if ($d['judul'] === '' || $d['penulis'] === '') {
            return 'Judul dan penulis wajib diisi!';
        }
        if ($d['kategori_id'] <= 0) {
            return 'Kategori wajib dipilih!';
        }
        if ($d['stok'] < 0 || $d['harga_per_hari'] < 0 || $d['denda_per_hari'] < 0) {
            return 'Stok, harga, dan denda tidak boleh negatif!';
        }
        return null;
    }

    public function create(array $data) {
        if ($err = $this->validate($data)) {
            return $this->fail($err);
        }
        $stmt = $this->db->prepare("
            INSERT INTO buku (kategori_id, judul, penulis, stok, harga_per_hari, denda_per_hari, img)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['kategori_id'], $data['judul'], $data['penulis'], $data['stok'],
            $data['harga_per_hari'], $data['denda_per_hari'], $data['img']
        ]);
        return $this->ok('Buku berhasil ditambahkan!');
    }

    public function update($id, array $data) {
        if ($err = $this->validate($data)) {
            return $this->fail($err);
        }
        $stmt = $this->db->prepare("
            UPDATE buku
            SET kategori_id = ?, judul = ?, penulis = ?, stok = ?, harga_per_hari = ?, denda_per_hari = ?, img = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['kategori_id'], $data['judul'], $data['penulis'], $data['stok'],
            $data['harga_per_hari'], $data['denda_per_hari'], $data['img'], $id
        ]);
        return $this->ok('Data buku berhasil diperbarui!');
    }

    // override getAll: di-JOIN ke kategori biar nama kategori ikut tampil
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT a.*, k.nama_kategori
            FROM buku a
            JOIN kategori k ON a.kategori_id = k.id
            ORDER BY a.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
