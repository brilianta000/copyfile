<?php
session_start();
include '../config.php';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM admin WHERE email_admin = '$email' LIMIT 1");
    
    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);

        if (password_verify($password, $data['password'])) {
            $_SESSION['id_admin'] = $data['id_admin'];
            $_SESSION['id_user']  = $data['id_admin'];
            $_SESSION['nama']     = $data['nama_admin'];
            $_SESSION['jabatan']  = $data['jabatan_admin'] ?? 'Admin Perpustakaan';
            $_SESSION['level']    = 'admin';

            $adminId = (int) $data['id_admin'];
            mysqli_query($koneksi, "UPDATE admin SET last_login_at = NOW() WHERE id_admin = $adminId");
            header("Location: ../admin/index.php?menu=dashboard");
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
