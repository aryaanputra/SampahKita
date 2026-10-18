    <!-- START: Footer -->
    <footer class="footer-custom">
      <div class="footer-left">
        <span class="footer-logo">
          <i class="bi bi-recycle"></i> Bank Sampah Kali Laya Berseri
        </span>
        <span class="footer-separator">|</span>
        <span class="footer-copy">&copy; <?= date('Y') ?> Kali Laya Berseri</span>
      </div>
      <div class="footer-right">
        <ul class="footer-links">
          <li><a href="<?= e($base ?? '') ?>index.php" class="footer-link">Dasbor</a></li>
          <li><span class="footer-link">Sistem <span class="status-dot"></span></span></li>
        </ul>
      </div>
    </footer>
    <!-- END: Footer -->

  </div>
  <!-- ============ END: Main ============ -->

  <script src="<?= e($base ?? '') ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= e($base ?? '') ?>assets/libs/apexcharts/apexcharts.min.js"></script>
  <script src="<?= e($base ?? '') ?>assets/libs/flatpickr/flatpickr.min.js"></script>
  <script src="<?= e($base ?? '') ?>assets/js/dashboard.js"></script>
  <script src="<?= e($base ?? '') ?>assets/js/app.js"></script>
</body>

</html>
