<?php 
require_once 'auth_check.php';
checkAdminAuth();

require_once '../config/database.php';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prodi_id = $_POST['prodi_id'];
    $semester = $_POST['semester'];
    $kode_matkul = $_POST['kode_matkul'];
    $nama_matkul = $_POST['nama_matkul'];
    $sks = $_POST['sks'];
    $waktu = $_POST['waktu'];

    $stmt = $conn->prepare("INSERT INTO jadwal_uas (prodi_id, semester, kode_matkul, nama_matkul, sks, waktu) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $prodi_id, $semester, $kode_matkul, $nama_matkul, $sks, $waktu);

    if ($stmt->execute()) {
        $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>Data berhasil disimpan!</div>";
    } else {
        $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Fetch Prodis
try {
    $prodis = $conn->query("SELECT * FROM prodi");
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1146) { // Table doesn't exist
        die("<div class='p-4 bg-red-100 text-red-700 border border-red-400 rounded'>
            <strong>System Error:</strong> Tabel Database belum dibuat.<br>
            Silahkan jalankan <a href='install.php' class='underline font-bold'>Installation Script</a> terlebih dahulu.
        </div>");
    } else {
        throw $e;
    }
}
?>

<?php include 'header.php'; ?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Input Jadwal UAS</h2>
    <?= $message ?>
    
    <form action="" method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700 font-bold mb-2">Program Studi</label>
            <select name="prodi_id" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
                <option value="">Pilih Prodi</option>
                <?php while($row = $prodis->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama_prodi'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Semester</label>
            <input type="number" name="semester" min="1" max="14" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 font-bold mb-2">Kode Matkul</label>
                <input type="text" name="kode_matkul" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">SKS</label>
                <input type="number" name="sks" min="1" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Nama Matakuliah</label>
            <input type="text" name="nama_matkul" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Waktu Ujian</label>
            <input type="datetime-local" name="waktu" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition">Simpan Jadwal</button>
    </form>
</div>

<?php include 'footer.php'; ?>
