<?php
/**
 * pengurus/offtaker.php - Catat penjualan sampah ke offtaker
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['pengurus']);

$u   = current_user();
$uid = $u['id_user'];
$errors = [];

$jenis_list = [];
$res = mysqli_query($con, "SELECT * FROM jenis_sampah ORDER BY kategori ASC");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $jenis_list[] = $r; } }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $tanggal   = trim($_POST['tanggal'] ?? '');
    $offtaker  = trim($_POST['nama_offtaker'] ?? '');
    $jenis_id  = (int) ($_POST['jenis_id'] ?? 0);
    $berat     = (float) str_replace(',', '.', $_POST['berat_kg'] ?? '0');
    $harga     = (float) ($_POST['harga'] ?? 0);

    if ($tanggal === '') { $errors[] = 'Tanggal wajib diisi.'; }
    if ($offtaker === '') { $errors[] = 'Nama offtaker wajib diisi.'; }
    if ($jenis_id <= 0) { $errors[] = 'Pilih jenis sampah.'; }
    if ($berat <= 0) { $errors[] = 'Berat harus lebih dari 0.'; }
    if ($harga <= 0) { $errors[] = 'Harga harus lebih dari 0.'; }

    if (empty($errors)) {
        $total = $berat * $harga;
        try {
            $stmt = mysqli_prepare($con, "INSERT INTO penjualan_offtaker (tanggal, nama_offtaker, jenis_id, berat_kg, harga, total, petugas_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssidddi', $tanggal, $offtaker, $jenis_id, $berat, $harga, $total, $uid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            set_flash('success', 'Penjualan ke offtaker dicatat (total ' . rupiah($total) . ').');
            header('Location: offtaker.php');
            exit();
        } catch (Throwable $ex) {
            $errors[] = 'Gagal menyimpan data: ' . $ex->getMessage();
        }
    }
}

$rows = [];
$res = mysqli_query($con, "SELECT o.*, j.kategori AS jenis, p.name AS petugas FROM penjualan_offtaker o JOIN jenis_sampah j ON j.id = o.jenis_id LEFT JOIN users p ON p.id_user = o.petugas_id ORDER BY o.tanggal DESC");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; } }

$total_penjualan = 0.0;
$res = mysqli_query($con, "SELECT COALESCE(SUM(total),0) v FROM penjualan_offtaker");
if ($res) { $total_penjualan = (float) mysqli_fetch_assoc($res)['v']; }

$page_title    = 'Setor ke Offtaker';
$page_subtitle = 'Catat penjualan sampah yang telah dipilah ke pengepul/offtaker.';
$active_menu   = 'offtaker';
require __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert-custom alert-custom-danger mb-4">
    <i class="bi bi-exclamation-octagon-fill alert-custom-icon"></i>
    <div class="alert-custom-content"><?= e(implode(' ', $errors)) ?></div>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card mb-4" style="background: linear-gradient(135deg, var(--brand-forest-medium), var(--brand-forest-dark)); color:#fff; border:none;">
      <div class="card-body">
        <span class="stat-label" style="color: rgba(255,255,255,.75);">Total Penjualan</span>
        <div class="stat-value" style="color:#fff; font-size:1.8rem; margin:.3rem 0;"><?= e(rupiah($total_penjualan)) ?></div>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><h2 class="card-title">Form Penjualan</h2></div>
      <form method="POST" action="offtaker.php">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label-custom">Tanggal</label>
          <input type="date" class="form-control-custom" name="tanggal" required value="<?= e($_POST['tanggal'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label-custom">Nama Offtaker</label>
          <input type="text" class="form-control-custom" name="nama_offtaker" required value="<?= e($_POST['nama_offtaker'] ?? '') ?>" placeholder="mis. PT Daur Ulang Nusantara">
        </div>
        <div class="mb-3">
          <label class="form-label-custom">Jenis Sampah</label>
          <select class="form-select-custom" name="jenis_id" required>
            <option value="">-- Pilih jenis --</option>
            <?php foreach ($jenis_list as $j): ?>
              <option value="<?= e($j['id']) ?>" <?= ((int)($_POST['jenis_id'] ?? 0) === (int)$j['id']) ? 'selected' : '' ?>><?= e($j['kategori']) ?> (<?= e(rupiah($j['harga_per_kg'])) ?>/kg)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6"><label class="form-label-custom">Berat (kg)</label>
            <input type="number" step="0.01" min="0" class="form-control-custom" name="berat_kg" required value="<?= e($_POST['berat_kg'] ?? '') ?>"></div>
          <div class="col-6"><label class="form-label-custom">Harga/kg (Rp)</label>
            <input type="number" min="0" step="100" class="form-control-custom" name="harga" required value="<?= e($_POST['harga'] ?? '') ?>"></div>
        </div>
        <button type="submit" class="btn btn-dark-custom w-100"><i class="bi bi-check-lg me-1"></i> Simpan Penjualan</button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="table-card-custom">
      <div class="table-header-control">
        <div class="table-search-box"><i class="bi bi-search table-search-icon"></i>
          <input type="text" class="table-search-input table-local-search" placeholder="Cari penjualan..."></div>
        <span class="badge-table info"><?= count($rows) ?> transaksi</span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>Tanggal</th><th>Offtaker</th><th>Kategori</th><th>Berat</th><th>Harga/kg</th><th class="text-end">Total</th></tr></thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="6" class="text-center text-muted-green py-4">Belum ada penjualan.</td></tr>
            <?php else: foreach ($rows as $r): ?>
              <tr>
                <td><?= e(tanggal_id($r['tanggal'])) ?></td>
                <td class="table-product-name"><?= e($r['nama_offtaker']) ?><div class="table-user-sub">oleh <?= e($r['petugas'] ?: '-') ?></div></td>
                <td><?= e($r['jenis']) ?></td>
                <td><?= e(kg($r['berat_kg'])) ?></td>
                <td><?= e(rupiah($r['harga'])) ?></td>
                <td class="table-amount text-end"><?= e(rupiah($r['total'])) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>