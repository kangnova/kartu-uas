<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_admin = $_SESSION['is_admin'] ?? false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kartu UAS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-800 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-xl font-bold">SI-UAS FAI</a>
            <div class="space-x-4 flex items-center">
                <a href="index.php" class="hover:text-blue-200">Home</a>
                <a href="student_print.php" class="hover:text-blue-200">Cetak Mandiri</a>
                
                <?php if ($is_admin): ?>
                    <a href="input_jadwal.php" class="hover:text-blue-200">Input Jadwal</a>
                    <a href="data_jadwal.php" class="hover:text-blue-200">Data Jadwal</a>
                    <a href="input_mahasiswa.php" class="hover:text-blue-200">Input Mahasiswa</a>
                    <a href="cetak_kartu.php" class="hover:text-blue-200">Cetak (Admin)</a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm transition">Logout</a>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'bendahara'): ?>
                    <a href="verifikasi_keuangan.php" class="hover:text-blue-200 font-bold">Verifikasi Keuangan</a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm transition">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="bg-white text-blue-800 px-3 py-1 rounded text-sm font-bold hover:bg-gray-100 transition">Login Admin/Bendahara</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container mx-auto p-6">
