<?php
/**
 * nasabah/jemput.php - Ajukan permintaan penjemputan sampah
 */
$base = '../';
$GLOBALS['base'] = $base;

require_once __DIR__ . '/../includes/auth.php';
require_role(['nasabah']);

/**
 * Catat permintaan penjemputan sebagai setoran (otomatis) + kredit saldo.
 * Permintaan sendiri tetap tersimpan di permintaan_jemput.
 * Mengembalikan true bila setoran berhasil dibuat.
 */
function catat_setoran_dari_permintaan(mysqli $con, int $user_id, string $tanggal, string $catatan, string $estimasi): bool
{
    $parsed = parse_estimasi_jemput($con, $estimasi);
    if (empty($parsed['jenis_ids']) || $parsed['berat_total'] <= 0) {
        return false;
    }

    // Siapkan rincian detail & total nilai sebelum insert.
    $jumlah_jenis = count($parsed['jenis_ids']);
    $total = 0.0;
    $rincian = [];
    foreach ($parsed['jenis_ids'] as $jenis_id) {
        $stmt = mysqli_prepare(
            $con,
            'SELECT harga_per_kg FROM jenis_sampah WHERE id = ?'
        );
        mysqli_stmt_bind_param($stmt, 'i', $jenis_id);
        mysqli_stmt_execute($stmt);
        $harga = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
        mysqli_stmt_close($stmt);
        if ($harga === null) {
            continue;
        }
        $harga_per_kg = (float) $harga['harga_per_kg'];
        $berat = $parsed['berat_total'] / $jumlah_jenis;
        $subtotal = $berat * $harga_per_kg;
        $total += $subtotal;
        $rincian[] = [
            'jenis_id' => $jenis_id,
            'berat' => $berat,
            'harga' => $harga_per_kg,
            'subtotal' => $subtotal,
        ];
    }
    if (empty($rincian)) {
        return false;
    }

    $stmt = mysqli_prepare(
        $con,
        'INSERT INTO setoran (user_id, tanggal, total_nilai, petugas_id, catatan) VALUES (?, ?, ?, NULL, ?)'
    );
    $catatan_setoran = $catatan !== '' ? $catatan : null;
    mysqli_stmt_bind_param($stmt, 'isds', $user_id, $tanggal, $total, $catatan_setoran);
    mysqli_stmt_execute($stmt);
    $setoran_id = mysqli_insert_id($con);

    foreach ($rincian as $rincian_item) {
        $stmt = mysqli_prepare(
            $con,
            'INSERT INTO detail_setoran
                (setoran_id, jenis_id, berat_kg, harga_per_kg, subtotal)
             VALUES (?, ?, ?, ?, ?)'
        );
        $rincian_jenis = $rincian_item['jenis_id'];
        $rincian_berat = $rincian_item['berat'];
        $rincian_harga = $rincian_item['harga'];
        $rincian_subtotal = $rincian_item['subtotal'];
        mysqli_stmt_bind_param(
            $stmt,
            'iiddd',
            $setoran_id,
            $rincian_jenis,
            $rincian_berat,
            $rincian_harga,
            $rincian_subtotal
        );
        mysqli_stmt_execute($stmt);
    }

    // Saldo nasabah otomatis kena kredit.
    $stmt = mysqli_prepare(
        $con,
        'SELECT saldo_akhir FROM saldo_mutasi WHERE user_id = ? ORDER BY id DESC LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $mutasi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $setelah = ($mutasi ? (float) $mutasi['saldo_akhir'] : 0.0) + $total;

    $stmt = mysqli_prepare(
        $con,
        "INSERT INTO saldo_mutasi
            (user_id, tipe, nominal, saldo_akhir, ref_type, ref_id, keterangan, tanggal)
         VALUES (?, 'kredit', ?, ?, 'setoran', ?, 'Setoran sampah', ?)"
    );
    mysqli_stmt_bind_param($stmt, 'iddis', $user_id, $total, $setelah, $setoran_id, $tanggal);
    mysqli_stmt_execute($stmt);

    return true;
}

$u   = current_user();
$uid = $u['id_user'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $alamat   = trim($_POST['alamat'] ?? '');
    $jadwal   = trim($_POST['jadwal'] ?? '');
    $estimasi = trim($_POST['estimasi'] ?? '');
    $catatan  = trim($_POST['catatan'] ?? '');

    if ($alamat === '') { $errors[] = 'Alamat penjemputan wajib diisi.'; }
    if ($jadwal === '') { $errors[] = 'Jadwal penjemputan wajib diisi.'; }

    if (empty($errors)) {
        $stmt = mysqli_prepare($con, "INSERT INTO permintaan_jemput (user_id, alamat, jadwal, estimasi, status, catatan) VALUES (?, ?, ?, ?, 'Diminta', ?)");
        mysqli_stmt_bind_param($stmt, 'issss', $uid, $alamat, $jadwal, $estimasi, $catatan);
        if (mysqli_stmt_execute($stmt)) {
            // Permintaan otomatis tercatat sebagai setoran.
            $setoran_dibuat = false;
            try {
                $setoran_dibuat = catat_setoran_dari_permintaan($con, $uid, $jadwal, $catatan, $estimasi);
            } catch (Throwable $ex) {
                $errors[] = 'Permintaan tersimpan, tetapi setoran otomatis gagal: ' . $ex->getMessage();
            }
            if (empty($errors)) {
                set_flash('success', $setoran_dibuat
                    ? 'Permintaan penjemputan berhasil dikirim dan tercatat sebagai setoran.'
                    : 'Permintaan penjemputan berhasil dikirim. Isi estimasi jenis & berat agar otomatis tercatat sebagai setoran.');
                header('Location: jemput.php');
                exit();
            }
        } else {
            $errors[] = 'Gagal menyimpan permintaan.';
        }
    }
}

$stmt = mysqli_prepare($con, "SELECT * FROM permintaan_jemput WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$rows = [];
while ($r = mysqli_fetch_assoc($res)) { $rows[] = $r; }
mysqli_stmt_close($stmt);

$page_title    = 'Minta Jemput';
$page_subtitle = 'Ajukan penjemputan sampah ke lokasi Anda, otomatis menjadi setoran.';
$active_menu   = 'jemput';
require __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Form Penjemputan</h2></div>
      <div class="card-body pt-3">
        <p class="text-muted small">
          Permintaan akan otomatis dicatat sebagai setoran sesuai estimasi jenis &amp; berat yang diisi.
        </p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert-custom alert-custom-danger mb-3">
          <i class="bi bi-exclamation-octagon-fill alert-custom-icon"></i>
          <div class="alert-custom-content"><?= e(implode(' ', $errors)) ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" action="jemput.php">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label-custom" for="alamat">Alamat Penjemputan</label>
          <input type="text" class="form-control-custom" id="alamat" name="alamat" value="<?= e($_POST['alamat'] ?? '') ?>" placeholder="Alamat lengkap" required>
        </div>
        <div class="mb-3">
          <label class="form-label-custom" for="jadwal">Jadwal</label>
          <input type="datetime-local" class="form-control-custom" id="jadwal" name="jadwal" value="<?= e($_POST['jadwal'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label-custom" for="estimasi">Estimasi Jenis & Berat</label>
          <input type="text" class="form-control-custom" id="estimasi" name="estimasi" value="<?= e($_POST['estimasi'] ?? '') ?>" placeholder="mis. Plastik, Kardus (± 10 kg)">
        </div>
        <div class="mb-3">
          <label class="form-label-custom" for="catatan">Catatan</label>
          <textarea class="form-control-custom" id="catatan" name="catatan" rows="2" placeholder="Catatan tambahan (opsional)"><?= e($_POST['catatan'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-dark-custom w-100"><i class="bi bi-send me-1"></i> Kirim Permintaan</button>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="table-card-custom">
      <div class="table-header-control"><h2 class="card-title mb-0">Riwayat Penjemputan</h2></div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead><tr><th>#</th><th>Jadwal</th><th>Alamat</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="4" class="text-center text-muted-green py-4">Belum ada permintaan.</td></tr>
            <?php else: foreach ($rows as $r): ?>
              <tr>
                <td class="table-order-id">#PJ-<?= e(str_pad($r['id'], 3, '0', STR_PAD_LEFT)) ?></td>
                <td><?= e(tanggal_waktu_id($r['jadwal'])) ?></td>
                <td><?= e(potong_teks($r['alamat'], 35)) ?><div class="table-user-sub"><?= e(potong_teks($r['estimasi'] ?: '', 40)) ?></div></td>
                <td><?= badge_jemput($r['status']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
