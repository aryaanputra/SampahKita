<?php
/**
 * nasabah/edukasi.php - Edukasi & tips pengelolaan sampah
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['nasabah']);

$rows = [];
$res = mysqli_query($con, "SELECT * FROM edukasi ORDER BY tanggal DESC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
}

$page_title    = 'Edukasi';
$page_subtitle = 'Tips dan panduan mengelola sampah rumah tangga.';
$active_menu   = 'edukasi';
require __DIR__ . '/../includes/header.php';
?>

<?php if (empty($rows)): ?>
  <div class="card"><div class="card-body text-center text-muted-green py-5">Belum ada konten edukasi.</div></div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($rows as $r): ?>
      <div class="col-md-6 col-xl-4">
        <div class="card edu-card">
          <div class="edu-thumb"><i class="bi bi-recycle"></i></div>
          <div class="card-body d-flex flex-column">
            <span class="stat-mini-label"><?= e(tanggal_id($r['tanggal'])) ?></span>
            <h3 class="h5 mt-1 mb-2"><?= e($r['judul']) ?></h3>
            <p class="text-muted-green" style="font-size:.92rem;"><?= e(potong_teks($r['isi'], 180)) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
