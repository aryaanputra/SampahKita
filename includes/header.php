<?php
/**
 * =====================================================================
 *  Bank Sampah Kali Laya Berseri
 *  includes/header.php - Layout (sidebar + navbar) mengikuti Spark Admin
 *
 *  Variabel yang disiapkan halaman sebelum include:
 *    $base        (string) prefix relatif ke root, mis. '../'
 *    $page_title  (string) judul halaman
 *    $page_subtitle (string, opsional)
 *    $page_actions  (string HTML, opsional) aksi di kanan page-header
 *    $active_menu   (string, opsional) id menu aktif
 * =====================================================================
 */

require_once __DIR__ . '/auth.php';

$base          = $base ?? '';
$page_title    = $page_title ?? 'Dasbor';
$page_subtitle = $page_subtitle ?? '';
$page_actions  = $page_actions ?? '';
$active_menu   = $active_menu ?? '';

$u = current_user();
$role = $u['role'] ?: 'nasabah';

/* ---------------------------------------------------------------------
 * Definisi menu per peran
 * ------------------------------------------------------------------- */
$menus = [
    'nasabah' => [
        ['id' => 'dashboard', 'label' => 'Dasbor Saldo',   'icon' => 'bi-grid-fill',        'href' => 'dashboard.php'],
        ['id' => 'setor',     'label' => 'Setor Sampah',   'icon' => 'bi-arrow-up-circle',  'href' => 'setor.php'],
        ['id' => 'jemput',    'label' => 'Minta Jemput',   'icon' => 'bi-truck',            'href' => 'jemput.php'],
        ['id' => 'saldo',     'label' => 'Saldo & Riwayat','icon' => 'bi-clock-history',    'href' => 'saldo.php'],
        ['id' => 'pencairan', 'label' => 'Ajukan Pencairan','icon' => 'bi-cash-coin',       'href' => 'pencairan.php'],
        ['id' => 'edukasi',   'label' => 'Edukasi',        'icon' => 'bi-mortarboard',      'href' => 'edukasi.php'],
    ],
    'pengurus' => [
        ['id' => 'dashboard',  'label' => 'Dasbor',          'icon' => 'bi-grid-fill',        'href' => 'dashboard.php'],
        ['id' => 'nasabah',    'label' => 'Kelola Nasabah',  'icon' => 'bi-people',           'href' => 'nasabah.php'],
        ['id' => 'jenis',      'label' => 'Jenis Sampah',    'icon' => 'bi-tags',             'href' => 'jenis_sampah.php'],
        ['id' => 'timbang',    'label' => 'Timbang & Catat', 'icon' => 'bi-basket',           'href' => 'timbang.php'],
        ['id' => 'penjemputan','label' => 'Penjemputan',     'icon' => 'bi-truck',            'href' => 'penjemputan.php'],
        ['id' => 'pencairan',  'label' => 'Pencairan',       'icon' => 'bi-cash-coin',        'href' => 'pencairan.php'],
        ['id' => 'offtaker',   'label' => 'Setor ke Offtaker','icon' => 'bi-shop',            'href' => 'offtaker.php'],
    ],
    'ketua' => [
        ['id' => 'dashboard',   'label' => 'Dasbor',              'icon' => 'bi-grid-fill',       'href' => 'dashboard.php'],
        ['id' => 'persetujuan', 'label' => 'Persetujuan Pencairan','icon' => 'bi-check2-circle',   'href' => 'persetujuan.php'],
        ['id' => 'laporan',     'label' => 'Laporan',             'icon' => 'bi-file-earmark-bar-graph', 'href' => 'laporan.php'],
    ],
];

$role_menu = $menus[$role] ?? $menus['nasabah'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?> &middot; Bank Sampah Kali Laya Berseri</title>

  <meta name="description" content="Aplikasi Bank Sampah Kali Laya Berseri">
  <meta name="author" content="Bank Sampah Kali Laya Berseri">

  <link rel="icon" type="image/png" href="<?= e($base) ?>assets/images/favicon.ico">

  <link rel="stylesheet" href="<?= e($base) ?>assets/libs/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= e($base) ?>assets/libs/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= e($base) ?>assets/libs/apexcharts/apexcharts.css">
  <link rel="stylesheet" href="<?= e($base) ?>assets/libs/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="<?= e($base) ?>assets/css/main.css">
  <link rel="stylesheet" href="<?= e($base) ?>assets/css/app.css">
</head>

<body>

  <!-- ============ START: Sidebar ============ -->
  <div class="sidebar-wrapper" id="sidebar">
    <a href="<?= e($base) ?>index.php" class="sidebar-brand">
      <i class="bi bi-recycle"></i>
      <span>Bank Sampah</span>
    </a>

    <div class="flex-grow-1 overflow-y-auto">
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Menu <?= e(label_role($role)) ?></div>
        <ul class="sidebar-menu-list">
          <?php foreach ($role_menu as $m): ?>
            <li class="sidebar-menu-item">
              <a href="<?= e($m['href']) ?>" class="sidebar-menu-link <?= $active_menu === $m['id'] ? 'active' : '' ?>"
                title="<?= e($m['label']) ?>">
                <i class="bi <?= e($m['icon']) ?>"></i>
                <span><?= e($m['label']) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Akun</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="<?= e($base) ?>logout-user.php" class="sidebar-menu-link" title="Keluar">
              <i class="bi bi-box-arrow-right"></i>
              <span>Keluar</span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div class="sidebar-profile">
      <div class="sidebar-profile-img app-avatar-initial"><?= e(mb_substr($u['name'] ?: 'U', 0, 1)) ?></div>
      <div class="sidebar-profile-info">
        <div class="sidebar-profile-name"><?= e($u['name']) ?></div>
        <div class="sidebar-profile-email"><?= e(label_role($role)) ?></div>
      </div>
    </div>
  </div>
  <!-- ============ END: Sidebar ============ -->

  <!-- ============ START: Main ============ -->
  <div class="main-wrapper">

    <!-- START: Navbar -->
    <header class="navbar-custom">
      <div class="navbar-left">
        <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3"
          id="desktop-sidebar-toggle" aria-label="Minimize Sidebar" type="button">
          <i class="bi bi-chevron-bar-left"></i>
        </button>
        <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Toggle Navigation" type="button">
          <i class="bi bi-list"></i>
        </button>
      </div>

      <div class="navbar-search-wrapper">
        <input type="text" class="navbar-search-input" placeholder="Cari data pada halaman ini..." id="main-search">
        <button class="navbar-search-btn" aria-label="Search" type="button">
          <i class="bi bi-search"></i>
        </button>
      </div>

      <div class="navbar-actions">
        <button class="navbar-action-btn me-1" aria-label="Toggle Fullscreen" id="btn-fullscreen" type="button">
          <i class="bi bi-arrows-fullscreen"></i>
        </button>

        <div class="dropdown ms-2">
          <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
            aria-expanded="false" id="profile-dropdown">
            <span class="navbar-profile-img app-avatar-initial"><?= e(mb_substr($u['name'] ?: 'U', 0, 1)) ?></span>
            <span class="navbar-profile-name d-none d-md-inline"><?= e($u['name']) ?></span>
            <i class="bi bi-chevron-down navbar-profile-caret"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
            <li class="dropdown-header">Masuk sebagai <?= e(label_role($role)) ?></li>
            <li><a class="dropdown-item" href="<?= e(dashboard_url($role, $base)) ?>"><i class="bi bi-grid-fill"></i> Dasbor</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="<?= e($base) ?>logout-user.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
          </ul>
        </div>
      </div>
    </header>
    <!-- END: Navbar -->

    <!-- START: Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title"><?= e($page_title) ?></h1>
        <?php if ($page_subtitle !== ''): ?>
          <p class="page-subtitle"><?= e($page_subtitle) ?></p>
        <?php endif; ?>
      </div>
      <?php if ($page_actions !== ''): ?>
        <div class="d-flex flex-wrap gap-2"><?= $page_actions ?></div>
      <?php endif; ?>
    </div>
    <!-- END: Page Header -->

    <?= render_flashes() ?>
