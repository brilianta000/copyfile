<?php
session_start();
include '../config.php'; // Pastikan file koneksi database sudah ada

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    // Mencari user berdasarkan email
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);

        // Verifikasi password (menggunakan hash agar aman)
        if (password_verify($password, $data['password'])) {
            
            // Simpan identitas ke Session
            $_SESSION['id_user'] = $data['id'];
            $_SESSION['nama']    = $data['nama_lengkap'];
            $_SESSION['level']   = $data['level']; // Penting untuk membedakan admin/user

            // Alihkan halaman berdasarkan level
            if ($data['level'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../user/beranda.php");
            }
            exit();

        } else {
            echo "<script>alert('Password salah!'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); window.location='login.php';</script>";
    }
} else {
    header("Location: login.php");
}
?>