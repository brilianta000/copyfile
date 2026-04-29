<?php

$page = $_GET['page'] ?? 'dashboard';

if ($page == 'databuku') {
    include 'databuku.php';
} elseif ($page == 'tambah') {
    include 'tambah.php';
} elseif ($page == 'akun') {
    include 'akun.php';
} else {
    include 'dashboard.php';
}

?>