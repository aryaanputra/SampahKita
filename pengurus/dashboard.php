<?php
/**
 * pengurus/dashboard.php - Ringkasan operasional Bank Sampah
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['pengurus']);

$u = current_user();

// Kartu statistik
$stats = [
    'nasabah' => 0, 'setoran_bulan' => 0, 'berat_bulan' => 0,
    'menunggu' => 0, 'jemput_open' => 0, 'saldo_total' => 0,
];

$res = mysqli_query($con, "SELECT COUNT(*) c FROM users WHERE role = 'nasabah'");
if ($res) { $stats['nasabah'] = (int) mysqli_fetch_assoc($res)['c']; }

$res = mysqli_query($con, "SELECT COALESCE(SUM(total_nilai),0) v FROM setoran WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) AND YEAR(tanggal) = YEAR(CURRENT_DATE())");
if ($res) { $stats['setoran_bulan'] = (float) mysqli_fetch_assoc($res)['v']; }

$res = mysqli_query($con, "SELECT COALESCE(SUM(d.berat_kg),0) v FROM detail_setoran d JOIN setoran s ON s.id = d.setoran_id WHERE MONTH(s.tanggal) = MONTH(CURRENT_DATE()) AND YEAR(s.tanggal) = YEAR(CURRENT_DATE())");
if ($res) { $stats['berat_bulan'] = (float) mysqli_fetch_assoc($res)['v']; }

$res = mysqli_query($con, "SELECT COUNT(*) c FROM pencairan WHERE status = 'menunggu'");
if ($res) { $stats['menunggu'] = (int) mysqli_fetch_assoc($res)['c']; }

$res = mysqli_query($con, "SELECT COUNT(*) c FROM permintaan_jemput WHERE status IN ('Diminta','Dijadwalkan')");
if ($res) { $stats['jemput_open'] = (int) mysqli_fetch_assoc($res)['c']; }

$res = mysqli_query($con, "SELECT COALESCE(SUM(saldo_akhir),0) v FROM saldo_mutasi WHERE id IN (SELECT MAX(id) FROM saldo_mutasi GROUP BY user_id)");
if ($res) { $stats['saldo_total'] = (float) mysqli_fetch_assoc($res)['v']; }

// Setoran 6 bulan terakhir
$chart_labels = []; $chart_nilai = []; $chart_berat = [];
$res = mysqli_query($con, "SELECT DATE_FORMAT(tanggal,'%Y-%m') ym, DATE_FORMAT(tanggal,'%b %y') lbl, SUM(total_nilai) nilai FROM setoran WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym, lbl ORDER BY ym ASC");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $chart_labels[] = $r['lbl']; $chart_nilai[] = (float) $r['nilai']; } }

// Komposisi jenis sampah (berat) untuk donut
$jenis_labels = []; $jenis_data = [];
$res = mysqli_query($con, "SELECT j.nama, SUM(d.berat_kg) berat FROM detail_setoran d JOIN jenis_sampah j ON j.id = d.jenis_id GROUP BY j.nama ORDER BY berat DESC LIMIT 6");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $jenis_labels[] = $r['nama']; $jenis_data[] = (float) $r['berat']; } }

// Setoran terakhir
$setoran_list = [];
$res = mysqli_query($con, "SELECT s.id, s.tanggal, s.total_nilai, u.name AS nasabah FROM setoran s JOIN users u ON u.id_user = s.user_id ORDER BY s.tanggal DESC LIMIT 6");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $setoran_list[] = $r; } }

// Pencairan menunggu
$cair_list = [];
$res = mysqli_query($con, "SELECT p.id, p.nominal, p.tanggal, u.name AS nasabah FROM pencairan p JOIN users u ON u.id_user = p.user_id WHERE p.status = 'menunggu' ORDER BY p.tanggal ASC LIMIT 5");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $cair_list[] = $r; } }

$page_title    = 'Dasbor';
$page_subtitle = 'Ringkasan aktivitas Bank Sampah hari ini.';
$active_menu   = 'dashboard';
$page_actions  = '<a href="timbang.php" class="btn btn-dark-custom"><i class="bi bi-basket me-1"></i> Catat Setoran</a>';
require __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
  <div class="col-md-6 col-xl-3">
    <div class="card card-stat">
      <div class="card-header"><span class="stat-label">Nasabah Terdaftar</span></div>
      <div class="stat-value"><?= e($stats['nasabah']) ?></div>
      <div class="trend-badge trend-up"><i class="bi bi-people"></i><span>Akun nasabah aktif & terdaftar</span></div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card card-stat">
      <div class="card-header"><span class="stat-label">Setoran Bulan Ini</span></div>
      <div class="stat-value"><?= e(rupiah($stats['setoran_bulan'])) ?></div>
      <div class="trend-badge trend-up"><i class="bi bi-arrow-up-right"></i><span><?= e(kg($stats['berat_bulan'])) ?> ditimbang</span></div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card card-stat">
      <div class="card-header"><span class="stat-label">Saldo Nasabah</span></div>
      <div class="stat-value"><?= e(rupiah($stats['saldo_total'])) ?></div>
      <div class="trend-badge trend-up"><i class="bi bi-wallet2"></i><span>Total kewajiban tabungan</span></div>
    </div>
  </div>
  <div class="col-md-6 col-xl-3">
    <div class="card card-stat">
      <div class="card-header"><span class="stat-label">Perlu Tindakan</span></div>
      <div class="stat-value"><?= e($stats['menunggu'] + $stats['jemput_open']) ?></div>
      <div class="trend-badge trend-down"><i class="bi bi-exclamation-triangle"></i>
        <span><?= e($stats['menunggu']) ?> pencairan · <?= e($stats['jemput_open']) ?> jemput</span>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Nilai Setoran 6 Bulan Terakhir</h2></div>
      <div id="setoran-chart"></div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header"><h2 class="card-title">Komposisi Jenis (kg)</h2></div>
      <div id="komposisi-chart"></div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="table-card-custom">
      <div class="table-header-control"><h2 class="card-title mb-0">Setoran Terakhir</h2>
        <div class="table-filter-group"><a href="timbang.php" class="btn-table-action"><i class="bi bi-plus-lg"></i> Catat</a></div>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>#</th><th>Nasabah</th><th>Tanggal</th><th class="text-end">Nilai</th></tr></thead>
          <tbody>
            <?php foreach ($setoran_list as $s): ?>
              <tr>
                <td class="table-order-id">#ST-<?= e(str_pad($s['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                <td><?= e($s['nasabah']) ?></td>
                <td><?= e(tanggal_waktu_id($s['tanggal'])) ?></td>
                <td class="table-amount text-end"><?= e(rupiah($s['total_nilai'])) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($setoran_list)): ?>
              <tr><td colspan="4" class="text-center text-muted-green py-4">Belum ada setoran.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header"><h2 class="card-title">Pencairan Menunggu</h2>
        <div class="dropdown">
          <a href="pencairan.php" class="card-more-btn" title="Kelola"><i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
      <ul class="clean-list">
        <?php if (empty($cair_list)): ?>
          <li class="clean-list-item"><div class="text-muted-green">Tidak ada pencairan menunggu.</div></li>
        <?php else: foreach ($cair_list as $c): ?>
          <li class="clean-list-item">
            <div class="stat-mini-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between"><strong><?= e($c['nasabah']) ?></strong><span class="table-amount"><?= e(rupiah($c['nominal'])) ?></span></div>
              <div class="stat-mini-label"><?= e(tanggal_id($c['tanggal'])) ?></div>
            </div>
          </li>
        <?php endforeach; endif; ?>
      </ul>
    </div>
  </div>
</div>

<script>
  window.__charts = [
    { id: 'setoran-chart', type: 'bar', labels: <?= json_encode($chart_labels) ?>,
      series: [{ name: 'Nilai Setoran', data: <?= json_encode($chart_nilai) ?> }], money: true },
    { id: 'komposisi-chart', type: 'donut', labels: <?= json_encode($jenis_labels) ?>,
      series: <?= json_encode($jenis_data) ?> }
  ];
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
