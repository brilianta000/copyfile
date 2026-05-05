<?php
include '../config.php';

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $pass  = $_POST['password'];
    $konfirmasi_pass = $_POST['konfirmasi_password'];

    // 1. Cek apakah password dan konfirmasi cocok
    if ($pass !== $konfirmasi_pass) {
        echo "<script>alert('Konfirmasi password tidak cocok!'); window.location='register.php';</script>";
        exit();
    }

    // 2. Cek apakah email sudah terdaftar
    $cek_email = mysqli_query($koneksi, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        echo "<script>alert('Email sudah terdaftar!'); window.location='register.php';</script>";
    } else {
        // 3. Enkripsi password 
        $password_hash = password_hash($pass, PASSWORD_DEFAULT);

        // 4. Simpan ke database (Level default: user)
        $simpan = mysqli_query($koneksi, "INSERT INTO users (nama_lengkap, email, password, level) 
                                          VALUES ('$nama', '$email', '$password_hash', 'user')");

        if ($simpan) {
            echo "<script>alert('Pendaftaran Berhasil! Silakan Login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Gagal mendaftar, coba lagi.'); window.location='register.php';</script>";
        }
    }
}
?>