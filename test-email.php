<?php
require_once __DIR__ . '/mailer.php';

$tujuan = 'aryaanputrarisqi@gmail.com';

if (sendAppEmail(
    $tujuan,
    'Tes Email Brevo - LoginSignup',
    'Jika email ini diterima, konfigurasi SMTP Brevo berhasil.'
)) {
    echo 'Email berhasil dikirim ke server SMTP Brevo.';
} else {
    echo 'Pengiriman email gagal. Periksa error log PHP.';
}
?>