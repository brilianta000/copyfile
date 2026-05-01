<?php

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Guard: hanya admin yang sudah login dan request via POST
if (($_SESSION['level'] ?? '') !== 'admin' || !isset($_SESSION['id_user'])) {
    header('Location: ../../../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../admin/');
    exit;
}

require_once __DIR__ . '/../../../controllers/PeminjamanController.php';

PeminjamanController::store($_POST);
