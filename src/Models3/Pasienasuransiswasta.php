<?php

namespace App\Models;

use App\Core\Pasien;

/**
 * Kelas PasienAsuransiSwasta
 * 
 * Subkelas konkret dari Pasien yang merepresentasikan pasien dengan jaminan Asuransi Swasta.
 * Menerapkan prinsip OOP Pewarisan (Inheritance) dan Polimorfisme (Polymorphism).
 * 
 * Rumus hitungTotalBiaya:
 * - Jika (lamaRawat * biayaKamarPerHari) > limitCover → total = sisa biaya yang tidak tercover
 * - Jika (lamaRawat * biayaKamarPerHari) <= limitCover → total = 0 (semua ditanggung asuransi)
 * 
 * @package App\Models
 */
class PasienAsuransiSwasta extends Pasien
{
    // Enkapsulasi: Atribut tambahan khusus PasienAsuransiSwasta
    private string $namaProvider;
    private string $nomorPolis;
    private float  $limitCover;

    /**
     * Konstruktor PasienAsuransiSwasta.
     * Memanggil konstruktor induk dan menambahkan atribut khusus Asuransi Swasta.
     *
     * @param string $id_pasien
     * @param string $nama
     * @param int $usia
     * @param int $lamaRawat
     * @param float $biayaKamarPerHari
     * @param string $namaProvider
     * @param string $nomorPolis
     * @param float $limitCover
     */
    public function __construct(
        string $id_pasien,
        string $nama,
        int $usia,
        int $lamaRawat,
        float $biayaKamarPerHari,
        string $namaProvider,
        string $nomorPolis,
        float $limitCover
    ) {
        // Memanggil konstruktor kelas induk (Pasien)
        parent::__construct($id_pasien, $nama, $usia, $lamaRawat, $biayaKamarPerHari);

        $this->namaProvider = $namaProvider;
        $this->nomorPolis   = $nomorPolis;
        $this->limitCover   = $limitCover;
    }

    // --- Getter dan Setter (Enkapsulasi) ---

    public function getNamaProvider(): string
    {
        return $this->namaProvider;
    }

    public function setNamaProvider(string $namaProvider): void
    {
        $this->namaProvider = $namaProvider;
    }

    public function getNomorPolis(): string
    {
        return $this->nomorPolis;
    }

    public function setNomorPolis(string $nomorPolis): void
    {
        $this->nomorPolis = $nomorPolis;
    }

    public function getLimitCover(): float
    {
        return $this->limitCover;
    }

    public function setLimitCover(float $limitCover): void
    {
        if ($limitCover < 0) {
            throw new \InvalidArgumentException("Limit cover tidak boleh kurang dari 0.");
        }
        $this->limitCover = $limitCover;
    }

    // --- Implementasi Metode Abstrak (Polimorfisme) ---

    /**
     * Override hitungTotalBiaya() untuk Pasien Asuransi Swasta.
     * Jika total biaya melebihi limit cover, pasien membayar sisanya.
     * Jika tidak melebihi, pasien tidak membayar (Rp 0).
     *
     * Rumus:
     * - biayaDasar > limitCover → total = biayaDasar - limitCover
     * - biayaDasar <= limitCover → total = 0
     *
     * @return float
     */
    public function hitungTotalBiaya(): float
    {
        $biayaDasar = $this->lamaRawat * $this->biayaKamarPerHari;

        if ($biayaDasar > $this->limitCover) {
            return $biayaDasar - $this->limitCover;
        }

        return 0;
    }

    /**
     * Override cetakKlaimLayanan() untuk Pasien Asuransi Swasta.
     * Mengembalikan array rincian klaim layanan Asuransi Swasta.
     *
     * @return array
     */
    public function cetakKlaimLayanan(): array
    {
        $biayaDasar  = $this->lamaRawat * $this->biayaKamarPerHari;
        $totalBayar  = $this->hitungTotalBiaya();
        $tercover    = $biayaDasar <= $this->limitCover ? $biayaDasar : $this->limitCover;
        $keterangan  = $biayaDasar <= $this->limitCover
            ? 'Semua biaya ditanggung asuransi (dalam limit cover)'
            : 'Biaya melebihi limit cover, sisa dibayar pasien';

        return [
            'id_pasien'     => $this->id_pasien,
            'nama'          => $this->nama,
            'usia'          => $this->usia,
            'jenis'         => 'ASURANSI',
            'nama_provider' => $this->namaProvider,
            'nomor_polis'   => $this->nomorPolis,
            'limit_cover'   => $this->limitCover,
            'lama_rawat'    => $this->lamaRawat . ' hari',
            'biaya_kamar'   => $this->biayaKamarPerHari,
            'biaya_dasar'   => $biayaDasar,
            'ditanggung'    => $tercover,
            'total_bayar'   => $totalBayar,
            'keterangan'    => $keterangan,
        ];
    }
}
