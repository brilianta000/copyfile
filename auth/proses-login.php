<?php
session_start();
require_once '../config/database.php';

if (!isset($_POST['login'])) {
    header('Location: login.php');
    exit;
}

$db    = new Database();
$conn  = $db->getConnection();

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

// Cek tabel admin terlebih dahulu
$stmt = $conn->prepare('SELECT id_admin AS id, nama_admin AS nama, password, "admin" AS level FROM admin WHERE email_admin = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Fallback: cek tabel anggota (level user)
if (!$data) {
    $stmt2 = $conn->prepare('SELECT id_anggota AS id, nama_anggota AS nama, NULL AS password, "user" AS level FROM anggota WHERE email_anggota = ? AND status_anggota = "active" LIMIT 1');
    $stmt2->bind_param('s', $email);
    $stmt2->execute();
    $data = $stmt2->get_result()->fetch_assoc();
}

if (!$data) {
    echo "<script>alert('Email tidak ditemukan!'); window.location='login.php';</script>";
    exit;
}

// Admin pakai password hash, anggota belum punya password di skema DB ini
if ($data['level'] === 'admin') {
    if (!password_verify($pass, (string) $data['password'])) {
        echo "<script>alert('Password salah!'); window.location='login.php';</script>";
        exit;
    }
}

$_SESSION['id_user'] = $data['id'];
$_SESSION['nama']    = $data['nama'];
$_SESSION['level']   = $data['level'];

if ($data['level'] === 'admin') {
    header('Location: ../admin/');
} else {
    header('Location: ../user/beranda.php');
}
exit;
