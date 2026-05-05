<?php

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

if (($_SESSION['level'] ?? '') !== 'admin' || !isset($_SESSION['id_user'])) {
    header('Location: ../../../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../admin/');
    exit;
}

require_once __DIR__ . '/../../../controllers/ReservasiController.php';

ReservasiController::batalkan($_POST);
