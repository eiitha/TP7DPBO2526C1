<?php
require_once __DIR__ . '/BaseModel.php';

// model transaksi penyewaan: menghubungkan penyewa dan buku.
// logika stok sengaja ditulis di sini (bukan lewat class Buku) karena setiap object model
// punya koneksi PDO sendiri, sedangkan transaksi database harus pakai satu koneksi yang sama.
class Penyewaan extends BaseModel {
    protected $table = 'penyewaan';

    // hitung selisih hari antar dua tanggal (Y-m-d)
    private function selisihHari($dari, $sampai) {
        $a = new DateTime($dari);
        $b = new DateTime($sampai);
        return (int) $a->diff($b)->format('%r%a');
    }

    // validasi input umum untuk create & update
    private function validate(array $d) {
        if ($d['penyewa_id'] <= 0 || $d['buku_id'] <= 0) {
            return 'Penyewa dan buku wajib dipilih!';
        }
        if ($d['jumlah'] <= 0) {
            return 'Jumlah harus lebih dari 0!';
        }
        $re = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($re, $d['tgl_sewa']) || !preg_match($re, $d['tgl_kembali_rencana'])) {
            return 'Format tanggal tidak valid!';
        }
        if ($this->selisihHari($d['tgl_sewa'], $d['tgl_kembali_rencana']) < 0) {
            return 'Tanggal kembali tidak boleh lebih awal dari tanggal sewa!';
        }
        return null;
    }

    // total biaya = harga/hari x jumlah x lama sewa (minimal 1 hari)
    private function hitungBiaya($harga, $jumlah, $tglSewa, $tglKembali) {
        $hari = max(1, $this->selisihHari($tglSewa, $tglKembali));
        return $harga * $jumlah * $hari;
    }

    // ambil & kunci baris buku selama transaksi berjalan (FOR UPDATE mencegah stok balapan)
    private function lockBuku($buku_id) {
        $stmt = $this->db->prepare("SELECT id, judul, stok, harga_per_hari, denda_per_hari FROM buku WHERE id = ? FOR UPDATE");
        $stmt->execute([$buku_id]);
        return $stmt->fetch();
    }

    private function ubahStok($buku_id, $delta) {
        $stmt = $this->db->prepare("UPDATE buku SET stok = stok + ? WHERE id = ?");
        $stmt->execute([$delta, $buku_id]);
    }

    private function lockPenyewaan($id) {
        $stmt = $this->db->prepare("SELECT * FROM penyewaan WHERE id = ? FOR UPDATE");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // CREATE: sewa baru -> cek stok -> simpan -> stok buku berkurang
    public function create(array $data) {
        if ($err = $this->validate($data)) {
            return $this->fail($err);
        }
        try {
            $this->db->beginTransaction();

            $buku = $this->lockBuku($data['buku_id']);
            if (!$buku) {
                $this->db->rollBack();
                return $this->fail('Buku tidak ditemukan!');
            }
            if ($buku['stok'] < $data['jumlah']) {
                $this->db->rollBack();
                return $this->fail("Stok {$buku['judul']} tidak cukup! Tersedia: {$buku['stok']}, diminta: {$data['jumlah']}.");
            }

            $total = $this->hitungBiaya($buku['harga_per_hari'], $data['jumlah'], $data['tgl_sewa'], $data['tgl_kembali_rencana']);

            $stmt = $this->db->prepare("
                INSERT INTO penyewaan (penyewa_id, buku_id, jumlah, tgl_sewa, tgl_kembali_rencana, total_biaya, denda, status)
                VALUES (?, ?, ?, ?, ?, ?, 0, 'Disewa')
            ");
            $stmt->execute([$data['penyewa_id'], $data['buku_id'], $data['jumlah'], $data['tgl_sewa'], $data['tgl_kembali_rencana'], $total]);

            $this->ubahStok($data['buku_id'], -$data['jumlah']);

            $this->db->commit();
            return $this->ok('Penyewaan berhasil dibuat! Total biaya: Rp ' . number_format($total, 0, ',', '.'));
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return $this->fail('Gagal menyimpan penyewaan.');
        }
    }

    // UPDATE: hanya untuk sewa yang masih aktif.
    // stok lama dikembalikan dulu, baru dicek & dikurangi lagi sesuai data baru
    public function update($id, array $data) {
        if ($err = $this->validate($data)) {
            return $this->fail($err);
        }
        try {
            $this->db->beginTransaction();

            $lama = $this->lockPenyewaan($id);
            if (!$lama) {
                $this->db->rollBack();
                return $this->fail('Data penyewaan tidak ditemukan!');
            }
            if ($lama['status'] !== 'Disewa') {
                $this->db->rollBack();
                return $this->fail('Penyewaan yang sudah dikembalikan tidak bisa diedit.');
            }

            // kembalikan stok lama (kunci buku lama dulu)
            $this->lockBuku($lama['buku_id']);
            $this->ubahStok($lama['buku_id'], $lama['jumlah']);

            // cek stok untuk buku/jumlah yang baru
            $buku = $this->lockBuku($data['buku_id']);
            if (!$buku) {
                $this->db->rollBack();
                return $this->fail('Buku tidak ditemukan!');
            }
            if ($buku['stok'] < $data['jumlah']) {
                $this->db->rollBack();
                return $this->fail("Stok {$buku['judul']} tidak cukup! Tersedia: {$buku['stok']}, diminta: {$data['jumlah']}.");
            }

            $total = $this->hitungBiaya($buku['harga_per_hari'], $data['jumlah'], $data['tgl_sewa'], $data['tgl_kembali_rencana']);

            $stmt = $this->db->prepare("
                UPDATE penyewaan
                SET penyewa_id = ?, buku_id = ?, jumlah = ?, tgl_sewa = ?, tgl_kembali_rencana = ?, total_biaya = ?
                WHERE id = ?
            ");
            $stmt->execute([$data['penyewa_id'], $data['buku_id'], $data['jumlah'], $data['tgl_sewa'], $data['tgl_kembali_rencana'], $total, $id]);

            $this->ubahStok($data['buku_id'], -$data['jumlah']);

            $this->db->commit();
            return $this->ok('Penyewaan berhasil diperbarui! Total biaya: Rp ' . number_format($total, 0, ',', '.'));
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return $this->fail('Gagal memperbarui penyewaan.');
        }
    }

    // PENGEMBALIAN: set status, hitung denda keterlambatan, stok dikembalikan
    public function kembalikan($id, $tglKembali = null) {
        $tglKembali = $tglKembali ?? date('Y-m-d');
        try {
            $this->db->beginTransaction();

            $sewa = $this->lockPenyewaan($id);
            if (!$sewa) {
                $this->db->rollBack();
                return $this->fail('Data penyewaan tidak ditemukan!');
            }
            if ($sewa['status'] !== 'Disewa') {
                $this->db->rollBack();
                return $this->fail('Buku pada penyewaan ini sudah dikembalikan.');
            }

            $buku = $this->lockBuku($sewa['buku_id']);
            $telat = max(0, $this->selisihHari($sewa['tgl_kembali_rencana'], $tglKembali));
            $denda = $telat * $buku['denda_per_hari'] * $sewa['jumlah'];

            $stmt = $this->db->prepare("
                UPDATE penyewaan SET status = 'Dikembalikan', tgl_kembali_aktual = ?, denda = ? WHERE id = ?
            ");
            $stmt->execute([$tglKembali, $denda, $id]);

            $this->ubahStok($sewa['buku_id'], $sewa['jumlah']);

            $this->db->commit();
            if ($telat > 0) {
                return $this->ok("Buku dikembalikan, terlambat $telat hari. Denda: Rp " . number_format($denda, 0, ',', '.'));
            }
            return $this->ok('Buku berhasil dikembalikan tepat waktu, tidak ada denda.');
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return $this->fail('Gagal memproses pengembalian.');
        }
    }

    // DELETE: kalau sewa masih aktif, stok dikembalikan dulu supaya tidak "hilang"
    public function delete($id) {
        try {
            $this->db->beginTransaction();

            $sewa = $this->lockPenyewaan($id);
            if (!$sewa) {
                $this->db->rollBack();
                return $this->fail('Data penyewaan tidak ditemukan!');
            }
            if ($sewa['status'] === 'Disewa') {
                $this->lockBuku($sewa['buku_id']);
                $this->ubahStok($sewa['buku_id'], $sewa['jumlah']);
            }
            $stmt = $this->db->prepare("DELETE FROM penyewaan WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return $this->ok('Data penyewaan berhasil dihapus!');
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return $this->fail('Gagal menghapus penyewaan.');
        }
    }

    // override getAll: JOIN ke penyewa dan buku, sewa aktif tampil duluan
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT s.*, p.nama AS nama_penyewa, a.judul, a.harga_per_hari
            FROM penyewaan s
            JOIN penyewa p ON s.penyewa_id = p.id
            JOIN buku a ON s.buku_id = a.id
            ORDER BY s.status ASC, s.tgl_kembali_rencana ASC, s.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // jumlah sewa aktif yang sudah lewat tanggal kembali (buat dashboard)
    public function countTerlambat() {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM penyewaan WHERE status = 'Disewa' AND tgl_kembali_rencana < ?");
        $stmt->execute([date('Y-m-d')]);
        return (int) $stmt->fetch()['total'];
    }

    public function countAktif() {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM penyewaan WHERE status = ?");
        $stmt->execute(['Disewa']);
        return (int) $stmt->fetch()['total'];
    }
}
