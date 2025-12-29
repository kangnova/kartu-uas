<?php 
require_once 'auth_check.php';
checkAdminAuth();

require_once '../config/database.php';

// Filter handling
$filter_prodi = $_GET['prodi'] ?? '';

// Build Query
$sql = "SELECT m.*, p.nama_prodi, p.kode_prodi 
        FROM mahasiswa m 
        JOIN prodi p ON m.prodi_id = p.id ";

if ($filter_prodi == 'PAI') {
    $sql .= "WHERE p.kode_prodi = 'PAI' ";
} elseif ($filter_prodi == 'PIAUD') {
    $sql .= "WHERE p.kode_prodi = 'PIAUD' ";
}

$sql .= "ORDER BY m.nama ASC";

$result = $conn->query($sql);
?>

<?php include 'header.php'; ?>

<div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Data Mahasiswa Siap Cetak</h2>
        
        <!-- Filter Controls -->
        <div class="flex gap-2">
            <a href="cetak_kartu.php" 
               class="px-4 py-2 rounded-lg font-semibold transition <?= $filter_prodi == '' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
               Semua
            </a>
            <a href="cetak_kartu.php?prodi=PAI" 
               class="px-4 py-2 rounded-lg font-semibold transition <?= $filter_prodi == 'PAI' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
               PAI
            </a>
            <a href="cetak_kartu.php?prodi=PIAUD" 
               class="px-4 py-2 rounded-lg font-semibold transition <?= $filter_prodi == 'PIAUD' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
               PIAUD
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border p-3 text-center w-16">No</th>
                    <th class="border p-3 text-left">Nama</th>
                    <th class="border p-3 text-left w-32">NIM</th>
                    <th class="border p-3 text-center w-24">Semester</th>
                    <th class="border p-3 text-center w-32">Prodi</th>
                    <th class="border p-3 text-center w-40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="border p-3 text-center"><?= $no++ ?></td>
                        <td class="border p-3 font-semibold text-gray-700 uppercase"><?= $row['nama'] ?></td>
                        <td class="border p-3"><?= $row['nim'] ?></td>
                        <td class="border p-3 text-center"><?= $row['semester'] ?></td>
                        <td class="border p-3 text-center">
                            <span class="px-2 py-1 rounded text-xs font-bold <?= $row['kode_prodi'] == 'PAI' ? 'bg-green-100 text-green-800' : 'bg-pink-100 text-pink-800' ?>">
                                <?= $row['kode_prodi'] ?>
                            </span>
                        </td>
                        <td class="border p-3 text-center">
                            <a href="print_card.php?id=<?= $row['id'] ?>" target="_blank" 
                               class="inline-block bg-purple-600 text-white font-bold py-1 px-3 rounded hover:bg-purple-700 transition text-sm flex items-center justify-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                </svg>
                                Cetak
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="border p-8 text-center text-gray-500">
                            Belum ada data mahasiswa. Silakan <a href="input_mahasiswa.php" class="text-blue-600 hover:underline">input data</a> terlebih dahulu.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
