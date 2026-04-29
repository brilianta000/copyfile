<footer class="footer">
<?php 
// Detect from where this file is being included
$trace = debug_backtrace();
$caller_file = isset($trace[0]['file']) ? $trace[0]['file'] : __FILE__;
$caller_dir = dirname($caller_file);

// Get the last directory name in the path
$parts = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $caller_dir));
$caller_dir_name = end($parts);

// If included from 'user' folder directly
$base = ($caller_dir_name === 'user') ? '' : '../';
?>
  <div class="footer-container">
    <div class="footer-section identity">
      <div class="footer-logo">
        <img src="<?php echo $base; ?>gambar/logo-polije.png" alt="Logo Polije">
        <span class="brand-text">PERPUSTAKAAN POLIJE</span>
      </div>
      <div class="contact-info">
        <p>Jl. Mastrip PO BOX 164 Jember</p>
        <div class="contact-item">
          <img src="<?php echo $base; ?>gambar/email.png" alt="Email Icon">
          <span>Email: perpus@polije.ac.id</span>
        </div>
        <div class="contact-item">
          <img src="<?php echo $base; ?>gambar/telpon.png" alt="Phone Icon">
          <span>Telp: (0331) 333532</span>
        </div>
      </div>
    </div>

    <div class="footer-section links">
      <h3 class="footer-title">Menu Cepat</h3>
      <ul>
       <li><a href="<?php echo $base; ?>beranda.php">Beranda</a></li>
        <li><a href="<?php echo $base; ?>profil.php"> Profil</a></li>
         <li><a href="<?php echo $base; ?>lokasi.php"> Lokasi & Jam Kerja</a></li>
        <li><a href="<?php echo $base; ?>tata tertib/tatatertib.php"> Tata Tertib</a></li>
         <li><a href="<?php echo $base; ?>tata cara/tatacara.php"> Tata Cara</a></li>
        <li><a href="<?php echo $base; ?>koleksi/koleksi.php"> Koleksi</a></li>
      </ul>
    </div>

    <div class="footer-section social">
      <h3 class="footer-title">Media Sosial</h3>
      <div class="social-container">
        <li><a href="#" class="social-link"><img src="<?php echo $base; ?>gambar/ig.png"> Instagram</a>
        <li><a href="#" class="social-link"><img src="<?php echo $base; ?>gambar/fb.png"> Facebook</a>
        <li><a href="#" class="social-link"><img src="<?php echo $base; ?>gambar/mail.png"> Email</a>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© 2026 Perpustakaan POLIJE. All rights reserved.</p>
  </div>
</footer>