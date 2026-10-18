<?php require_once "controllerUserData.php"; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Nasabah - Bank Sampah Kali Laya Berseri</title>
    <meta name="description" content="Registrasi nasabah Bank Sampah Kali Laya Berseri">
    <link rel="icon" type="image/png" href="assets/images/favicon.ico">
    <link rel="stylesheet" href="assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/libs/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body>
    <div class="login-wrapper">
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <div class="login-card login-card-wide">
            <a href="login-user.php" class="login-brand text-decoration-none">
                <i class="bi bi-recycle"></i>
                <span>Kali Laya Berseri</span>
            </a>

            <p class="login-subtitle">
                Daftar sebagai nasabah untuk mulai menyetor sampah dan mengelola saldo tabungan Anda.
            </p>

            <form action="signup-user.php" method="POST" id="signupForm" class="needs-validation" autocomplete="on">

                <?php if (count($errors) > 0) { ?>
                    <div class="alert alert-danger">
                        <?php if (count($errors) == 1) { ?>
                            <?php foreach ($errors as $showerror) { ?>
                                <p class="mb-0"><?php echo htmlspecialchars($showerror, ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php } ?>
                        <?php } else { ?>
                            <ul class="mb-0">
                                <?php foreach ($errors as $showerror) { ?>
                                    <li><?php echo htmlspecialchars($showerror, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>
                <?php } ?>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="name" class="login-form-label">Nama Lengkap</label>
                        <div class="login-input-group">
                            <i class="bi bi-person input-icon"></i>
                            <input type="text" id="name" name="name" class="login-input"
                                placeholder="Nama lengkap Anda" autocomplete="name" required
                                value="<?php echo htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="email" class="login-form-label">Email</label>
                        <div class="login-input-group">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" id="email" name="email" class="login-input"
                                placeholder="nama@email.com" autocomplete="email" required
                                value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="nik" class="login-form-label">NIK</label>
                        <div class="login-input-group">
                            <i class="bi bi-person-vcard input-icon"></i>
                            <input type="text" id="nik" name="nik" class="login-input" inputmode="numeric"
                                placeholder="16 digit NIK" pattern="[0-9]{8,20}" required
                                value="<?php echo htmlspecialchars($_POST['nik'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="no_hp" class="login-form-label">No. HP</label>
                        <div class="login-input-group">
                            <i class="bi bi-telephone input-icon"></i>
                            <input type="text" id="no_hp" name="no_hp" class="login-input" inputmode="tel"
                                placeholder="08xxxxxxxxxx" pattern="[0-9+]{8,20}" required
                                value="<?php echo htmlspecialchars($_POST['no_hp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="alamat" class="login-form-label">Alamat</label>
                        <div class="login-input-group">
                            <i class="bi bi-geo-alt input-icon"></i>
                            <input type="text" id="alamat" name="alamat" class="login-input"
                                placeholder="Alamat lengkap (RT/RW)"
                                value="<?php echo htmlspecialchars($_POST['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="login-form-label">Password</label>
                        <div class="login-input-group">
                            <i class="bi bi-shield-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="login-input login-input-password"
                                placeholder="Min. 6 karakter" autocomplete="new-password" required>
                            <button type="button" class="password-toggle-btn" data-target="password"
                                aria-label="Show password"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="cpassword" class="login-form-label">Konfirmasi Password</label>
                        <div class="login-input-group">
                            <i class="bi bi-shield-check input-icon"></i>
                            <input type="password" id="cpassword" name="cpassword"
                                class="login-input login-input-password" placeholder="Ulangi password"
                                autocomplete="new-password" required>
                            <button type="button" class="password-toggle-btn" data-target="cpassword"
                                aria-label="Show password"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                </div>

                <button type="submit" name="signup" class="btn-login mt-3" id="btn-submit">
                    <span>Buat Akun</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <p class="login-footer-text mt-4">
                Sudah punya akun?
                <a href="login-user.php" id="link-login">Masuk</a>
            </p>
        </div>
    </div>

    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
