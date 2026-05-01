<?php

require_once __DIR__ . '/../../../controllers/BukuController.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ($_POST['action'] ?? '') !== 'edit_buku') {
    header('Location: ../../index.php?menu=databuku');
    exit;
}

(new BukuController())->update($_POST);
