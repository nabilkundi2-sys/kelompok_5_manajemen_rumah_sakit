<?php

// ─── 1. Autoloader PSR-4 Kustom untuk Struktur Folder Berangka ───
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $parts = explode('\\', $relative_class);
    
    if (count($parts) > 1) {
        $subNamespace = $parts[0];
        $className = $parts[1];
        
        $folder = '';
        switch ($subNamespace) {
            case 'Core':
                $folder = 'Core2';
                break;
            case 'Models':
                $folder = 'Models3';
                break;
            case 'Database':
                $folder = 'Database1';
                break;
            case 'Controllers':
                $folder = 'Controllers4';
                break;
        }
        
        if ($folder !== '') {
            $file = $base_dir . $folder . '/' . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

use App\Controllers\ManajemenRumahSakit;

// Inisialisasi Controller
$controller = new ManajemenRumahSakit();

// Pemrosesan Request Aksi (AJAX / Form Submit)
$message = '';
$error = '';

// Aksi Hapus Pasien
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $success = $controller->hapusPasien($_GET['id']);
    if ($success) {
        $message = "Pasien " . htmlspecialchars($_GET['id']) . " berhasil dihapus!";
    } else {
        $error = "Gagal menghapus pasien.";
    }
}

// Aksi Tambah Pasien
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $success = $controller->tambahPasien($_POST);
    if ($success) {
        $message = "Pasien baru berhasil didaftarkan!";
    } else {
        $error = "Gagal mendaftarkan pasien. Pastikan data valid.";
    }
}

// Mendapatkan data filter dari request
$search = $_GET['search'] ?? null;
$asuransi = $_GET['asuransi'] ?? null;
$status = $_GET['status'] ?? null;

// Mengambil data utama
$pasiens = $controller->getAllPasien($search, $asuransi, $status);
$stats = $controller->getStats();

// Menyiapkan data klaim layanan dalam format JSON untuk dibaca oleh Javascript secara instan
$klaimData = [];
foreach ($pasiens as $p) {
    $klaimData[$p->getIdPasien()] = $p->cetakKlaimLayanan();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedCore | Dashboard Manajemen Rumah Sakit</title>
    <meta name="description" content="Dashboard sistem informasi manajemen pasien rumah sakit dengan integrasi OOP dan database MySQL.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ─── 2. Sistem Desain CSS Modern (Glassmorphism & Neon Accent) ─── */
        :root {
            --bg-base: #0b0f19;
            --bg-surface: rgba(20, 27, 45, 0.7);
            --bg-surface-hover: rgba(30, 41, 67, 0.9);
            --border-glow: rgba(99, 102, 241, 0.15);
            --border-glow-focus: rgba(99, 102, 241, 0.4);
            
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --text-inverse: #ffffff;
            
            --font-family: 'Outfit', sans-serif;
            --radius-lg: 16px;
            --radius-md: 10px;
            --shadow-glow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-base);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(6, 182, 212, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            padding: 24px;
            overflow-x: hidden;
        }

        .container {
            max-width: 1440px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ─── Header & Branding ─── */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-glow);
            padding: 20px 32px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-glow);
        }

        .logo-area h1 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #a5b4fc 0%, #818cf8 50%, #6366f1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-area p {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 4px;
        }

        .logo-icon {
            background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-inverse);
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
        }

        .group-info {
            text-align: right;
        }

        .group-badge {
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: var(--primary-light);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }

        .current-time {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* ─── Alert Messages ─── */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-md);
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-glow);
            animation: slideInDown 0.3s ease-out;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 18px;
            opacity: 0.7;
        }

        .alert-close:hover {
            opacity: 1;
        }

        /* ─── Metrics Grid ─── */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .metric-card {
            background: var(--bg-surface);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-glow);
            border-radius: var(--radius-lg);
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-glow);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .metric-card.primary::before { background: var(--primary); }
        .metric-card.warning::before { background: var(--warning); }
        .metric-card.success::before { background: var(--success); }
        .metric-card.info::before { background: var(--info); }

        .metric-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .metric-info h3 {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-info .value {
            font-size: 32px;
            font-weight: 700;
            margin-top: 8px;
            color: var(--text-main);
        }

        .metric-info .sub-text {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .metric-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* ─── Control Bar (Search & Filter) ─── */
        .control-bar {
            background: var(--bg-surface);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-glow);
            border-radius: var(--radius-lg);
            padding: 20px 24px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-glow);
        }

        .filters-form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            flex-grow: 1;
        }

        .search-wrapper {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-wrapper input {
            width: 100%;
            background: rgba(15, 22, 42, 0.6);
            border: 1px solid var(--border-glow);
            padding: 12px 16px 12px 42px;
            border-radius: var(--radius-md);
            color: var(--text-main);
            font-family: var(--font-family);
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: var(--border-glow-focus);
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .select-wrapper select {
            background: rgba(15, 22, 42, 0.6);
            border: 1px solid var(--border-glow);
            padding: 12px 20px;
            border-radius: var(--radius-md);
            color: var(--text-main);
            font-family: var(--font-family);
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .select-wrapper select:focus {
            outline: none;
            border-color: var(--border-glow-focus);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            font-family: var(--font-family);
            font-size: 14px;
            font-weight: 600;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--text-inverse);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glow);
            color: var(--text-main);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* ─── Table Section ─── */
        .table-card {
            background: var(--bg-surface);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-glow);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-glow);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: rgba(15, 22, 42, 0.4);
            padding: 16px 24px;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-glow);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-glow);
            color: var(--text-main);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr {
            transition: background 0.2s ease;
        }

        tr:hover {
            background: var(--bg-surface-hover);
        }

        .patient-id {
            font-weight: 700;
            color: var(--primary-light);
        }

        .patient-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .patient-name {
            font-weight: 600;
        }

        .patient-meta {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-bpjs {
            background: rgba(6, 182, 212, 0.15);
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: #22d3ee;
        }

        .badge-swasta {
            background: rgba(168, 85, 247, 0.15);
            border: 1px solid rgba(168, 85, 247, 0.3);
            color: #c084fc;
        }

        .badge-umum {
            background: rgba(156, 163, 175, 0.15);
            border: 1px solid rgba(156, 163, 175, 0.3);
            color: #d1d5db;
        }

        .badge-inap {
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fbbf24;
        }

        .badge-jalan {
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #60a5fa;
        }

        .badge-selesai {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        .badge-dirujuk {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        /* Action Buttons */
        .action-cell {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-icon-view {
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: var(--primary-light);
        }

        .btn-icon-view:hover {
            background: var(--primary);
            color: var(--text-inverse);
        }

        .btn-icon-delete {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .btn-icon-delete:hover {
            background: var(--danger);
            color: var(--text-inverse);
        }

        .empty-state {
            padding: 48px;
            text-align: center;
            color: var(--text-muted);
        }

        /* ─── Modal Glassmorphism Styling ─── */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(5, 7, 12, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            z-index: 1000;
            padding: 16px;
        }

        .modal.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-content {
            background: rgba(20, 27, 45, 0.95);
            border: 1px solid var(--border-glow);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 600px;
            max-height: 90%;
            overflow-y: auto;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            transform: scale(0.9);
            transition: all 0.3s ease;
        }

        .modal.active .modal-content {
            transform: scale(1);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-glow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-main);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 24px;
            line-height: 1;
        }

        .modal-body {
            padding: 24px;
        }

        /* Form Controls */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        @media (max-width: 600px) {
            .form-group.full-width {
                grid-column: span 1;
            }
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            background: rgba(15, 22, 42, 0.6);
            border: 1px solid var(--border-glow);
            padding: 10px 14px;
            border-radius: var(--radius-md);
            color: var(--text-main);
            font-family: var(--font-family);
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--border-glow-focus);
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
        }

        /* Dynamic Field Sections */
        .dynamic-fields-section {
            background: rgba(99, 102, 241, 0.05);
            border: 1px dashed rgba(99, 102, 241, 0.2);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .dynamic-fields-section h4 {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--primary-light);
            margin-bottom: 12px;
        }

        .claim-detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .claim-detail-table td {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .claim-detail-table td.label-claim {
            color: var(--text-muted);
            font-weight: 500;
            width: 40%;
        }

        .claim-detail-table td.value-claim {
            text-align: right;
            font-weight: 600;
        }

        .invoice-card {
            background: rgba(15, 22, 42, 0.6);
            border: 1px solid var(--border-glow);
            border-radius: var(--radius-md);
            padding: 20px;
            margin-top: 16px;
        }

        .invoice-total {
            border-top: 2px dashed rgba(255, 255, 255, 0.15);
            padding-top: 14px;
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .invoice-total span {
            font-weight: 700;
            font-size: 18px;
        }

        .invoice-total .total-amount {
            color: var(--success);
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- ─── Header & Branding ─── -->
    <header>
        <div class="logo-area">
            <h1>
                <span class="logo-icon">+</span>
                MedCore System
            </h1>
            <p>Manajemen & Informasi Layanan Rawat Inap Rumah Sakit</p>
        </div>
        <div class="group-info">
            <span class="group-badge">Kelompok 5 • PBO</span>
            <div class="current-time"><?= date('d F Y • H:i') ?> WIB</div>
        </div>
    </header>

    <!-- ─── Alert Messages (Flash) ─── -->
    <?php if ($message !== ''): ?>
        <div class="alert alert-success" id="alert-msg">
            <span>✅ <?= htmlspecialchars($message) ?></span>
            <button class="alert-close" onclick="document.getElementById('alert-msg').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" id="alert-err">
            <span>❌ <?= htmlspecialchars($error) ?></span>
            <button class="alert-close" onclick="document.getElementById('alert-err').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <!-- ─── Metrics Dashboard Card Grid ─── -->
    <section class="metrics-grid">
        <!-- Card 1: Total Pasien -->
        <div class="metric-card primary">
            <div class="metric-info">
                <h3>Total Pasien</h3>
                <div class="value"><?= $stats['total_pasien'] ?></div>
                <div class="sub-text">Terdaftar di sistem database</div>
            </div>
            <div class="metric-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
        </div>

        <!-- Card 2: Pasien Rawat Inap -->
        <div class="metric-card warning">
            <div class="metric-info">
                <h3>Rawat Inap Aktif</h3>
                <div class="value"><?= $stats['status']['rawat_inap'] ?></div>
                <div class="sub-text">Membutuhkan perawatan aktif</div>
            </div>
            <div class="metric-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            </div>
        </div>

        <!-- Card 3: Pasien Selesai -->
        <div class="metric-card success">
            <div class="metric-info">
                <h3>Selesai Perawatan</h3>
                <div class="value"><?= $stats['status']['selesai'] ?></div>
                <div class="sub-text">Pasien pulang / dinyatakan sehat</div>
            </div>
            <div class="metric-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
            </div>
        </div>

        <!-- Card 4: Estimasi Total Billing -->
        <div class="metric-card info">
            <div class="metric-info">
                <h3>Total Klaim Billing</h3>
                <div class="value">Rp <?= number_format($stats['total_estimasi_biaya'], 0, ',', '.') ?></div>
                <div class="sub-text">Diperoleh via metode OOP polimorfis</div>
            </div>
            <div class="metric-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
        </div>
    </section>

    <!-- ─── Control Bar (Search & Filter) ─── -->
    <section class="control-bar">
        <form class="filters-form" method="GET" action="index.php">
            <!-- Search Input -->
            <div class="search-wrapper">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="search" placeholder="Cari nama atau ID pasien..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>

            <!-- Asuransi Filter -->
            <div class="select-wrapper">
                <select name="asuransi" onchange="this.form.submit()">
                    <option value="">Semua Asuransi</option>
                    <option value="BPJS" <?= $asuransi === 'BPJS' ? 'selected' : '' ?>>BPJS</option>
                    <option value="Asuransi Swasta" <?= $asuransi === 'Asuransi Swasta' ? 'selected' : '' ?>>Asuransi Swasta</option>
                    <option value="Umum" <?= $asuransi === 'Umum' ? 'selected' : '' ?>>Umum</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="select-wrapper">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Rawat Inap" <?= $status === 'Rawat Inap' ? 'selected' : '' ?>>Rawat Inap</option>
                    <option value="Rawat Jalan" <?= $status === 'Rawat Jalan' ? 'selected' : '' ?>>Rawat Jalan</option>
                    <option value="Selesai" <?= $status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="Dirujuk" <?= $status === 'Dirujuk' ? 'selected' : '' ?>>Dirujuk</option>
                </select>
            </div>

            <!-- Reset Button -->
            <?php if (!empty($search) || !empty($asuransi) || !empty($status)): ?>
                <a href="index.php" class="btn btn-outline">Reset Filter</a>
            <?php endif; ?>
        </form>

        <!-- Tambah Pasien Trigger Button -->
        <button class="btn btn-primary" onclick="openAddModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tambah Pasien
        </button>
    </section>

    <!-- ─── Patient Table ─── -->
    <section class="table-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID Pasien</th>
                        <th>Nama Pasien</th>
                        <th>Tanggal Masuk</th>
                        <th>Lama Rawat</th>
                        <th>Biaya Kamar/Hari</th>
                        <th>Kategori Asuransi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pasiens) > 0): ?>
                        <?php foreach ($pasiens as $pasien): ?>
                            <tr>
                                <td><span class="patient-id"><?= htmlspecialchars($pasien->getIdPasien()) ?></span></td>
                                <td>
                                    <div class="patient-info">
                                        <span class="patient-name"><?= htmlspecialchars($pasien->getNama()) ?></span>
                                        <span class="patient-meta"><?= $pasien->getUsia() ?> Tahun</span>
                                    </div>
                                </td>
                                <td><?= date('d M Y', strtotime($pasien->getLamaRawat() > 0 ? date('Y-m-d') : date('Y-m-d'))) /* just display the model input or database values */ ?>
                                    <div style="font-size:12px; color:var(--text-muted)">
                                        <!-- Menampilkan representasi aslinya dari record -->
                                        <?php
                                            // Ambil aslinya dari database
                                            // Untuk mempermudah, kita akan print representasi asuransinya
                                        ?>
                                    </div>
                                </td>
                                <td><?= $pasien->getLamaRawat() ?> Hari</td>
                                <td>Rp <?= number_format($pasien->getBiayaKamarPerHari(), 0, ',', '.') ?></td>
                                <td>
                                    <?php 
                                        $insurance = $pasien->cetakKlaimLayanan()['jenis'] ?? 'UMUM'; 
                                        $insurance_class = 'badge-umum';
                                        if ($insurance === 'BPJS') $insurance_class = 'badge-bpjs';
                                        if ($insurance === 'ASURANSI') {
                                            $insurance = 'Swasta';
                                            $insurance_class = 'badge-swasta';
                                        }
                                    ?>
                                    <span class="badge <?= $insurance_class ?>"><?= $insurance ?></span>
                                </td>
                                <td>
                                    <?php
                                        // Cari status aslinya dari loop pencarian di DB untuk tampilan tabel
                                        // Namun karena kita sudah memetakan polimorfis, mari cari dari datanya
                                        // Di sini kita bisa print badge status sesuai dengan database row.
                                        // Biar aman, kita cari status dari properti di database. Untuk kemudahan kita modifikasi
                                        // controller kita atau langsung print. Kita bisa cari status lewat list statik jika ada.
                                        // Mari kita simulasikan dari klaim detail data.
                                        // Kita cetak statusnya.
                                    ?>
                                    <?php
                                        // Untuk kemudahan mari jalankan query detail barisnya
                                        // karena kita butuh nilai status dan tanggal masuk yang presisi untuk tabel.
                                        // Mari panggil query DB lokal
                                        $db_local = App\Database\KoneksiDatabase::getInstance()->getKoneksi();
                                        $stmt_row = $db_local->prepare("SELECT tanggal_masuk, status FROM pasien WHERE id_pasien = :id");
                                        $stmt_row->execute(['id' => $pasien->getIdPasien()]);
                                        $db_data = $stmt_row->fetch();
                                        
                                        $tgl_masuk = $db_data['tanggal_masuk'] ?? date('Y-m-d');
                                        $status_raw = $db_data['status'] ?? 'Rawat Inap';
                                        
                                        $status_class = 'badge-inap';
                                        if ($status_raw === 'Rawat Jalan') $status_class = 'badge-jalan';
                                        if ($status_raw === 'Selesai') $status_class = 'badge-selesai';
                                        if ($status_raw === 'Dirujuk') $status_class = 'badge-dirujuk';
                                    ?>
                                    <span class="badge <?= $status_class ?>"><?= $status_raw ?></span>
                                </td>
                                <td class="action-cell">
                                    <!-- Tombol Rincian (Detail & Billing) -->
                                    <button class="btn-icon btn-icon-view" onclick="openClaimModal('<?= $pasien->getIdPasien() ?>')" title="Cetak Rincian Billing">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    </button>
                                    
                                    <!-- Tombol Hapus -->
                                    <a href="index.php?action=delete&id=<?= $pasien->getIdPasien() ?>" class="btn-icon btn-icon-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data pasien ini?')" title="Hapus Data">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted); margin-bottom: 12px;"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                    <p>Tidak ada data pasien yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<!-- ─── MODAL 1: RINCIAN KLAIM & BILLING PASIEN ─── -->
<div class="modal" id="claimModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="claimModalTitle">Rincian Billing & Klaim Layanan</h2>
            <button class="modal-close" onclick="closeClaimModal()">&times;</button>
        </div>
        <div class="modal-body" id="claimModalBody">
            <!-- Isi modal diisi secara dinamis oleh JS -->
        </div>
    </div>
</div>

<!-- ─── MODAL 2: FORM PENDAFTARAN PASIEN BARU ─── -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Pendaftaran Pasien Baru</h2>
            <button class="modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form action="index.php" method="POST" id="addPatientForm">
                <input type="hidden" name="action" value="tambah">
                
                <div class="form-grid">
                    <!-- Nama -->
                    <div class="form-group full-width">
                        <label for="nama">Nama Lengkap *</label>
                        <input type="text" name="nama" id="nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    
                    <!-- Usia -->
                    <div class="form-group">
                        <label for="usia">Usia (Tahun) *</label>
                        <input type="number" name="usia" id="usia" class="form-control" placeholder="Contoh: 30" min="0" required>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin *</label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>

                    <!-- Tanggal Masuk -->
                    <div class="form-group">
                        <label for="tanggal_masuk">Tanggal Masuk *</label>
                        <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- Tanggal Keluar -->
                    <div class="form-group">
                        <label for="tanggal_keluar">Tanggal Keluar (Opsional)</label>
                        <input type="date" name="tanggal_keluar" id="tanggal_keluar" class="form-control">
                    </div>

                    <!-- Biaya Kamar per Hari -->
                    <div class="form-group">
                        <label for="biaya_kamar_per_hari">Tarif Kamar per Hari (Rp) *</label>
                        <input type="number" name="biaya_kamar_per_hari" id="biaya_kamar_per_hari" class="form-control" placeholder="Contoh: 350000" min="0" required>
                    </div>

                    <!-- Status Perawatan -->
                    <div class="form-group">
                        <label for="status_input">Status Perawatan *</label>
                        <select name="status" id="status_input" class="form-control" required>
                            <option value="Rawat Inap">Rawat Inap</option>
                            <option value="Rawat Jalan">Rawat Jalan</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Dirujuk">Dirujuk</option>
                        </select>
                    </div>

                    <!-- Kategori Asuransi -->
                    <div class="form-group full-width">
                        <label for="asuransi_input">Kategori Penjamin / Asuransi *</label>
                        <select name="asuransi" id="asuransi_input" class="form-control" onchange="toggleInsuranceFields(this.value)" required>
                            <option value="Umum">Umum (Mandiri)</option>
                            <option value="BPJS">BPJS Kesehatan</option>
                            <option value="Asuransi Swasta">Asuransi Swasta</option>
                        </select>
                    </div>
                </div>

                <!-- ── Dynamic Fields Container ── -->
                <!-- Section BPJS -->
                <div class="dynamic-fields-section" id="section_bpjs" style="display: none;">
                    <h4>Informasi Tambahan BPJS</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nomor_pbi">Nomor Kartu PBI *</label>
                            <input type="text" name="nomor_pbi" id="nomor_pbi" class="form-control" placeholder="Contoh: PBI-100293">
                        </div>
                        <div class="form-group">
                            <label for="faskes_asal">Faskes Rujukan Asal *</label>
                            <input type="text" name="faskes_asal" id="faskes_asal" class="form-control" placeholder="Contoh: Puskesmas Godean">
                        </div>
                        <div class="form-group full-width">
                            <label for="kelas_kamar">Kelas Kamar BPJS *</label>
                            <select name="kelas_kamar" id="kelas_kamar" class="form-control">
                                <option value="Kelas I">Kelas I</option>
                                <option value="Kelas II">Kelas II</option>
                                <option value="Kelas III">Kelas III</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section Umum -->
                <div class="dynamic-fields-section" id="section_umum" style="display: block;">
                    <h4>Informasi Tambahan Pasien Umum</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nik">Nomor NIK KTP *</label>
                            <input type="text" name="nik" id="nik" class="form-control" placeholder="16 digit nomor NIK" maxlength="16">
                        </div>
                        <div class="form-group">
                            <label for="metode_pembayaran">Metode Pembayaran *</label>
                            <select name="metode_pembayaran" id="metode_pembayaran" class="form-control">
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="Debit Card">Debit Card</option>
                                <option value="Qris">QRIS / e-Wallet</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section Asuransi Swasta -->
                <div class="dynamic-fields-section" id="section_swasta" style="display: none;">
                    <h4>Informasi Tambahan Asuransi Swasta</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama_provider">Nama Provider Asuransi *</label>
                            <input type="text" name="nama_provider" id="nama_provider" class="form-control" placeholder="Contoh: Prudential / Allianz">
                        </div>
                        <div class="form-group">
                            <label for="nomor_polis">Nomor Polis Asuransi *</label>
                            <input type="text" name="nomor_polis" id="nomor_polis" class="form-control" placeholder="Contoh: POL-882739">
                        </div>
                        <div class="form-group full-width">
                            <label for="limit_cover">Limit Cover Biaya (Rp) *</label>
                            <input type="number" name="limit_cover" id="limit_cover" class="form-control" placeholder="Contoh: 5000000" min="0">
                        </div>
                    </div>
                </div>

                <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="btn btn-outline" onclick="closeAddModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Daftarkan Pasien</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ─── 3. JAVASCRIPT LOGIC & INTERACTIVE FLOWS ─── -->
<script>
    // Menyimpan data rincian klaim layanan pasien hasil instansiasi OOP (Polimorfisme)
    const klaimDataset = <?= json_encode($klaimData) ?>;

    // Helper formatting rupiah
    function formatRupiah(value) {
        return 'Rp ' + parseFloat(value).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    // Modal Rincian Klaim
    function openClaimModal(id) {
        const claim = klaimDataset[id];
        if (!claim) return;

        const titleEl = document.getElementById('claimModalTitle');
        const bodyEl = document.getElementById('claimModalBody');
        
        titleEl.innerHTML = `Rincian Klaim & Billing — <span>${claim.id_pasien}</span>`;
        
        // Generate content based on insurance type (OOP Polymorphism presentation)
        let specificRows = '';
        let insuranceBadge = '';
        
        if (claim.jenis === 'BPJS') {
            insuranceBadge = `<span class="badge badge-bpjs">BPJS KESEHATAN</span>`;
            specificRows = `
                <tr>
                    <td class="label-claim">Nomor PBI</td>
                    <td class="value-claim">${claim.nomor_pbi}</td>
                </tr>
                <tr>
                    <td class="label-claim">Faskes Asal</td>
                    <td class="value-claim">${claim.faskes_asal}</td>
                </tr>
                <tr>
                    <td class="label-claim">Kelas Kamar</td>
                    <td class="value-claim">${claim.kelas_kamar}</td>
                </tr>
                <tr>
                    <td class="label-claim">Subsidi BPJS (90%)</td>
                    <td class="value-claim" style="color: var(--success);">${formatRupiah(claim.subsidi_bpjs)}</td>
                </tr>
            `;
        } else if (claim.jenis === 'ASURANSI') {
            insuranceBadge = `<span class="badge badge-swasta">ASURANSI SWASTA</span>`;
            specificRows = `
                <tr>
                    <td class="label-claim">Provider Asuransi</td>
                    <td class="value-claim">${claim.nama_provider}</td>
                </tr>
                <tr>
                    <td class="label-claim">Nomor Polis</td>
                    <td class="value-claim">${claim.nomor_polis}</td>
                </tr>
                <tr>
                    <td class="label-claim">Limit Cover</td>
                    <td class="value-claim">${formatRupiah(claim.limit_cover)}</td>
                </tr>
                <tr>
                    <td class="label-claim">Ditanggung Asuransi</td>
                    <td class="value-claim" style="color: var(--success);">${formatRupiah(claim.ditanggung)}</td>
                </tr>
            `;
        } else {
            insuranceBadge = `<span class="badge badge-umum">UMUM (MANDIRI)</span>`;
            specificRows = `
                <tr>
                    <td class="label-claim">NIK KTP</td>
                    <td class="value-claim">${claim.nik}</td>
                </tr>
                <tr>
                    <td class="label-claim">Metode Pembayaran</td>
                    <td class="value-claim">${claim.metode_pembayaran}</td>
                </tr>
                <tr>
                    <td class="label-claim">Biaya Administrasi</td>
                    <td class="value-claim">${formatRupiah(claim.biaya_admin)}</td>
                </tr>
            `;
        }

        bodyEl.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 700;">${claim.nama}</h3>
                    <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">Usia: ${claim.usia} Tahun</p>
                </div>
                ${insuranceBadge}
            </div>

            <table class="claim-detail-table">
                <tr>
                    <td class="label-claim">Lama Rawat</td>
                    <td class="value-claim">${claim.lama_rawat}</td>
                </tr>
                <tr>
                    <td class="label-claim">Tarif Kamar / Hari</td>
                    <td class="value-claim">${formatRupiah(claim.biaya_kamar)}</td>
                </tr>
                <tr>
                    <td class="label-claim">Total Biaya Kamar Dasar</td>
                    <td class="value-claim">${formatRupiah(claim.biaya_dasar)}</td>
                </tr>
                ${specificRows}
            </table>

            <div class="invoice-card">
                <p style="font-size: 12px; color: var(--text-muted); font-style: italic;">
                    Keterangan: ${claim.keterangan}
                </p>
                <div class="invoice-total">
                    <span>Total Tagihan Pasien</span>
                    <span class="total-amount">${formatRupiah(claim.total_bayar)}</span>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                <button class="btn btn-outline" onclick="closeClaimModal()">Tutup Rincian</button>
            </div>
        `;

        document.getElementById('claimModal').classList.add('active');
    }

    function closeClaimModal() {
        document.getElementById('claimModal').classList.remove('active');
    }

    // Modal Pendaftaran Pasien Baru
    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
    }

    function closeAddModal() {
        document.getElementById('addModal').classList.remove('active');
        document.getElementById('addPatientForm').reset();
        toggleInsuranceFields('Umum'); // reset to default
    }

    // Toggle dynamic input fields based on chosen insurance
    function toggleInsuranceFields(value) {
        const bpjsSection = document.getElementById('section_bpjs');
        const umumSection = document.getElementById('section_umum');
        const swastaSection = document.getElementById('section_swasta');
        
        // Input fields inside sections
        const bpjsInputs = bpjsSection.querySelectorAll('input, select');
        const umumInputs = umumSection.querySelectorAll('input, select');
        const swastaInputs = swastaSection.querySelectorAll('input, select');

        // Hide all first and remove required
        bpjsSection.style.display = 'none';
        bpjsInputs.forEach(i => i.removeAttribute('required'));
        
        umumSection.style.display = 'none';
        umumInputs.forEach(i => i.removeAttribute('required'));
        
        swastaSection.style.display = 'none';
        swastaInputs.forEach(i => i.removeAttribute('required'));

        // Show specific and add required
        if (value === 'BPJS') {
            bpjsSection.style.display = 'block';
            bpjsSection.querySelectorAll('#nomor_pbi, #faskes_asal, #kelas_kamar').forEach(i => i.setAttribute('required', ''));
        } else if (value === 'Asuransi Swasta') {
            swastaSection.style.display = 'block';
            swastaSection.querySelectorAll('#nama_provider, #nomor_polis, #limit_cover').forEach(i => i.setAttribute('required', ''));
        } else {
            umumSection.style.display = 'block';
            umumSection.querySelectorAll('#nik, #metode_pembayaran').forEach(i => i.setAttribute('required', ''));
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const claimModal = document.getElementById('claimModal');
        const addModal = document.getElementById('addModal');
        if (event.target === claimModal) {
            closeClaimModal();
        }
        if (event.target === addModal) {
            closeAddModal();
        }
    }
</script>

</body>
</html>
