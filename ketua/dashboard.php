<?php
/**
 * ketua/dashboard.php - Dasbor pimpinan
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['ketua']);

$u = current_user();

$stat = ['nasabah' => 0, 'setoran_all' => 0, 'berat_all' => 0, 'saldo_total' => 0, 'menunggu' => 0, 'penjualan' => 0];

$res = mysqli_query($con, "SELECT COUNT(*) c FROM users WHERE role = 'nasabah'");
if ($res) { $stat['nasabah'] = (int) mysqli_fetch_assoc($res)['c']; }
$res = mysqli_query($con, "SELECT COALESCE(SUM(total_nilai),0) v FROM setoran");
if ($res) { $stat['setoran_all'] = (float) mysqli_fetch_assoc($res)['v']; }
$res = mysqli_query($con, "SELECT COALESCE(SUM(berat_kg),0) v FROM detail_setoran");
if ($res) { $stat['berat_all'] = (float) mysqli_fetch_assoc($res)['v']; }
$res = mysqli_query($con, "SELECT COALESCE(SUM(saldo_akhir),0) v FROM saldo_mutasi WHERE id IN (SELECT MAX(id) FROM saldo_mutasi GROUP BY user_id)");
if ($res) { $stat['saldo_total'] = (float) mysqli_fetch_assoc($res)['v']; }
$res = mysqli_query($con, "SELECT COUNT(*) c FROM pencairan WHERE status = 'menunggu'");
if ($res) { $stat['menunggu'] = (int) mysqli_fetch_assoc($res)['c']; }
$res = mysqli_query($con, "SELECT COALESCE(SUM(total),0) v FROM penjualan_offtaker");
if ($res) { $stat['penjualan'] = (float) mysqli_fetch_assoc($res)['v']; }

// Setoran per bulan (12 bulan)
$chart_labels = []; $chart_nilai = [];
$res = mysqli_query($con, "SELECT DATE_FORMAT(tanggal,'%Y-%m') ym, DATE_FORMAT(tanggal,'%b %y') lbl, SUM(total_nilai) nilai FROM setoran WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY ym, lbl ORDER BY ym ASC");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $chart_labels[] = $r['lbl']; $chart_nilai[] = (float) $r['nilai']; } }

// Komposisi kategori (donut)
$kat_labels = []; $kat_data = [];
$res = mysqli_query($con, "SELECT j.kategori, SUM(d.berat_kg) berat FROM detail_setoran d JOIN jenis_sampah j ON j.id = d.jenis_id GROUP BY j.kategori ORDER BY berat DESC");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $kat_labels[] = $r['kategori']; $kat_data[] = (float) $r['berat']; } }

// Pencairan menunggu
$cair = [];
$res = mysqli_query($con, "SELECT p.*, u.name AS nasabah FROM pencairan p JOIN users u ON u.id_user = p.user_id WHERE p.status = 'menunggu' ORDER BY p.tanggal ASC LIMIT 5");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $cair[] = $r; } }

// Top nasabah
$top = [];
$res = mysqli_query($con, "SELECT u.name, COALESCE(SUM(s.total_nilai),0) total FROM users u LEFT JOIN setoran s ON s.user_id = u.id_user WHERE u.role = 'nasabah' GROUP BY u.id_user ORDER BY total DESC LIMIT 5");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $top[] = $r; } }
$max_top = 0.0;
foreach ($top as $t) { if ((float) $t['total'] > $max_top) { $max_top = (float) $t['total']; } }

$page_title    = 'Dasbor';
$page_subtitle = 'Ringkasan kinerja Bank Sampah Kali Laya Berseri.';
$active_menu   = 'dashboard';
$page_actions  = '<a href="persetujuan.php" class="btn btn-dark-custom"><i class="bi bi-check2-circle me-1"></i> Persetujuan (' . e($stat['menunggu']) . ')</a>';
require __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
  <div class="col-md-6 col-xl-3"><div class="card card-stat">
    <div class="card-header"><span class="stat-label">Total Setoran</span></div>
    <div class="stat-value"><?= e(rupiah($stat['setoran_all'])) ?></div>
    <div class="trend-badge trend-up"><i class="bi bi-arrow-up-right"></i><span><?= e(kg($stat['berat_all'])) ?> terkumpul</span></div>
  </div></div>
  <div class="col-md-6 col-xl-3"><div class="card card-stat">
    <div class="card-header"><span class="stat-label">Saldo Nasabah</span></div>
    <div class="stat-value"><?= e(rupiah($stat['saldo_total'])) ?></div>
    <div class="trend-badge trend-up"><i class="bi bi-people"></i><span><?= e($stat['nasabah']) ?> nasabah</span></div>
  </div></div>
  <div class="col-md-6 col-xl-3"><div class="card card-stat">
    <div class="card-header"><span class="stat-label">Penjualan Offtaker</span></div>
    <div class="stat-value"><?= e(rupiah($stat['penjualan'])) ?></div>
    <div class="trend-badge trend-up"><i class="bi bi-shop"></i><span>Pendapatan penjualan</span></div>
  </div></div>
  <div class="col-md-6 col-xl-3"><div class="card card-stat">
    <div class="card-header"><span class="stat-label">Menunggu Persetujuan</span></div>
    <div class="stat-value"><?= e($stat['menunggu']) ?></div>
    <div class="trend-badge trend-down"><i class="bi bi-hourglass-split"></i><span>Pencairan perlu diputus</span></div>
  </div></div>

  <div class="col-lg-8"><div class="card">
    <div class="card-header"><h2 class="card-title">Tren Nilai Setoran (12 Bulan)</h2></div>
    <div id="ketua-tren"></div>
  </div></div>
  <div class="col-lg-4"><div class="card h-100">
    <div class="card-header"><h2 class="card-title">Komposisi Kategori (kg)</h2></div>
    <div id="ketua-komposisi"></div>
  </div></div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Pencairan Menunggu Persetujuan</h2>
        <a href="persetujuan.php" class="card-more-btn" title="Kelola"><i class="bi bi-arrow-right"></i></a></div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>#</th><th>Nasabah</th><th>Tanggal</th><th class="text-end">Nominal</th></tr></thead>
          <tbody>
            <?php if (empty($cair)): ?>
              <tr><td colspan="4" class="text-center text-muted-green py-4">Tidak ada pengajuan menunggu.</td></tr>
            <?php else: foreach ($cair as $c): ?>
              <tr>
                <td class="table-order-id">#PC-<?= e(str_pad($c['id'], 3, '0', STR_PAD_LEFT)) ?></td>
                <td><?= e($c['nasabah']) ?></td>
                <td><?= e(tanggal_id($c['tanggal'])) ?></td>
                <td class="table-amount text-end"><?= e(rupiah($c['nominal'])) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header"><h2 class="card-title">Nasabah Teraktif</h2></div>
      <ul class="clean-list">
        <?php if (empty($top)): ?>
          <li class="clean-list-item"><div class="text-muted-green">Belum ada data.</div></li>
        <?php else: foreach ($top as $t): $pct = $max_top > 0 ? round((float)$t['total'] / $max_top * 100) : 0; ?>
          <li class="clean-list-item">
            <span class="table-user-avatar app-avatar-initial"><?= e(mb_substr($t['name'], 0, 1)) ?></span>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between"><strong><?= e($t['name']) ?></strong><span class="table-amount"><?= e(rupiah($t['total'])) ?></span></div>
              <div style="height:6px;border-radius:50rem;background:var(--bs-body-bg);margin-top:.4rem;overflow:hidden;">
                <div style="height:100%;width:<?= e($pct) ?>%;background:var(--brand-forest-medium);"></div>
              </div>
            </div>
          </li>
        <?php endforeach; endif; ?>
      </ul>
    </div>
  </div>
</div>

<script>
  window.__charts = [
    { id: 'ketua-tren', type: 'area', labels: <?= json_encode($chart_labels) ?>,
      series: [{ name: 'Nilai Setoran', data: <?= json_encode($chart_nilai) ?> }], money: true },
    { id: 'ketua-komposisi', type: 'donut', labels: <?= json_encode($kat_labels) ?>, series: <?= json_encode($kat_data) ?> }
  ];
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
