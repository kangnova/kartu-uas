<?php 
require_once '../config/database.php';
$search_results = null;

if (isset($_GET['nim'])) {
    $nim = $_GET['nim'];
    $stmt = $conn->prepare("
        SELECT m.*, p.nama_prodi 
        FROM mahasiswa m 
        JOIN prodi p ON m.prodi_id = p.id 
        WHERE m.nim = ?
    ");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $search_results = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<?php include 'header.php'; ?>

<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Cetak Kartu UAS</h2>
    
    <form action="" method="GET" class="flex gap-4 mb-8">
        <input type="text" name="nim" placeholder="Masukkan NIM Mahasiswa..." class="flex-1 border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" value="<?= isset($_GET['nim']) ? $_GET['nim'] : '' ?>" required>
        <button type="submit" class="bg-purple-600 text-white font-bold py-2 px-6 rounded hover:bg-purple-700 transition">Cari</button>
    </form>

    <?php if (isset($_GET['nim']) && !$search_results): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            Data mahasiswa dengan NIM <strong><?= htmlspecialchars($_GET['nim']) ?></strong> tidak ditemukan.
        </div>
    <?php endif; ?>

    <?php if ($search_results): ?>
        <div class="border rounded-lg p-6 bg-gray-50">
            <h3 class="text-xl font-bold mb-4">Data Ditemukan</h3>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm text-gray-600">Nama</label>
                    <div class="font-bold text-lg"><?= $search_results['nama'] ?></div>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">NIM</label>
                    <div class="font-bold text-lg"><?= $search_results['nim'] ?></div>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Prodi</label>
                    <div class="font-bold"><?= $search_results['nama_prodi'] ?></div>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Semester</label>
                    <div class="font-bold"><?= $search_results['semester'] ?></div>
                </div>
            </div>
            
            <a href="print_card.php?id=<?= $search_results['id'] ?>" target="_blank" class="inline-block bg-blue-600 text-white font-bold py-3 px-8 rounded hover:bg-blue-700 transition shadow-lg">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    CETAK KARTU UAS
                </span>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
