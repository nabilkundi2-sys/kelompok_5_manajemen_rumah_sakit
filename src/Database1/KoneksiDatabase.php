<?php

namespace App\Database;

/**
 * Kelas KoneksiDatabase
 * 
 * Mengelola koneksi ke database MySQL menggunakan PDO.
 * Menerapkan pola Singleton agar hanya ada satu koneksi aktif.
 * 
 * @package App\Database
 */
class KoneksiDatabase
{
    // ─── Konfigurasi Database ────────────────────────────────
    private const DB_HOST = 'localhost';
    private const DB_PORT = '3306';
    private const DB_NAME = 'Rumah_Sakit';
    private const DB_USER = 'root';
    private const DB_PASS = '';           // Sesuaikan dengan password MySQL Anda
    private const DB_CHARSET = 'utf8mb4';

    // ─── Singleton Instance ──────────────────────────────────
    private static ?KoneksiDatabase $instance = null;
    private ?\PDO $koneksi = null;

    /**
     * Konstruktor privat — mencegah instansiasi langsung dari luar (Singleton).
     * Membuat koneksi PDO ke MySQL.
     */
    private function __construct()
    {
        try {
            // Hubungkan ke host terlebih dahulu (tanpa dbname) untuk menghindari error 1049
            $dsnNoDb = sprintf(
                'mysql:host=%s;port=%s;charset=%s',
                self::DB_HOST,
                self::DB_PORT,
                self::DB_CHARSET
            );

            $options = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => true, // Izinkan multi-statements untuk inisialisasi awal
            ];

            $this->koneksi = new \PDO($dsnNoDb, self::DB_USER, self::DB_PASS, $options);

            // Buat database jika belum ada
            $this->koneksi->exec("CREATE DATABASE IF NOT EXISTS `" . self::DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $this->koneksi->exec("USE `" . self::DB_NAME . "`");

            // Matikan kembali emulate prepares untuk keamanan query selanjutnya jika diperlukan
            $this->koneksi->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

            // Cek apakah tabel pasien sudah terbuat
            $tabelAda = false;
            try {
                $check = $this->koneksi->query("SHOW TABLES LIKE 'pasien'");
                $tabelAda = ($check->rowCount() > 0);
            } catch (\PDOException $e) {
                $tabelAda = false;
            }

            // Jika tabel belum ada, impor file SQL secara otomatis
            if (!$tabelAda) {
                $sqlFile = __DIR__ . '/rumah_sakit.sql';
                if (file_exists($sqlFile)) {
                    $sqlContent = file_get_contents($sqlFile);
                    // Gunakan koneksi dengan emulate_prepares true untuk mengeksekusi script SQL panjang
                    $this->koneksi->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
                    $this->koneksi->exec($sqlContent);
                    $this->koneksi->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
                }
            }

        } catch (\PDOException $e) {
            die("❌ Gagal terhubung ke database: " . $e->getMessage());
        }
    }

    /**
     * Mendapatkan instance tunggal KoneksiDatabase (Singleton Pattern).
     * 
     * @return KoneksiDatabase
     */
    public static function getInstance(): KoneksiDatabase
    {
        if (self::$instance === null) {
            self::$instance = new KoneksiDatabase();
        }
        return self::$instance;
    }

    /**
     * Mendapatkan objek PDO untuk menjalankan query.
     * 
     * @return \PDO
     */
    public function getKoneksi(): \PDO
    {
        return $this->koneksi;
    }

    /**
     * Menutup koneksi database.
     */
    public function tutupKoneksi(): void
    {
        $this->koneksi = null;
        self::$instance = null;
    }

    /**
     * Mencegah kloning objek (bagian dari Singleton Pattern).
     */
    private function __clone() {}

    /**
     * Mencegah deserialisasi objek (bagian dari Singleton Pattern).
     */
    public function __wakeup()
    {
        throw new \Exception("Tidak dapat melakukan unserialize pada Singleton.");
    }
}
