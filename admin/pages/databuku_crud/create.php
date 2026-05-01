<?php

require_once __DIR__ . '/../../../controllers/BukuController.php';

(new BukuController())->store($_POST, $_FILES);
