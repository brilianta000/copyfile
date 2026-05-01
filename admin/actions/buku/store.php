<?php

require_once __DIR__ . '/../../../controllers/BukuController.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ../../index.php?menu=tambahbuku');
    exit;
}

(new BukuController())->store($_POST, $_FILES);
