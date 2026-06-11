-- ============================================================
-- File   : rumah_sakit.sql
-- Desc   : Skrip SQL untuk membuat database dan tabel Rumah Sakit
-- DBMS   : MySQL
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Buat Database
-- ────────────────────────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `Rumah_Sakit`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_general_ci;

USE `Rumah_Sakit`;

-- ────────────────────────────────────────────────────────────
-- 2. Tabel Pasien
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pasien` (
    `id_pasien`           VARCHAR(20)    NOT NULL,
    `nama`                VARCHAR(100)   NOT NULL,
    `usia`                INT            NOT NULL,
    `jenis_kelamin`       ENUM('Laki-laki', 'Perempuan') NOT NULL,
    `tanggal_masuk`       DATE           NOT NULL,
    `tanggal_keluar`      DATE           NULL DEFAULT NULL,
    `lama_rawat`          INT            NOT NULL DEFAULT 0,
    `biaya_kamar_per_hari` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `asuransi`            ENUM('BPJS', 'Asuransi Swasta', 'Umum') NOT NULL DEFAULT 'Umum',
    `status`              ENUM('Rawat Inap', 'Rawat Jalan', 'Selesai', 'Dirujuk') NOT NULL DEFAULT 'Rawat Inap',

    PRIMARY KEY (`id_pasien`),

    -- Index untuk pencarian cepat
    INDEX `idx_nama`           (`nama`),
    INDEX `idx_tanggal_masuk`  (`tanggal_masuk`),
    INDEX `idx_asuransi`       (`asuransi`),
    INDEX `idx_status`         (`status`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- 3. Data Contoh (40 Pasien)
-- ────────────────────────────────────────────────────────────
INSERT INTO `pasien`
    (`id_pasien`, `nama`, `usia`, `jenis_kelamin`, `tanggal_masuk`, `tanggal_keluar`, `lama_rawat`, `biaya_kamar_per_hari`, `asuransi`, `status`)
VALUES
    ('PSN-001', 'Ahmad Fauzi',          35, 'Laki-laki',  '2026-01-05', '2026-01-10',  5, 350000.00, 'BPJS',             'Selesai'),
    ('PSN-002', 'Siti Nurhaliza',       28, 'Perempuan',  '2026-01-08', '2026-01-15',  7, 500000.00, 'Asuransi Swasta',  'Selesai'),
    ('PSN-003', 'Budi Santoso',         45, 'Laki-laki',  '2026-01-12', '2026-01-15',  3, 250000.00, 'Umum',             'Selesai'),
    ('PSN-004', 'Dewi Kartika',         60, 'Perempuan',  '2026-01-18', '2026-01-22',  4, 450000.00, 'BPJS',             'Selesai'),
    ('PSN-005', 'Reza Mahendra',        22, 'Laki-laki',  '2026-01-20', '2026-01-22',  2, 300000.00, 'Umum',             'Selesai'),
    ('PSN-006', 'Rina Wulandari',       31, 'Perempuan',  '2026-02-01', '2026-02-06',  5, 400000.00, 'Asuransi Swasta',  'Selesai'),
    ('PSN-007', 'Hendra Gunawan',       50, 'Laki-laki',  '2026-02-03', '2026-02-13', 10, 350000.00, 'BPJS',             'Selesai'),
    ('PSN-008', 'Putri Amelia',         19, 'Perempuan',  '2026-02-10', '2026-02-13',  3, 275000.00, 'Umum',             'Selesai'),
    ('PSN-009', 'Dimas Prasetyo',       40, 'Laki-laki',  '2026-02-14', '2026-02-20',  6, 500000.00, 'Asuransi Swasta',  'Selesai'),
    ('PSN-010', 'Lestari Handayani',    55, 'Perempuan',  '2026-02-18', '2026-02-25',  7, 350000.00, 'BPJS',             'Selesai'),
    ('PSN-011', 'Fajar Nugroho',        33, 'Laki-laki',  '2026-03-01', '2026-03-04',  3, 300000.00, 'Umum',             'Selesai'),
    ('PSN-012', 'Anisa Rahma',          26, 'Perempuan',  '2026-03-05', '2026-03-09',  4, 450000.00, 'BPJS',             'Selesai'),
    ('PSN-013', 'Joko Widodo',          48, 'Laki-laki',  '2026-03-10', '2026-03-18',  8, 600000.00, 'Asuransi Swasta',  'Selesai'),
    ('PSN-014', 'Maya Sari',            37, 'Perempuan',  '2026-03-15', '2026-03-17',  2, 250000.00, 'Umum',             'Selesai'),
    ('PSN-015', 'Irfan Hakim',          29, 'Laki-laki',  '2026-03-20', '2026-03-26',  6, 400000.00, 'BPJS',             'Selesai'),
    ('PSN-016', 'Nurul Hidayah',        42, 'Perempuan',  '2026-04-01', '2026-04-05',  4, 350000.00, 'Umum',             'Selesai'),
    ('PSN-017', 'Andi Wijaya',          58, 'Laki-laki',  '2026-04-03', '2026-04-12',  9, 500000.00, 'Asuransi Swasta',  'Selesai'),
    ('PSN-018', 'Ratna Dewi',           24, 'Perempuan',  '2026-04-08', '2026-04-11',  3, 275000.00, 'BPJS',             'Selesai'),
    ('PSN-019', 'Surya Pratama',        36, 'Laki-laki',  '2026-04-14', '2026-04-19',  5, 450000.00, 'Umum',             'Selesai'),
    ('PSN-020', 'Indah Permatasari',    52, 'Perempuan',  '2026-04-20', '2026-04-27',  7, 600000.00, 'Asuransi Swasta',  'Selesai'),
    ('PSN-021', 'Rizky Ramadhan',       20, 'Laki-laki',  '2026-05-01', '2026-05-03',  2, 250000.00, 'BPJS',             'Selesai'),
    ('PSN-022', 'Kartini Suharto',      65, 'Perempuan',  '2026-05-02', '2026-05-10',  8, 400000.00, 'Umum',             'Selesai'),
    ('PSN-023', 'Wahyu Hidayat',        44, 'Laki-laki',  '2026-05-05', '2026-05-11',  6, 350000.00, 'Asuransi Swasta',  'Selesai'),
    ('PSN-024', 'Fitri Yani',           30, 'Perempuan',  '2026-05-10', '2026-05-14',  4, 500000.00, 'BPJS',             'Selesai'),
    ('PSN-025', 'Bayu Aditya',          27, 'Laki-laki',  '2026-05-15', '2026-05-18',  3, 300000.00, 'Umum',             'Selesai'),
    ('PSN-026', 'Sri Mulyani',          47, 'Perempuan',  '2026-05-18', '2026-05-23',  5, 450000.00, 'BPJS',             'Selesai'),
    ('PSN-027', 'Galih Permana',        38, 'Laki-laki',  '2026-05-22', '2026-05-30',  8, 550000.00, 'Asuransi Swasta',  'Selesai'),
    ('PSN-028', 'Wulan Dari',           23, 'Perempuan',  '2026-05-25', '2026-05-28',  3, 275000.00, 'Umum',             'Selesai'),
    ('PSN-029', 'Teguh Saputra',        54, 'Laki-laki',  '2026-05-28', '2026-06-03',  6, 400000.00, 'BPJS',             'Selesai'),
    ('PSN-030', 'Larasati Putri',       32, 'Perempuan',  '2026-06-01', '2026-06-05',  4, 350000.00, 'Asuransi Swasta',  'Selesai'),
    ('PSN-031', 'Arief Rahman',         41, 'Laki-laki',  '2026-06-02', '2026-06-09',  7, 500000.00, 'BPJS',             'Selesai'),
    ('PSN-032', 'Mega Puspita',         18, 'Perempuan',  '2026-06-03', '2026-06-05',  2, 250000.00, 'Umum',             'Selesai'),
    ('PSN-033', 'Yusuf Maulana',        49, 'Laki-laki',  '2026-06-04', NULL,          5, 450000.00, 'Asuransi Swasta',  'Rawat Inap'),
    ('PSN-034', 'Citra Kirana',         34, 'Perempuan',  '2026-06-05', NULL,          4, 350000.00, 'BPJS',             'Rawat Inap'),
    ('PSN-035', 'Eko Prasetyo',         56, 'Laki-laki',  '2026-06-06', NULL,          3, 600000.00, 'Umum',             'Rawat Inap'),
    ('PSN-036', 'Dian Sastro',          39, 'Perempuan',  '2026-06-07', NULL,          2, 500000.00, 'Asuransi Swasta',  'Rawat Inap'),
    ('PSN-037', 'Rangga Kusuma',        25, 'Laki-laki',  '2026-06-08', NULL,          1, 300000.00, 'BPJS',             'Rawat Jalan'),
    ('PSN-038', 'Nadia Safitri',        43, 'Perempuan',  '2026-06-09', NULL,          3, 400000.00, 'Umum',             'Rawat Inap'),
    ('PSN-039', 'Taufik Hidayat',       51, 'Laki-laki',  '2026-06-10', NULL,          1, 350000.00, 'BPJS',             'Dirujuk'),
    ('PSN-040', 'Ayu Lestari',          29, 'Perempuan',  '2026-06-11', NULL,          1, 275000.00, 'Asuransi Swasta',  'Rawat Jalan');
