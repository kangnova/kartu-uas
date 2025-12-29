<?php 
require_once '../config/database.php';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prodi_id = $_POST['prodi_id'];
    $semester = $_POST['semester'];
    $nama = $_POST['nama'];
    $nim = $_POST['nim'];

    // Check for duplicate NIM
    $check = $conn->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
    $check->bind_param("s", $nim);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "<div class='bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4'>NIM sudah terdaftar!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO mahasiswa (prodi_id, semester, nama, nim) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $prodi_id, $semester, $nama, $nim);

        if ($stmt->execute()) {
            $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>Data Mahasiswa berhasil disimpan!</div>";
        } else {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
    $check->close();
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
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Input Data Mahasiswa</h2>
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

        <div>
            <label class="block text-gray-700 font-bold mb-2">Nama Lengkap</label>
            <input type="text" name="nama" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 uppercase">
            <p class="text-xs text-gray-500 mt-1">Gunakan huruf kapital (e.g. ABDULLAH FARHAN)</p>
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">NIM</label>
            <input type="text" name="nim" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
        </div>

        <button type="submit" class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded hover:bg-green-700 transition">Simpan Mahasiswa</button>
    </form>
</div>

<?php include 'footer.php'; ?>
