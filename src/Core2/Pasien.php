<?php

namespace App\Core;

/**
 * Kelas Pasien
 * 
 * Kelas abstrak yang mewakili data pasien secara umum.
 * Menerapkan prinsip OOP Abstraksi dan Enkapsulasi.
 * 
 * @package App\Core
 */
abstract class Pasien
{
    // Enkapsulasi: Properti yang dilindungi (protected), dapat diakses oleh subkelas tetapi tidak langsung dari luar.
    protected string $id_pasien;
    protected string $nama;
    protected int $usia;
    protected int $lamaRawat;
    protected float $biayaKamarPerHari;

    /**
     * Konstruktor untuk menginisialisasi atribut pasien.
     * 
     * @param string $id_pasien
     * @param string $nama
     * @param int $usia
     * @param int $lamaRawat
     * @param float $biayaKamarPerHari
     */
    public function __construct(string $id_pasien, string $nama, int $usia, int $lamaRawat, float $biayaKamarPerHari)
    {
        $this->id_pasien = $id_pasien;
        $this->nama = $nama;
        $this->usia = $usia;
        $this->lamaRawat = $lamaRawat;
        $this->biayaKamarPerHari = $biayaKamarPerHari;
    }

    // --- Getter dan Setter (Enkapsulasi) ---

    public function getIdPasien(): string
    {
        return $this->id_pasien;
    }

    public function setIdPasien(string $id_pasien): void
    {
        $this->id_pasien = $id_pasien;
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    public function setNama(string $nama): void
    {
        $this->nama = $nama;
    }

    public function getUsia(): int
    {
        return $this->usia;
    }

    public function setUsia(int $usia): void
    {
        if ($usia < 0) {
            throw new \InvalidArgumentException("Usia tidak boleh kurang dari 0.");
        }
        $this->usia = $usia;
    }

    public function getLamaRawat(): int
    {
        return $this->lamaRawat;
    }

    public function setLamaRawat(int $lamaRawat): void
    {
        if ($lamaRawat < 0) {
            throw new \InvalidArgumentException("Lama rawat tidak boleh kurang dari 0.");
        }
        $this->lamaRawat = $lamaRawat;
    }

    public function getBiayaKamarPerHari(): float
    {
        return $this->biayaKamarPerHari;
    }

    public function setBiayaKamarPerHari(float $biayaKamarPerHari): void
    {
        if ($biayaKamarPerHari < 0) {
            throw new \InvalidArgumentException("Biaya kamar per hari tidak boleh kurang dari 0.");
        }
        $this->biayaKamarPerHari = $biayaKamarPerHari;
    }

    // --- Metode Abstrak ---

    /**
     * Menghitung total biaya layanan medis pasien.
     * Metode ini harus diimplementasikan oleh subkelas konkret (BPJS, Asuransi Swasta, Umum).
     * 
     * @return float
     */
    abstract public function hitungTotalBiaya(): float;

    /**
     * Membuat / Mencetak rincian klaim layanan untuk pasien.
     * Metode ini harus diimplementasikan oleh subkelas konkret (BPJS, Asuransi Swasta, Umum).
     * 
     * @return array
     */
    abstract public function cetakKlaimLayanan(): array;
}
