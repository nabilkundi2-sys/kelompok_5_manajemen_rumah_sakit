<?php

namespace App\Models;

use App\Core\Pasien;

/**
 * Kelas PasienUmum
 * 
 * Subkelas konkret dari Pasien yang merepresentasikan pasien umum / mandiri (tanpa jaminan).
 * Menerapkan prinsip OOP Pewarisan (Inheritance) dan Polimorfisme (Polymorphism).
 * 
 * Rumus hitungTotalBiaya:
 * Total = (lamaRawat * biayaKamarPerHari) + Biaya Administrasi Rp 150.000
 * 
 * @package App\Models
 */
class PasienUmum extends Pasien
{
    // Enkapsulasi: Atribut tambahan khusus PasienUmum
    private string $nik;
    private string $metodePembayaran;

    // Konstanta biaya administrasi tambahan untuk pasien umum
    const BIAYA_ADMIN = 150000;

    /**
     * Konstruktor PasienUmum.
     * Memanggil konstruktor induk dan menambahkan atribut khusus Pasien Umum.
     *
     * @param string $id_pasien
     * @param string $nama
     * @param int $usia
     * @param int $lamaRawat
     * @param float $biayaKamarPerHari
     * @param string $nik
     * @param string $metodePembayaran
     */
    public function __construct(
        string $id_pasien,
        string $nama,
        int $usia,
        int $lamaRawat,
        float $biayaKamarPerHari,
        string $nik,
        string $metodePembayaran
    ) {
        // Memanggil konstruktor kelas induk (Pasien)
        parent::__construct($id_pasien, $nama, $usia, $lamaRawat, $biayaKamarPerHari);

        $this->nik              = $nik;
        $this->metodePembayaran = $metodePembayaran;
    }

    // --- Getter dan Setter (Enkapsulasi) ---

    public function getNik(): string
    {
        return $this->nik;
    }

    public function setNik(string $nik): void
    {
        $this->nik = $nik;
    }

    public function getMetodePembayaran(): string
    {
        return $this->metodePembayaran;
    }

    public function setMetodePembayaran(string $metodePembayaran): void
    {
        $this->metodePembayaran = $metodePembayaran;
    }

    // --- Implementasi Metode Abstrak (Polimorfisme) ---

    /**
     * Override hitungTotalBiaya() untuk Pasien Umum.
     * Pasien umum membayar penuh biaya kamar ditambah biaya administrasi Rp 150.000.
     *
     * Rumus: (lamaRawat * biayaKamarPerHari) + Rp 150.000
     *
     * @return float
     */
    public function hitungTotalBiaya(): float
    {
        $biayaDasar = $this->lamaRawat * $this->biayaKamarPerHari;
        return $biayaDasar + self::BIAYA_ADMIN;
    }

    /**
     * Override cetakKlaimLayanan() untuk Pasien Umum.
     * Mengembalikan array rincian klaim layanan Pasien Umum.
     *
     * @return array
     */
    public function cetakKlaimLayanan(): array
    {
        $biayaDasar = $this->lamaRawat * $this->biayaKamarPerHari;

        return [
            'id_pasien'         => $this->id_pasien,
            'nama'              => $this->nama,
            'usia'              => $this->usia,
            'jenis'             => 'UMUM',
            'nik'               => $this->nik,
            'metode_pembayaran' => $this->metodePembayaran,
            'lama_rawat'        => $this->lamaRawat . ' hari',
            'biaya_kamar'       => $this->biayaKamarPerHari,
            'biaya_dasar'       => $biayaDasar,
            'biaya_admin'       => self::BIAYA_ADMIN,
            'total_bayar'       => $this->hitungTotalBiaya(),
            'keterangan'        => 'Pasien umum membayar penuh + biaya administrasi Rp 150.000',
        ];
    }
}