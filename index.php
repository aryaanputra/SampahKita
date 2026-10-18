<?php
/**
 * =====================================================================
 *  Bank Sampah Kali Laya Berseri
 *  index.php - Router berdasarkan peran pengguna
 * =====================================================================
 */
require_once __DIR__ . '/includes/auth.php';

if (!is_logged_in()) {
    header('Location: login-user.php');
    exit();
}

header('Location: ' . dashboard_url(current_user()['role']));
exit();
