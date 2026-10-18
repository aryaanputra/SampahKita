<?php require_once "controllerUserData.php"; ?>

<?php
if (($_SESSION['info'] ?? false) == false) {
    header('Location: login-user.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Success - Kali Laya Berseri</title>

    <meta name="description"
          content="Your Kali Laya Berseri account password has been updated">

    <!-- Favicon -->
    <link rel="icon"
          type="image/png"
          href="assets/images/favicon.ico">

    <!-- Local Bootstrap 5 -->
    <link rel="stylesheet"
          href="assets/libs/bootstrap/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="assets/libs/bootstrap-icons/bootstrap-icons.css">

    <!-- Original Spark Admin CSS -->
    <link rel="stylesheet"
          href="assets/css/main.css">
</head>

<body>

    <div class="login-wrapper">

        <!-- Background Shapes -->
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <!-- Success Card -->
        <div class="login-card">

            <!-- Brand Identity -->
            <a href="login-user.php"
               class="login-brand text-decoration-none">

                <i class="bi bi-recycle"></i>
                <span>Kali Laya Berseri</span>

            </a>

            <!-- Success Icon -->
            <div class="text-center mb-4">
                <i class="bi bi-check-circle-fill text-success"
                   style="font-size: 64px;"
                   aria-hidden="true"></i>
            </div>

            <!-- Page Subtitle -->
            <p class="login-subtitle">
                Your account recovery process has been completed.
            </p>

            <!-- PHP Success Message -->
            <?php if (isset($_SESSION['info'])) { ?>

                <div class="alert alert-success text-center"
                     role="status">

                    <?php
                    echo htmlspecialchars(
                        $_SESSION['info'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                </div>

            <?php } ?>

            <!-- Login Now Form -->
            <form action="login-user.php" method="POST">

                <button
                    type="submit"
                    name="login-now"
                    class="btn-login"
                    id="btn-login-now">

                    <span>Login Now</span>

                    <i class="bi bi-arrow-right"></i>

                </button>

            </form>

            <!-- Footer -->
            <p class="login-footer-text mt-4">
                Return to Kali Laya Berseri and sign in with your new password.
            </p>

        </div>

    </div>

    <!-- Local Bootstrap -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>