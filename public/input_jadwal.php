<?php 
require_once 'auth_check.php';
checkAdminAuth();

require_once '../config/database.php';

// Fetch Prodis Map for Import
$prodiMap = [];
try {
    $pResult = $conn->query("SELECT id, kode_prodi FROM prodi");
    if ($pResult->num_rows == 0) {
        $message = "<div class='bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4'>
            <strong>Peringatan:</strong> Tabel Master Prodi kosong. Silahkan input data Prodi terlebih dahulu atau jalankan script instalasi ulang jika ini adalah instalasi baru.
        </div>";
    }
    while($pRow = $pResult->fetch_assoc()) {
        $prodiMap[strtoupper($pRow['kode_prodi'])] = $pRow['id'];
    }
} catch (Exception $e) {
    // Ignore if table not ready, handled below
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['import_csv'])) {
        // Handle CSV Import
        if (isset($_FILES['file_jadwal']) && $_FILES['file_jadwal']['error'] == 0) {
            $file = $_FILES['file_jadwal']['tmp_name'];
            $ext = pathinfo($_FILES['file_jadwal']['name'], PATHINFO_EXTENSION);
            
            if (strtolower($ext) !== 'csv') {
                $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Hanya file CSV yang diperbolehkan.</div>";
            } else {
                $handle = fopen($file, "r");
                if ($handle !== FALSE) {
                    // Auto-detect delimiter
                    $firstLine = fgets($handle);
                    rewind($handle);
                    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

                    $row = 0;
                    $success = 0;
                    $failed = 0;
                    $errors = [];
                    
                    while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                        $row++;
                        if ($row == 1) continue; // Skip header

                        // Clean BOM from first column if present (common in Excel exports)
                        if (isset($data[0])) {
                            $data[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0]);
                        }

                        // Map columns: 0:KodeProdi, 1:Semester, 2:KodeMatkul, 3:NamaMatkul, 4:SKS, 5:Waktu
                        if (count($data) < 6) {
                             $failed++;
                             $errors[] = "Baris $row: Format kolom tidak sesuai (mungkin masalah delimiter).";
                             continue;
                        }

                        $kodeProdi = strtoupper(trim($data[0]));
                        $semesterRaw = trim($data[1]);
                        $semester = $semesterRaw; // Keep as string for '7 Non Reg'
                        $kodeMatkul = trim($data[2]);
                        $namaMatkul = trim($data[3]);
                        $sks = (int)$data[4];
                        $waktuRaw = trim($data[5]);
                        $pengawas = isset($data[6]) ? trim($data[6]) : ''; // Column 7: Pengawas

                        // Handle Date Format (d/m/Y H:i usually from Excel ID region)
                        $waktuObj = DateTime::createFromFormat('d/m/Y H:i', $waktuRaw);
                        if (!$waktuObj) {
                            // Try fallback to standard Y-m-d H:i
                            $waktuObj = DateTime::createFromFormat('Y-m-d H:i', $waktuRaw);
                        }
                        
                        if (!$waktuObj) {
                             // Try strtotime as last resort
                             $ts = strtotime($waktuRaw);
                             if ($ts) {
                                $waktu = date('Y-m-d H:i:s', $ts);
                             } else {
                                $failed++;
                                $errors[] = "Baris $row: Format waktu '$waktuRaw' tidak dikenali (Gunakan d/m/Y H:i).";
                                continue;
                             }
                        } else {
                            $waktu = $waktuObj->format('Y-m-d H:i:s');
                        }

                        if (!isset($prodiMap[$kodeProdi])) {
                            $failed++;
                            $errors[] = "Baris $row: Kode Prodi '$kodeProdi' tidak ditemukan.";
                            continue;
                        }
                        $prodiId = $prodiMap[$kodeProdi];

                        $stmt = $conn->prepare("INSERT INTO jadwal_uas (prodi_id, semester, kode_matkul, nama_matkul, sks, waktu, pengawas) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("issssss", $prodiId, $semester, $kodeMatkul, $namaMatkul, $sks, $waktu, $pengawas);
                        
                        if ($stmt->execute()) {
                            $success++;
                        } else {
                            $failed++;
                            $errors[] = "Baris $row: Error DB - " . $stmt->error;
                        }
                        $stmt->close();
                    }
                    fclose($handle);
                    $message = "<div class='bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4'>
                                <strong>Import Selesai!</strong><br>
                                Data Berhasil: $success<br>
                                Data Gagal: $failed";
                    if (!empty($errors)) {
                        $message .= "<br><div class='mt-2 max-h-32 overflow-y-auto text-sm'><ul class='list-disc pl-4'>";
                        foreach($errors as $err) $message .= "<li>$err</li>";
                        $message .= "</ul></div>";
                    }
                    $message .= "</div>";
                }
            }
        } else {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Silahkan pilih file CSV terlebih dahulu.</div>";
        }
    } elseif (isset($_POST['save_manual'])) {
        // Handle Manual Input
        $prodi_id = $_POST['prodi_id'];
        $semester = $_POST['semester'];
        $kode_matkul = $_POST['kode_matkul'];
        $nama_matkul = $_POST['nama_matkul'];
        $sks = $_POST['sks'];
        $waktu = $_POST['waktu'];
        $pengawas = $_POST['pengawas'];

        $stmt = $conn->prepare("INSERT INTO jadwal_uas (prodi_id, semester, kode_matkul, nama_matkul, sks, waktu, pengawas) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $prodi_id, $semester, $kode_matkul, $nama_matkul, $sks, $waktu, $pengawas);

        if ($stmt->execute()) {
            $message = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>Data berhasil disimpan!</div>";
        } else {
            $message = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
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

    <!-- Import Section -->
    <div class="mb-8 p-6 bg-gray-50 border border-gray-200 rounded-lg">
        <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Import Jadwal dari CSV</h3>
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-grow">
                <p class="text-sm text-gray-600 mb-2">Gunakan template berikut untuk data yang valid:</p>
                <a href="download_template_jadwal.php" class="inline-flex items-center px-4 py-2 border border-blue-500 text-blue-600 rounded hover:bg-blue-50 transition text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Template CSV
                </a>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data" class="flex-grow w-full">
                <label class="block text-gray-700 font-bold mb-2">Upload File CSV</label>
                <div class="flex gap-2">
                    <input type="file" name="file_jadwal" accept=".csv" required class="flex-grow border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 bg-white">
                    <button type="submit" name="import_csv" class="bg-green-600 text-white font-bold py-2 px-4 rounded hover:bg-green-700 transition">Import</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Divider -->
    <div class="relative flex py-5 items-center">
        <div class="flex-grow border-t border-gray-300"></div>
        <span class="flex-shrink-0 mx-4 text-gray-400">Atau Input Manual</span>
        <div class="flex-grow border-t border-gray-300"></div>
    </div>
    
    <h3 class="text-lg font-bold text-gray-700 mb-4">Form Input Manual</h3>
    
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
            <input type="text" name="semester" placeholder="Contoh: 1, 3, 7 Non Reg" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
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
            <label class="block text-gray-700 font-bold mb-2">Pengawas</label>
            <input type="text" name="pengawas" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500" placeholder="Nama Pengawas">
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Waktu Ujian</label>
            <input type="datetime-local" name="waktu" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
        </div>

        <button type="submit" name="save_manual" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition">Simpan Jadwal</button>
    </form>
</div>

<?php include 'footer.php'; ?>
