<?php

require_once __DIR__ . '/../../../controllers/BukuController.php';

extract((new BukuController())->index(), EXTR_SKIP);
