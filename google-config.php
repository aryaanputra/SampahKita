<?php
/**
 * =====================================================================
 *  Bank Sampah Kali Laya Berseri
 *  google-config.php - Kredensial Google OAuth 2.0 (Login dengan Google)
 * =====================================================================
 *
 * Cara mengisi:
 *  1. Buka https://console.cloud.google.com/apis/credentials
 *  2. Buat "OAuth client ID" tipe "Web application".
 *  3. Pada "Authorized redirect URIs", tambahkan:
 *        http://localhost/kalilaya/oauth-google-callback.php
 *     (atau URL sesuai tempat aplikasi diakses - lihat halaman login).
 *  4. Salin Client ID & Client Secret ke bawah ini.
 *
 * Catatan: redirect URI harus SAMA PERSIS dengan yang didaftarkan di Google.
 */

define('GOOGLE_CLIENT_ID', '79890024391-no5n0eagh65cg86meh0n8jqfpcl4daas.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');

/**
 * URI callback. Secara default dihitung otomatis dari URL permintaan,
 * sehingga cocok baik diakses lewat localhost/kalilaya maupun kalilaya.test.
 * Isi manual (string) bila ingin dipaksa, mis. 'http://localhost/kalilaya/oauth-google-callback.php'.
 */
define('GOOGLE_REDIRECT_URI', ''); // kosong = otomatis
