<?php 
require_once 'auth_check.php';
checkAdminAuth();

require_once '../config/database.php';

// Handle form submission
$message = '';
// Handle CSV Import
if (isset($_POST['import_csv'])) {
    if (is_uploaded_file($_FILES['file_csv']['tmp_name'])) {
        $file = fopen($_FILES['file_csv']['tmp_name'], "r");
        $count = 0;
        $success = 0;
        $errors = 0;
        $error_msgs = [];

        // Detect Delimiter
        $firstLine = fgets($file);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($file);

        // Skip header
        fgetcsv($file, 0, $delimiter);

        // Get Prodi Map
        $prodi_map = [];
        $p_res = $conn->query("SELECT id, kode_prodi FROM prodi");
        while($p = $p_res->fetch_assoc()) {
            $prodi_map[trim($p['kode_prodi'])] = $p['id'];
        }

        while (($data = fgetcsv($file, 1000, $delimiter)) !== FALSE) {
            $count++;
            // Expected: NIM, NAMA, KODE_PRODI, SEMESTER, STATUS_KEUANGAN, CATATAN_KEUANGAN (Optional)
            
            // Skip empty rows
            if (empty(implode('', $data))) continue;

            $nim = trim($data[0] ?? '');
            $nama = strtoupper(trim($data[1] ?? ''));
            $kode_prodi = trim($data[2] ?? '');
            $semester = (int)($data[3] ?? 1);
            $status = strtoupper(trim($data[4] ?? 'BELUM_LUNAS'));
            $catatan = trim($data[5] ?? '');

            if (empty($nim) || empty($nama) || empty($kode_prodi)) {
                $errors++;
                $error_msgs[] = "Baris $count: Data tidak lengkap (NIM, Nama, atau Prodi kosong).";
                continue;
            }

            if (!isset($prodi_map[$kode_prodi])) {
                 $errors++;
                 $error_msgs[] = "Baris $count: Kode Prodi '$kode_prodi' tidak ditemukan.";
                 continue;
            }

            $prodi_id = $prodi_map[$kode_prodi];

            // Validasi Status ENUM
            $valid_status = ['LUNAS', 'DISPENSASI', 'BELUM_LUNAS'];
            if (!in_array($status, $valid_status)) $status = 'BELUM_LUNAS';

            // Insert or Update
            // Updated to utilize all columns shown in user's image, including catatan_keuangan
            $stmt = $conn->prepare("INSERT INTO mahasiswa (nim, nama, prodi_id, semester, status_keuangan, catatan_keuangan) VALUES (?, ?, ?, ?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE nama=VALUES(nama), prodi_id=VALUES(prodi_id), semester=VALUES(semester), status_keuangan=VALUES(status_keuangan), catatan_keuangan=VALUES(catatan_keuangan)");
            $stmt->bind_param("ssiiss", $nim, $nama, $prodi_id, $semester, $status, $catatan);
            
            if ($stmt->execute()) {
                $success++;
            } else {
                $errors++;
                $error_msgs[] = "Baris $count: Gagal simpan DB - " . $stmt->error;
            }
        }
        fclose($file);

        $msg_class = ($errors > 0) ? "yellow" : "green";
        $message = "<div class='bg-{$msg_class}-100 border border-{$msg_class}-400 text-{$msg_class}-700 px-4 py-3 rounded mb-4'>
                        <strong>Import Selesai!</strong><br>
                        Sukses: $success data.<br>
                        Gagal: $errors data.<br>";
        if (!empty($error_msgs) && $errors < 10) {
            $message .= "<ul class='list-disc pl-5 mt-2 text-sm'>";
            foreach($error_msgs as $err) $message .= "<li>$err</li>";
            $message .= "</ul>";
        }
        $message .= "</div>";
    }
}

// Handle Manual Form Submission
if (isset($_POST['submit_manual'])) {
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

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md mb-8">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Import Data Mahasiswa (CSV)</h2>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <p class="text-sm text-blue-700">
            Fitur ini memungkinkan Anda mengupload data mahasiswa dalam jumlah banyak sekaligus.<br>
            Silakan download template CSV terlebih dahulu.
        </p>
    </div>

    <div class="flex justify-between items-center mb-6">
        <a href="template_mahasiswa.csv" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/></svg>
            <span>Download Template CSV</span>
        </a>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4 border-t pt-4">
        <div>
            <label class="block text-gray-700 font-bold mb-2">Upload File CSV</label>
            <input type="file" name="file_csv" accept=".csv" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
        </div>
        <button type="submit" name="import_csv" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition">Import Data CSV</button>
    </form>
</div>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Input Manual Data Mahasiswa</h2>
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

        <button type="submit" name="submit_manual" class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded hover:bg-green-700 transition">Simpan Mahasiswa</button>
    </form>
</div>

<?php include 'footer.php'; ?>
