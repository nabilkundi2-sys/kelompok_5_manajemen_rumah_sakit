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
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                self::DB_HOST,
                self::DB_PORT,
                self::DB_NAME,
                self::DB_CHARSET
            );

            $options = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->koneksi = new \PDO($dsn, self::DB_USER, self::DB_PASS, $options);

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
