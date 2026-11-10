<?php
// class kecil khusus urusan upload foto buku ke folder img/
class ImageUploader {
    private $uploadDir;
    private $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private $maxSize = 2097152; // 2 MB

    public function __construct($uploadDir = null) {
        $this->uploadDir = $uploadDir ?? __DIR__ . '/../img/';
    }

    // kembalikan path baru (mis. img/123_sampul.jpg) kalau upload sukses,
    // kalau tidak ada upload / gagal, kembalikan $fallback (gambar lama atau default)
    public function upload($fileKey, $fallback = 'img/default.jpg') {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return $fallback;
        }
        $file = $_FILES[$fileKey];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $this->allowedExt, true) || $file['size'] > $this->maxSize) {
            return $fallback;
        }
        // pastikan isinya memang gambar, bukan file lain yang cuma ganti ekstensi
        if (@getimagesize($file['tmp_name']) === false) {
            return $fallback;
        }
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
        $clean = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $newName = time() . '_' . $clean . '.' . $ext;

        if (move_uploaded_file($file['tmp_name'], $this->uploadDir . $newName)) {
            return 'img/' . $newName;
        }
        return $fallback;
    }
}
