# Bank Sampah Kali Laya Berseri

Aplikasi web pengelolaan bank sampah berbasis **PHP + MySQL** dengan tiga peran
pengguna: **Nasabah**, **Pengurus**, dan **Ketua**. Tampilan mengikuti design
system template *Spark Admin* (Bootstrap 5, aset 100% lokal/offline).

## Fitur per Peran

| Peran | Halaman |
|-------|---------|
| **Nasabah** | Dasbor saldo, Setor sampah (harga & riwayat), Minta jemput, Saldo & riwayat mutasi, Ajukan pencairan, Edukasi |
| **Pengurus** | Dasbor, Kelola nasabah, Jenis sampah, Timbang & catat setoran, Penjemputan, Pencairan (proses), Setor ke offtaker, Kuitansi |
| **Ketua** | Dasbor, Persetujuan pencairan, Laporan (rekap per jenis/nasabah/offtaker, filter periode) |

## Kebutuhan

- PHP **8.0+** (dites pada PHP 8.3; ekstensi `mysqli`, `mbstring`).
- MySQL / MariaDB.
- Web server (XAMPP/Laragon) atau `php -S`.

## Instalasi

1. **Impor database**
   - Buat/impor skema + data demo:
     ```
     mysql -u root -p < database.sql
     ```
   - Atau melalui **phpMyAdmin → Import → pilih `database.sql`**.
   - Ini membuat database `db_kalilaya` beserta tabel dan data contoh.

2. **Konfigurasi koneksi**
   - Sesuaikan `connection.php` bila perlu (default: host `localhost`,
     user `root`, password kosong, database `db_kalilaya`).

3. **Konfigurasi email (opsional)**
   - Pendaftaran mandiri & lupa password mengirim kode OTP lewat email.
     Isi kredensial SMTP pada `mailer.php`.
   - Pengurus dapat menambah nasabah langsung tanpa email (status langsung
     *verified*), sehingga aplikasi tetap bisa dipakai walau SMTP belum diisi.

4. **Akses aplikasi**
   - Taruh folder ini di document root web server, lalu buka
     `login-user.php` (mis. `http://localhost/kalilaya/login-user.php`).
   - Halaman masuk pertama: `login-user.php`. `index.php` akan mengarahkan
     otomatis ke dasbor sesuai peran.

## Menjalankan dengan Laragon

Proyek ini sudah disiapkan agar berjalan dengan **Laragon** (Apache 2.4 +
PHP 8.3 + MySQL 8.4):

1. Buka **Laragon**, klik **Start All** (menyalakan Apache & MySQL).
2. Proyek diakses lewat folder `www` Laragon. Bila proyek berada di luar
   `C:\laragon\www`, buat tautan (junction) tanpa menyalin berkas:
   ```
   mklink /J "C:\laragon\www\kalilaya" "C:\Users\admin\Documents\kalilaya"
   ```
3. Impor database melalui **Database** Laragon (HeidiSQL/phpMyAdmin) atau:
   ```
   C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root < database.sql
   ```
4. Buka aplikasi:
   - **http://localhost/kalilaya/login-user.php**
   - Atau, aktifkan **Auto virtual hosts** pada Laragon agar dapat diakses
     lewat **http://kalilaya.test** (Laragon menambahkan entri ke `hosts`
     secara otomatis; jalankan Laragon sebagai Administrator).

> Konfigurasi MySQL Laragon untuk proyek ini: host `localhost`, port `3306`,
> user `root`, password **kosong** — sama dengan bawaan `connection.php`.

## Login dengan Google (Opsional)

Aplikasi mendukung "Masuk dengan Google" via OAuth 2.0. Untuk mengaktifkan:

1. Buka **https://console.cloud.google.com/apis/credentials**.
2. Buat **OAuth client ID** tipe **Web application**.
3. Pada **Authorized redirect URIs**, tambahkan (harus sama persis):
   ```
   http://localhost/kalilaya/oauth-google-callback.php
   ```
4. Salin **Client ID** & **Client Secret** ke `google-config.php`.
5. Selesai — tombol **Masuk dengan Google** muncul di `login-user.php`.

Perilaku:
- Email Google yang cocok dengan akun lama akan otomatis ditautkan (login ke peran yang sama).
- Email Google baru otomatis dibuatkan akun baru dengan peran **nasabah** (status terverifikasi).
- Butuh koneksi internet saat login.

File terkait: `google-config.php`, `includes/google_oauth.php`, `oauth-google.php`, `oauth-google-callback.php`.

## Akun Demo

Kata sandi semua akun: **`kalilaya123`**

| Email | Peran |
|-------|-------|
| `nasabah@kalilaya.test` | Nasabah |
| `pengurus@kalilaya.test` | Pengurus |
| `ketua@kalilaya.test` | Ketua |
| `ahmad@kalilaya.test` | Nasabah |
| `dewi@kalilaya.test` | Nasabah |
| `rudi@kalilaya.test` | Nasabah |

Data demo mencakup: 8 jenis sampah, 8 setoran, mutasi saldo yang konsisten,
pencairan (termasuk satu berstatus *disetujui* siap diproses Pengurus),
permintaan penjemputan, penjualan offtaker, dan konten edukasi.

## Alur Kerja Utama

1. **Setoran**: Pengurus → *Timbang & Catat* → pilih nasabah, isi baris
   timbangan → simpan. Sistem otomatis membuat setoran, rincian, menambah
   stok jenis sampah, dan menambah **saldo nasabah** (mutasi kredit), lalu
   menampilkan **kuitansi** yang dapat dicetak.
2. **Pencairan**: Nasabah mengajukan → **Ketua** menyetujui/menolak →
   **Pengurus** memproses pembayaran (status *selesai*, saldo dikurangi).
3. **Penjemputan**: Nasabah mengajukan → permintaan otomatis tercatat sebagai
   setoran (dengan estimasi dari form) → **Pengurus** menjadwalkan/menyelesaikan.
4. **Offtaker**: Pengurus mencatat penjualan sampah terjual (stok berkurang).

## Struktur Direktori

```
kalilaya/
├─ database.sql              Skema + data demo
├─ connection.php            Koneksi database (mysqli)
├─ controllerUserData.php    Auth: daftar, OTP, masuk, lupa/reset password
├─ mailer.php                Pengiriman email (PHPMailer)
├─ login-user.php, signup-user.php, user-otp.php,
│  forgot-password.php, reset-code.php, new-password.php,
│  password-changed.php, logout-user.php
├─ index.php                 Router berdasarkan peran
├─ includes/
│  ├─ auth.php               Sesi, guard peran, CSRF, flash message
│  ├─ fungsi.php             Helper (rupiah, tanggal, badge, saldo)
│  ├─ header.php             Sidebar + navbar (design Spark Admin)
│  └─ footer.php
├─ nasabah/                  Halaman peran nasabah
├─ pengurus/                 Halaman peran pengurus
├─ ketua/                    Halaman peran ketua
└─ assets/                   CSS/JS/libs (main.css, app.css, app.js, libs)
```

## Catatan Keamanan

- Kata sandi disimpan dengan `password_hash` (bcrypt).
- Semua aksi POST dilindungi token **CSRF** (`csrf_field()` / `require_csrf()`).
- Akses halaman dibatasi per peran (`require_role()`); pengguna tanpa hak
  diarahkan ke dasbornya.
- Query memakai *prepared statements* untuk mencegah SQL injection, dan output
  di-escape (`e()`) untuk mencegah XSS.
