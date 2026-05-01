<?php

require_once __DIR__ . '/../../../controllers/BukuController.php';

(new BukuController())->update($_POST);
