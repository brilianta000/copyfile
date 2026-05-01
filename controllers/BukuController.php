<?php

require_once __DIR__ . '/../models/Buku.php';

class BukuController
{
    public static function dataFile(): string
    {
        return __DIR__ . '/../admin/pages/data_buku.json';
    }
}
