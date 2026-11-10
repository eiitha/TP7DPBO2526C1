<?php
require_once __DIR__ . '/BaseModel.php';

// model tabel penyewa (pelanggan)
class Penyewa extends BaseModel {
    protected $table = 'penyewa';

    // validasi dipisah biar dipakai create dan update
    private function validate(array $d) {
        if ($d['nama'] === '') {
            return 'Nama penyewa wajib diisi!';
        }
        if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Format email tidak valid!';
        }
        return null;
    }

    public function create(array $data) {
        if ($err = $this->validate($data)) {
            return $this->fail($err);
        }
        $stmt = $this->db->prepare("INSERT INTO penyewa (nama, email, no_hp, alamat) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['nama'], $data['email'], $data['no_hp'], $data['alamat']]);
        return $this->ok('Penyewa berhasil ditambahkan!');
    }

    public function update($id, array $data) {
        if ($err = $this->validate($data)) {
            return $this->fail($err);
        }
        $stmt = $this->db->prepare("UPDATE penyewa SET nama = ?, email = ?, no_hp = ?, alamat = ? WHERE id = ?");
        $stmt->execute([$data['nama'], $data['email'], $data['no_hp'], $data['alamat'], $id]);
        return $this->ok('Data penyewa berhasil diperbarui!');
    }
}
