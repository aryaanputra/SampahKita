<?php
/**
 * =====================================================================
 *  Bank Sampah Kali Laya Berseri
 *  oauth-google.php - Mulai proses "Masuk dengan Google"
 * =====================================================================
 */

require_once __DIR__ . '/includes/google_oauth.php';

if (!google_is_configured()) {
    $_SESSION['oauth_error'] = 'Login Google belum dikonfigurasi. Isi Client ID & Secret pada google-config.php.';
    header('Location: login-user.php');
    exit();
}

$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

header('Location: ' . google_auth_url($state));
exit();
