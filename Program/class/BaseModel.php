<?php
require_once __DIR__ . '/../config/db.php';

// class induk abstrak: semua model (Kategori, Buku, Penyewa, Penyewaan) mewarisi class ini.
// koneksi db + operasi umum (getAll, getById, delete) ditulis sekali di sini.
abstract class BaseModel {
    protected $db;
    protected $table;   // diisi oleh class anak (hardcode, bukan dari input user)

    public function __construct() {
        $database = new Database();
        $this->db = $database->conn;
    }

    // tiap anak wajib punya create & update sendiri karena kolomnya beda-beda
    abstract public function create(array $data);
    abstract public function update($id, array $data);

    // helper format hasil biar seragam: ['status' => bool, 'message' => string]
    protected function ok($message) {
        return ['status' => true, 'message' => $message];
    }

    protected function fail($message) {
        return ['status' => false, 'message' => $message];
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function count() {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM {$this->table}");
        $stmt->execute();
        return (int) $stmt->fetch()['total'];
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) {
                return $this->fail('Data tidak ditemukan.');
            }
            return $this->ok('Data berhasil dihapus!');
        } catch (PDOException $e) {
            // 23000 = pelanggaran foreign key (data masih dipakai tabel lain)
            if ($e->getCode() === '23000') {
                return $this->fail('Data tidak bisa dihapus karena masih dipakai oleh data lain (foreign key).');
            }
            return $this->fail('Gagal menghapus data.');
        }
    }
}
