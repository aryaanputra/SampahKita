<?php
/**
 * ketua/laporan.php - Laporan & rekapitulasi
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['ketua']);

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');
$from_esc = mysqli_real_escape_string($con, $from);
$to_esc   = mysqli_real_escape_string($con, $to . ' 23:59:59');

// Ringkasan
$r = mysqli_query($con, "SELECT COALESCE(SUM(total_nilai),0) v, COUNT(*) c FROM setoran WHERE tanggal BETWEEN '$from_esc' AND '$to_esc'");
$sum_setoran = $r ? mysqli_fetch_assoc($r) : ['v' => 0, 'c' => 0];

$r = mysqli_query($con, "SELECT COALESCE(SUM(d.berat_kg),0) v FROM detail_setoran d JOIN setoran s ON s.id = d.setoran_id WHERE s.tanggal BETWEEN '$from_esc' AND '$to_esc'");
$sum_berat = $r ? (float) mysqli_fetch_assoc($r)['v'] : 0;

$r = mysqli_query($con, "SELECT COALESCE(SUM(nominal),0) v, COUNT(*) c FROM pencairan WHERE tanggal BETWEEN '$from_esc' AND '$to_esc' AND status = 'selesai'");
$sum_cair = $r ? mysqli_fetch_assoc($r) : ['v' => 0, 'c' => 0];

$r = mysqli_query($con, "SELECT COALESCE(SUM(total),0) v, COALESCE(SUM(berat_kg),0) b FROM penjualan_offtaker WHERE tanggal BETWEEN '$from' AND '$to'");
$sum_off = $r ? mysqli_fetch_assoc($r) : ['v' => 0, 'b' => 0];

// Rekap per jenis
$per_jenis = [];
$res = mysqli_query($con, "SELECT j.nama, j.kategori, SUM(d.berat_kg) berat, SUM(d.subtotal) nilai FROM detail_setoran d JOIN setoran s ON s.id = d.setoran_id JOIN jenis_sampah j ON j.id = d.jenis_id WHERE s.tanggal BETWEEN '$from_esc' AND '$to_esc' GROUP BY j.id ORDER BY nilai DESC");
if ($res) { while ($x = mysqli_fetch_assoc($res)) { $per_jenis[] = $x; } }

// Rekap per nasabah
$per_nasabah = [];
$res = mysqli_query($con, "SELECT u.name, COUNT(s.id) jumlah, COALESCE(SUM(s.total_nilai),0) nilai FROM setoran s JOIN users u ON u.id_user = s.user_id WHERE s.tanggal BETWEEN '$from_esc' AND '$to_esc' GROUP BY u.id_user ORDER BY nilai DESC");
if ($res) { while ($x = mysqli_fetch_assoc($res)) { $per_nasabah[] = $x; } }

// Penjualan offtaker
$per_off = [];
$res = mysqli_query($con, "SELECT o.*, j.nama AS jenis FROM penjualan_offtaker o JOIN jenis_sampah j ON j.id = o.jenis_id WHERE o.tanggal BETWEEN '$from' AND '$to' ORDER BY o.tanggal DESC");
if ($res) { while ($x = mysqli_fetch_assoc($res)) { $per_off[] = $x; } }

$page_title    = 'Laporan';
$page_subtitle = 'Rekapitulasi operasional Bank Sampah.';
$active_menu   = 'laporan';
$page_actions  = '<button type="button" class="btn btn-dark-custom no-print" data-print><i class="bi bi-printer me-1"></i> Cetak</button>';
require __DIR__ . '/../includes/header.php';
?>

<div class="card mb-4 no-print">
  <div class="card-body">
    <form method="GET" action="laporan.php" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label-custom">Dari Tanggal</label>
        <input type="date" class="form-control-custom" name="from" value="<?= e($from) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label-custom">Sampai Tanggal</label>
        <input type="date" class="form-control-custom" name="to" value="<?= e($to) ?>">
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-dark-custom w-100"><i class="bi bi-funnel me-1"></i> Tampilkan</button>
      </div>
    </form>
  </div>
</div>

<div class="mb-4">
  <h2 class="h5 mb-1">Laporan Periode <?= e(tanggal_id($from)) ?> &ndash; <?= e(tanggal_id($to)) ?></h2>
  <p class="text-muted-green mb-0">Bank Sampah Kali Laya Berseri</p>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-3"><div class="card card-stat"><div class="card-header"><span class="stat-label">Setoran</span></div>
    <div class="stat-value"><?= e(rupiah($sum_setoran['v'])) ?></div><div class="trend-badge trend-up"><span><?= e($sum_setoran['c']) ?> transaksi · <?= e(kg($sum_berat)) ?></span></div></div></div>
  <div class="col-md-3"><div class="card card-stat"><div class="card-header"><span class="stat-label">Pencairan Selesai</span></div>
    <div class="stat-value"><?= e(rupiah($sum_cair['v'])) ?></div><div class="trend-badge trend-down"><span><?= e($sum_cair['c']) ?> transaksi</span></div></div></div>
  <div class="col-md-3"><div class="card card-stat"><div class="card-header"><span class="stat-label">Penjualan Offtaker</span></div>
    <div class="stat-value"><?= e(rupiah($sum_off['v'])) ?></div><div class="trend-badge trend-up"><span><?= e(kg($sum_off['b'])) ?></span></div></div></div>
  <div class="col-md-3"><div class="card card-stat"><div class="card-header"><span class="stat-label">Margin</span></div>
    <div class="stat-value"><?= e(rupiah((float) $sum_off['v'] - (float) $sum_setoran['v'])) ?></div><div class="trend-badge trend-up"><span>Penjualan - setoran</span></div></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="table-card-custom">
      <div class="table-header-control"><h2 class="card-title mb-0">Rekap per Jenis Sampah</h2></div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>Jenis</th><th>Kategori</th><th>Berat</th><th class="text-end">Nilai</th></tr></thead>
          <tbody>
            <?php if (empty($per_jenis)): ?>
              <tr><td colspan="4" class="text-center text-muted-green py-3">Tidak ada data.</td></tr>
            <?php else: foreach ($per_jenis as $x): ?>
              <tr><td class="table-product-name"><?= e($x['nama']) ?></td><td><?= e($x['kategori']) ?></td>
                <td><?= e(kg($x['berat'])) ?></td><td class="table-amount text-end"><?= e(rupiah($x['nilai'])) ?></td></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="table-card-custom">
      <div class="table-header-control"><h2 class="card-title mb-0">Rekap per Nasabah</h2></div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>Nasabah</th><th>Jml Setoran</th><th class="text-end">Nilai</th></tr></thead>
          <tbody>
            <?php if (empty($per_nasabah)): ?>
              <tr><td colspan="3" class="text-center text-muted-green py-3">Tidak ada data.</td></tr>
            <?php else: foreach ($per_nasabah as $x): ?>
              <tr><td class="table-product-name"><?= e($x['name']) ?></td><td><?= e($x['jumlah']) ?>x</td>
                <td class="table-amount text-end"><?= e(rupiah($x['nilai'])) ?></td></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="table-card-custom">
      <div class="table-header-control"><h2 class="card-title mb-0">Penjualan ke Offtaker</h2></div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>Tanggal</th><th>Offtaker</th><th>Jenis</th><th>Berat</th><th>Harga/kg</th><th class="text-end">Total</th></tr></thead>
          <tbody>
            <?php if (empty($per_off)): ?>
              <tr><td colspan="6" class="text-center text-muted-green py-3">Tidak ada data.</td></tr>
            <?php else: foreach ($per_off as $x): ?>
              <tr><td><?= e(tanggal_id($x['tanggal'])) ?></td><td class="table-product-name"><?= e($x['nama_offtaker']) ?></td>
                <td><?= e($x['jenis']) ?></td><td><?= e(kg($x['berat_kg'])) ?></td><td><?= e(rupiah($x['harga'])) ?></td>
                <td class="table-amount text-end"><?= e(rupiah($x['total'])) ?></td></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
