<?php
/**
 * pengurus/jenis_sampah.php - Kelola jenis sampah & harga
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['pengurus']);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'simpan') {
        $id = (int) ($_POST['id'] ?? 0);
        $kategori = trim($_POST['kategori'] ?? '');
        $harga = (float) ($_POST['harga_per_kg'] ?? 0);

        if ($kategori === '') { $errors[] = 'Kategori wajib diisi.'; }
        if ($harga < 0) { $errors[] = 'Harga tidak boleh negatif.'; }

        if (empty($errors)) {
            if ($id > 0) {
                $stmt = mysqli_prepare($con, "UPDATE jenis_sampah SET kategori = ?, harga_per_kg = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'sdi', $kategori, $harga, $id);
            } else {
                $stmt = mysqli_prepare($con, "INSERT INTO jenis_sampah (kategori, harga_per_kg) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, 'sd', $kategori, $harga);
            }
            if (mysqli_stmt_execute($stmt)) {
                set_flash('success', 'Data jenis sampah disimpan.');
                header('Location: jenis_sampah.php');
                exit();
            }
            $errors[] = 'Gagal menyimpan data jenis sampah.';
        }
    }

    if ($aksi === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        $chk = mysqli_query($con, "SELECT COUNT(*) c FROM detail_setoran WHERE jenis_id = " . $id);
        $used = $chk ? (int) mysqli_fetch_assoc($chk)['c'] : 0;
        if ($used > 0) {
            $errors[] = 'Jenis sampah tidak dapat dihapus karena sudah dipakai pada riwayat setoran.';
        } else {
            mysqli_query($con, "DELETE FROM penjualan_offtaker WHERE jenis_id = " . $id);
            mysqli_query($con, "DELETE FROM jenis_sampah WHERE id = " . $id);
            set_flash('info', 'Jenis sampah dihapus.');
            header('Location: jenis_sampah.php');
            exit();
        }
    }
}

$rows = [];
$res = mysqli_query($con, "SELECT j.*, COALESCE(SUM(d.berat_kg),0) terpakai FROM jenis_sampah j LEFT JOIN detail_setoran d ON d.jenis_id = j.id GROUP BY j.id ORDER BY j.kategori");
if ($res) { while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; } }

$edit = null;
if (isset($_GET['edit'])) {
    $e = (int) $_GET['edit'];
    $q = mysqli_query($con, "SELECT * FROM jenis_sampah WHERE id = " . $e . " LIMIT 1");
    if ($q) { $edit = mysqli_fetch_assoc($q); }
}

$page_title    = 'Jenis Sampah';
$page_subtitle = 'Kelola daftar jenis sampah beserta harga per kilogram.';
$active_menu   = 'jenis';
$page_actions  = '<button class="btn btn-dark-custom" type="button" data-scroll="#form-jenis"><i class="bi bi-plus-lg me-1"></i> Tambah Jenis</button>';
require __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($errors)): ?>
  <div class="alert-custom alert-custom-danger mb-4">
    <i class="bi bi-exclamation-octagon-fill alert-custom-icon"></i>
    <div class="alert-custom-content"><?= e(implode(' ', $errors)) ?></div>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-4" id="form-jenis">
    <div class="card">
      <div class="card-header"><h2 class="card-title"><?= $edit ? 'Edit Jenis' : 'Tambah Jenis' ?></h2></div>
      <form method="POST" action="jenis_sampah.php">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? 0) ?>">

        <div class="mb-3">
          <label class="form-label-custom">Kategori</label>
          <input type="text" class="form-control-custom" name="kategori" min="0" step="100" required value="<?= e($edit['kategori'] ?? ($_POST['kategori'] ?? '')) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label-custom">Harga per kg (Rp)</label>
          <input type="number" class="form-control-custom" name="harga_per_kg" min="0" step="100" required value="<?= e($edit['harga_per_kg'] ?? ($_POST['harga_per_kg'] ?? '')) ?>">
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-dark-custom flex-grow-1"><i class="bi bi-check-lg me-1"></i> Simpan</button>
          <?php if ($edit): ?><a href="jenis_sampah.php" class="btn" style="background:var(--bs-body-bg);color:var(--text-main);">Batal</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="table-card-custom">
      <div class="table-header-control">
        <div class="table-search-box"><i class="bi bi-search table-search-icon"></i>
          <input type="text" class="table-search-input table-local-search" placeholder="Cari jenis sampah..."></div>
        <span class="badge-table info"><?= count($rows) ?> jenis</span>
      </div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>Kategori</th><th>Harga/kg</th><th>Terpakai</th><th class="text-center">Aksi</th></tr></thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="4" class="text-center text-muted-green py-4">Belum ada jenis sampah.</td></tr>
            <?php else: foreach ($rows as $r): ?>
              <tr>
                <td class="table-product-name"><?= e($r['kategori']) ?></td>
                <td class="table-amount"><?= e(rupiah($r['harga_per_kg'])) ?></td>
                <td><?= e(kg($r['terpakai'])) ?></td>
                <td class="text-center">
                  <div class="table-actions justify-content-center">
                    <a href="jenis_sampah.php?edit=<?= e($r['id']) ?>" class="table-btn-action" title="Edit"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="jenis_sampah.php" class="d-inline" data-confirm="Hapus jenis &quot;<?= e($r['kategori']) ?>&quot;?">
                      <?= csrf_field() ?>
                      <input type="hidden" name="aksi" value="hapus">
                      <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                      <button type="submit" class="table-btn-action delete" title="Hapus"><i class="bi bi-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>