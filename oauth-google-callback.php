<?php
/**
 * =====================================================================
 *  Bank Sampah Kali Laya Berseri
 *  oauth-google-callback.php - Callback dari Google (OAuth 2.0)
 * =====================================================================
 */

require_once __DIR__ . '/includes/google_oauth.php';
require_once __DIR__ . '/includes/auth.php';

function google_fail(string $message): void
{
    $_SESSION['oauth_error'] = $message;
    header('Location: login-user.php');
    exit();
}

if (!google_is_configured()) {
    google_fail('Login Google belum dikonfigurasi.');
}

// Google mengirim ?error=... bila pengguna menolak / ada masalah
if (!empty($_GET['error'])) {
    google_fail('Login Google dibatalkan atau gagal: ' . htmlspecialchars((string) $_GET['error'], ENT_QUOTES, 'UTF-8'));
}

$state = (string) ($_GET['state'] ?? '');
$code  = (string) ($_GET['code'] ?? '');

// Verifikasi state (anti-CSRF)
if (empty($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], $state)) {
    google_fail('Sesi login Google tidak valid. Silakan coba lagi.');
}
if ($code === '') {
    google_fail('Kode otorisasi Google tidak diterima.');
}

// Tukar code menjadi access token
$accessToken = google_exchange_code($code);
if (!$accessToken) {
    google_fail('Gagal menukar kode otorisasi dengan Google.');
}

// Ambil profil pengguna
$profile = google_get_userinfo($accessToken);
if (!$profile) {
    google_fail('Gagal mengambil profil dari Google.');
}

// Login atau daftarkan otomatis
$result = google_login_or_register($con, $profile);
if (!$result['ok']) {
    google_fail($result['error'] ?? 'Login Google gagal.');
}

google_session_login($result['user']);
