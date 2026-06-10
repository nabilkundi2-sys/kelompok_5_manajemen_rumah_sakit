<?php

namespace App\Models;

use App\Core\Pasien;

/**
 * Kelas PasienBPJS
 * 
 * Subkelas konkret dari Pasien yang merepresentasikan pasien dengan jaminan BPJS.
 * Menerapkan prinsip OOP Pewarisan (Inheritance) dan Polimorfisme (Polymorphism).
 * 
 * Rumus hitungTotalBiaya:
 * Total = (lamaRawat * biayaKamarPerHari) * 10%
 * (BPJS menanggung 90% dari tarif dasar)
 * 
 * @package App\Models
 */
class PasienBPJS extends Pasien
{
    // Enkapsulasi: Atribut tambahan khusus PasienBPJS
    private string $nomorPBI;
    private string $faskesAsal;
    private string $kelasKamar;

    /**
     * Konstruktor PasienBPJS.
     * Memanggil konstruktor induk dan menambahkan atribut khusus BPJS.
     *
     * @param string $id_pasien
     * @param string $nama
     * @param int $usia
     * @param int $lamaRawat
     * @param float $biayaKamarPerHari
     * @param string $nomorPBI
     * @param string $faskesAsal
     * @param string $kelasKamar
     */
    public function __construct(
        string $id_pasien,
        string $nama,
        int $usia,
        int $lamaRawat,
        float $biayaKamarPerHari,
        string $nomorPBI,
        string $faskesAsal,
        string $kelasKamar
    ) {
        // Memanggil konstruktor kelas induk (Pasien)
        parent::__construct($id_pasien, $nama, $usia, $lamaRawat, $biayaKamarPerHari);

        $this->nomorPBI   = $nomorPBI;
        $this->faskesAsal = $faskesAsal;
        $this->kelasKamar = $kelasKamar;
    }

    // --- Getter dan Setter (Enkapsulasi) ---

    public function getNomorPBI(): string
    {
        return $this->nomorPBI;
    }

    public function setNomorPBI(string $nomorPBI): void
    {
        $this->nomorPBI = $nomorPBI;
    }

    public function getFaskesAsal(): string
    {
        return $this->faskesAsal;
    }

    public function setFaskesAsal(string $faskesAsal): void
    {
        $this->faskesAsal = $faskesAsal;
    }

    public function getKelasKamar(): string
    {
        return $this->kelasKamar;
    }

    public function setKelasKamar(string $kelasKamar): void
    {
        $this->kelasKamar = $kelasKamar;
    }

    // --- Implementasi Metode Abstrak (Polimorfisme) ---

    /**
     * Override hitungTotalBiaya() untuk Pasien BPJS.
     * BPJS menanggung 90% biaya, pasien hanya membayar 10%.
     * 
     * Rumus: (lamaRawat * biayaKamarPerHari) * 10%
     *
     * @return float
     */
    public function hitungTotalBiaya(): float
    {
        $biayaDasar = $this->lamaRawat * $this->biayaKamarPerHari;
        return $biayaDasar * 0.10;
    }

    /**
     * Override cetakKlaimLayanan() untuk Pasien BPJS.
     * Mengembalikan array rincian klaim layanan BPJS.
     *
     * @return array
     */
    public function cetakKlaimLayanan(): array
    {
        $biayaDasar = $this->lamaRawat * $this->biayaKamarPerHari;

        return [
            'id_pasien'      => $this->id_pasien,
            'nama'           => $this->nama,
            'usia'           => $this->usia,
            'jenis'          => 'BPJS',
            'nomor_pbi'      => $this->nomorPBI,
            'faskes_asal'    => $this->faskesAsal,
            'kelas_kamar'    => $this->kelasKamar,
            'lama_rawat'     => $this->lamaRawat . ' hari',
            'biaya_kamar'    => $this->biayaKamarPerHari,
            'biaya_dasar'    => $biayaDasar,
            'subsidi_bpjs'   => $biayaDasar * 0.90,
            'total_bayar'    => $this->hitungTotalBiaya(),
            'keterangan'     => 'BPJS menanggung 90% dari biaya dasar',
        ];
    }
}