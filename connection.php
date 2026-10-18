<?php
/**
 * Koneksi database Aplikasi Bank Sampah Kali Laya Berseri.
 * Sesuaikan kredensial di bawah bila perlu.
 */
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'db_kalilaya';

$con = @mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$con) {
    http_response_code(500);
    die(
        'Koneksi database gagal: ' . mysqli_connect_error()
            . '. Pastikan MySQL (Laragon/XAMPP) berjalan dan database "db_kalilaya" sudah diimpor dari database.sql.'
    );
}

mysqli_set_charset($con, 'utf8mb4');
