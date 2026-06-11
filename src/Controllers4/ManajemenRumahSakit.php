<?php

namespace App\Controllers;

use App\Database\KoneksiDatabase;
use App\Models\PasienBPJS;
use App\Models\PasienUmum;
use App\Models\PasienAsuransiSwasta;

/**
 * Kelas ManajemenRumahSakit
 * 
 * Controller utama yang menghubungkan database dengan model-model pasien OOP.
 * Mengelola operasi CRUD untuk data pasien dan menghitung data statistik secara dinamis.
 * 
 * @package App\Controllers
 */
class ManajemenRumahSakit
{
    private \PDO $db;

    public function __construct()
    {
        // Mendapatkan koneksi tunggal PDO via Singleton KoneksiDatabase
        $this->db = KoneksiDatabase::getInstance()->getKoneksi();
    }

    /**
     * Mengambil daftar pasien dan mengubah setiap baris data database menjadi
     * instance objek konkret (BPJS, Swasta, Umum) sesuai dengan kolom asuransi.
     * 
     * @param string|null $search Kata kunci pencarian nama atau ID Pasien
     * @param string|null $asuransi Filter kategori asuransi
     * @param string|null $status Filter status perawatan
     * @return array Array objek Pasien
     */
    public function getAllPasien(?string $search = null, ?string $asuransi = null, ?string $status = null): array
    {
        $query = "SELECT * FROM pasien WHERE 1=1";
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $query .= " AND (id_pasien LIKE :search OR nama LIKE :search)";
            $params['search'] = '%' . trim($search) . '%';
        }

        if ($asuransi !== null && trim($asuransi) !== '') {
            $query .= " AND asuransi = :asuransi";
            $params['asuransi'] = trim($asuransi);
        }

        if ($status !== null && trim($status) !== '') {
            $query .= " AND status = :status";
            $params['status'] = trim($status);
        }

        $query .= " ORDER BY id_pasien ASC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            $pasiens = [];
            foreach ($rows as $row) {
                // Konversi tanggal ke lama rawat jika lama rawat adalah 0 atau null
                $lamaRawat = (int)$row['lama_rawat'];
                if ($lamaRawat <= 0) {
                    $tglMasuk = new \DateTime($row['tanggal_masuk']);
                    $tglKeluar = !empty($row['tanggal_keluar']) ? new \DateTime($row['tanggal_keluar']) : new \DateTime();
                    $diff = $tglMasuk->diff($tglKeluar);
                    $lamaRawat = max(1, $diff->days);
                }

                // Instansiasi objek konkret berdasarkan jenis asuransi (Polimorfisme)
                if ($row['asuransi'] === 'BPJS') {
                    $pasiens[] = new PasienBPJS(
                        $row['id_pasien'],
                        $row['nama'],
                        (int)$row['usia'],
                        $lamaRawat,
                        (float)$row['biaya_kamar_per_hari'],
                        $row['nomor_pbi'] ?? '',
                        $row['faskes_asal'] ?? '',
                        $row['kelas_kamar'] ?? ''
                    );
                } elseif ($row['asuransi'] === 'Asuransi Swasta') {
                    $pasiens[] = new PasienAsuransiSwasta(
                        $row['id_pasien'],
                        $row['nama'],
                        (int)$row['usia'],
                        $lamaRawat,
                        (float)$row['biaya_kamar_per_hari'],
                        $row['nama_provider'] ?? '',
                        $row['nomor_polis'] ?? '',
                        (float)($row['limit_cover'] ?? 0.0)
                    );
                } else {
                    $pasiens[] = new PasienUmum(
                        $row['id_pasien'],
                        $row['nama'],
                        (int)$row['usia'],
                        $lamaRawat,
                        (float)$row['biaya_kamar_per_hari'],
                        $row['nik'] ?? '',
                        $row['metode_pembayaran'] ?? ''
                    );
                }
            }

            return $pasiens;

        } catch (\PDOException $e) {
            error_log("Gagal mengambil data pasien: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Menghitung data statistik secara dinamis menggunakan method OOP hitungTotalBiaya()
     * untuk kalkulasi total billing secara akurat berdasarkan polimorfisme kelas.
     * 
     * @return array
     */
    public function getStats(): array
    {
        $allPasiens = $this->getAllPasien();

        $stats = [
            'total_pasien' => count($allPasiens),
            'status' => [
                'rawat_inap' => 0,
                'rawat_jalan' => 0,
                'selesai' => 0,
                'dirujuk' => 0
            ],
            'asuransi' => [
                'bpjs' => 0,
                'swasta' => 0,
                'umum' => 0
            ],
            'total_estimasi_biaya' => 0.0
        ];

        // Query database secara langsung untuk status agar hemat memori (opsional, tapi kalkulasi objek lebih dinamis)
        try {
            $stmt = $this->db->query("SELECT status, COUNT(*) as jumlah FROM pasien GROUP BY status");
            while ($row = $stmt->fetch()) {
                $statusKey = strtolower(str_replace(' ', '_', $row['status']));
                if (isset($stats['status'][$statusKey])) {
                    $stats['status'][$statusKey] = (int)$row['jumlah'];
                }
            }

            $stmtAsuransi = $this->db->query("SELECT asuransi, COUNT(*) as jumlah FROM pasien GROUP BY asuransi");
            while ($row = $stmtAsuransi->fetch()) {
                $key = $row['asuransi'];
                if ($key === 'BPJS') {
                    $stats['asuransi']['bpjs'] = (int)$row['jumlah'];
                } elseif ($key === 'Asuransi Swasta') {
                    $stats['asuransi']['swasta'] = (int)$row['jumlah'];
                } else {
                    $stats['asuransi']['umum'] = (int)$row['jumlah'];
                }
            }
        } catch (\PDOException $e) {
            error_log("Gagal mengambil ringkasan statistik: " . $e->getMessage());
        }

        // Hitung total billing dari semua objek pasien secara polimorfis
        foreach ($allPasiens as $pasien) {
            $stats['total_estimasi_biaya'] += $pasien->hitungTotalBiaya();
        }

        return $stats;
    }

    /**
     * Mendaftarkan pasien baru ke database MySQL.
     * 
     * @param array $data Data pendaftaran pasien
     * @return bool True jika berhasil, False jika gagal
     */
    public function tambahPasien(array $data): bool
    {
        // Generate ID Pasien baru secara otomatis (contoh: PSN-041)
        try {
            $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(id_pasien, 5) AS UNSIGNED)) as max_id FROM pasien");
            $res = $stmt->fetch();
            $nextIdNum = ($res['max_id'] !== null) ? ((int)$res['max_id'] + 1) : 1;
            $newId = 'PSN-' . str_pad($nextIdNum, 3, '0', STR_PAD_LEFT);
        } catch (\PDOException $e) {
            $newId = 'PSN-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
        }

        // Kalkulasi lama rawat dari tanggal masuk dan keluar
        $tglMasuk = new \DateTime($data['tanggal_masuk']);
        $tglKeluarVal = !empty($data['tanggal_keluar']) ? $data['tanggal_keluar'] : null;
        
        if ($tglKeluarVal !== null) {
            $tglKeluar = new \DateTime($tglKeluarVal);
            $diff = $tglMasuk->diff($tglKeluar);
            $lamaRawat = max(1, $diff->days);
        } else {
            // Jika masih dirawat (Rawat Inap/Jalan), hitung selisih dari hari masuk sampai hari ini
            $tglSekarang = new \DateTime();
            if ($tglSekarang > $tglMasuk) {
                $diff = $tglMasuk->diff($tglSekarang);
                $lamaRawat = max(1, $diff->days);
            } else {
                $lamaRawat = 1;
            }
        }

        $query = "INSERT INTO pasien (
            id_pasien, nama, usia, jenis_kelamin, tanggal_masuk, tanggal_keluar, lama_rawat, 
            biaya_kamar_per_hari, asuransi, status, 
            nomor_pbi, faskes_asal, kelas_kamar, 
            nik, metode_pembayaran, 
            nama_provider, nomor_polis, limit_cover
        ) VALUES (
            :id_pasien, :nama, :usia, :jenis_kelamin, :tanggal_masuk, :tanggal_keluar, :lama_rawat, 
            :biaya_kamar_per_hari, :asuransi, :status, 
            :nomor_pbi, :faskes_asal, :kelas_kamar, 
            :nik, :metode_pembayaran, 
            :nama_provider, :nomor_polis, :limit_cover
        )";

        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                'id_pasien' => $newId,
                'nama' => trim($data['nama']),
                'usia' => (int)$data['usia'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tanggal_masuk' => $data['tanggal_masuk'],
                'tanggal_keluar' => $tglKeluarVal,
                'lama_rawat' => $lamaRawat,
                'biaya_kamar_per_hari' => (float)$data['biaya_kamar_per_hari'],
                'asuransi' => $data['asuransi'],
                'status' => $data['status'],
                
                // Parameter BPJS
                'nomor_pbi' => $data['asuransi'] === 'BPJS' ? ($data['nomor_pbi'] ?? null) : null,
                'faskes_asal' => $data['asuransi'] === 'BPJS' ? ($data['faskes_asal'] ?? null) : null,
                'kelas_kamar' => $data['asuransi'] === 'BPJS' ? ($data['kelas_kamar'] ?? null) : null,
                
                // Parameter Umum
                'nik' => $data['asuransi'] === 'Umum' ? ($data['nik'] ?? null) : null,
                'metode_pembayaran' => $data['asuransi'] === 'Umum' ? ($data['metode_pembayaran'] ?? null) : null,
                
                // Parameter Asuransi Swasta
                'nama_provider' => $data['asuransi'] === 'Asuransi Swasta' ? ($data['nama_provider'] ?? null) : null,
                'nomor_polis' => $data['asuransi'] === 'Asuransi Swasta' ? ($data['nomor_polis'] ?? null) : null,
                'limit_cover' => $data['asuransi'] === 'Asuransi Swasta' ? (float)($data['limit_cover'] ?? 0.0) : null
            ]);
        } catch (\PDOException $e) {
            error_log("Gagal menambahkan pasien baru: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Menghapus data pasien dari database MySQL.
     * 
     * @param string $id ID Pasien yang akan dihapus
     * @return bool True jika berhasil, False jika gagal
     */
    public function hapusPasien(string $id): bool
    {
        $query = "DELETE FROM pasien WHERE id_pasien = :id";
        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            error_log("Gagal menghapus data pasien: " . $e->getMessage());
            return false;
        }
    }
}
