<?php 
require_once '../config/database.php';

// Handle AJAX Request for Data
if (isset($_GET['action'])) {
    
    // 1. Fetch Semesters based on Prodi
    if ($_GET['action'] == 'get_semesters') {
        $prodi_id = $_GET['prodi_id'] ?? 0;
        $stmt = $conn->prepare("SELECT DISTINCT semester FROM jadwal_uas WHERE prodi_id = ? ORDER BY semester ASC");
        $stmt->bind_param("i", $prodi_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $sems = [];
        while($row = $res->fetch_assoc()) $sems[] = $row['semester'];
        header('Content-Type: application/json');
        echo json_encode($sems);
        exit;
    }

    // 2. Fetch Schedules
    if ($_GET['action'] == 'get_schedules') {
        $prodi_id = $_GET['prodi_id'] ?? '';
        $semester = $_GET['semester'] ?? '';
        $show_all = $_GET['show_all'] ?? 'false';
        
        $query = "SELECT * FROM jadwal_uas WHERE prodi_id = ?";
        $params = ["i", $prodi_id];
        
        if ($show_all === 'true') {
            // No semester filter (Show All for Prodi)
        } elseif (!empty($semester)) {
             $query .= " AND semester = ?";
             $params[0] .= "s";
             $params[] = $semester;
        }
        
        $query .= " ORDER BY semester ASC, waktu ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param(...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($schedules);
        exit;
    }
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prodi_id = $_POST['prodi_id'];
    $semester = $_POST['semester'];
    $nama = strtoupper($_POST['nama']); // Ensure uppercase
    $nim = $_POST['nim'];
    $jadwal_ids = $_POST['jadwal_ids'] ?? [];

    // Check if Student Exists (NIM)
    $check = $conn->prepare("SELECT id, nama FROM mahasiswa WHERE nim = ?");
    $check->bind_param("s", $nim);
    $check->execute();
    $result = $check->get_result();
    $existing_student = $result->fetch_assoc();
    $check->close();

    $student_id = null;

    if ($existing_student) {
        // If Name and NIM match, don't save/update, just print
        if (strtoupper($existing_student['nama']) === $nama) {
            $student_id = $existing_student['id'];
            // SKIP UPDATE
        } else {
            // NIM exists but Name differs
            $student_id = $existing_student['id'];
            $stmt = $conn->prepare("UPDATE mahasiswa SET prodi_id = ?, semester = ?, nama = ? WHERE id = ?");
            $stmt->bind_param("issi", $prodi_id, $semester, $nama, $student_id);
            if (!$stmt->execute()) {
                 die("Error updating data: " . $stmt->error);
            }
            $stmt->close();
        }
    } else {
        // Insert new student
        $stmt = $conn->prepare("INSERT INTO mahasiswa (prodi_id, semester, nama, nim) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $prodi_id, $semester, $nama, $nim);
        
        if ($stmt->execute()) {
            $student_id = $conn->insert_id;
        } else {
            die("Error inserting data: " . $stmt->error);
        }
        $stmt->close();
    }

    // Redirect logic based on Status
    if ($student_id) {
        // Handle Schedule Persistence
        if (!empty($jadwal_ids)) {
            // 1. Clear existing manual schedule for this student
            $conn->query("DELETE FROM mahasiswa_jadwal WHERE mahasiswa_id = $student_id");

            // 2. Insert new selections
            $stmt_insert = $conn->prepare("INSERT INTO mahasiswa_jadwal (mahasiswa_id, jadwal_id) VALUES (?, ?)");
            foreach ($jadwal_ids as $jid) {
                $stmt_insert->bind_param("ii", $student_id, $jid);
                $stmt_insert->execute();
            }
            $stmt_insert->close();
        } else {
             // If User unchecked everything, assume revert to Automatic
             $conn->query("DELETE FROM mahasiswa_jadwal WHERE mahasiswa_id = $student_id");
        }

        // Fetch current status
        $check_status = $conn->query("SELECT status_keuangan FROM mahasiswa WHERE id = $student_id")->fetch_assoc();
        $status = $check_status['status_keuangan'];

        if ($status == 'LUNAS' || $status == 'DISPENSASI') {
            header("Location: print_card.php?id=" . $student_id);
            exit;
        } else {
            $message = "
            <div class='bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4'>
                <strong>Data Berhasil Disimpan!</strong><br>
                Namun Anda belum dapat mencetak kartu karena status keuangan Anda: <strong>" . str_replace('_', ' ', $status) . "</strong>.<br>
                Silakan hubungi Bendahara untuk verifikasi pembayaran.
            </div>";
        }
    }
}

// Fetch Prodis for dropdown
try {
    $prodis = $conn->query("SELECT * FROM prodi");
    // Removed Initial Semester Load - Will use JS
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<?php include 'header.php'; ?>

<div class="max-w-xl mx-auto bg-white p-8 rounded-lg shadow-md mt-10">
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Cetak Kartu UAS Mandiri</h2>
        <p class="text-gray-600">Silakan lengkapi data diri Anda untuk mencetak kartu.</p>
    </div>
    
    <?= $message ?>
    
    <form action="" method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700 font-bold mb-2">Program Studi</label>
            <select name="prodi_id" id="prodi_id" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
                <option value="">-- Pilih Prodi --</option>
                <?php while($row = $prodis->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['nama_prodi'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Nama Lengkap</label>
            <input type="text" name="nama" required placeholder="Sesuai KTM" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500 uppercase">
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">NIM</label>
            <input type="text" name="nim" required placeholder="Nomor Induk Mahasiswa" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div>
            <label class="block text-gray-700 font-bold mb-2">Semester</label>
            <select name="semester" id="semester" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:border-blue-500">
                <option value="">-- Pilih Prodi Terlebih Dahulu --</option>
            </select>
        </div>
        
        <!-- Schedule Selection Area -->
        <div id="schedule-container" class="hidden">
            <div class="flex justify-between items-center mb-2">
                <label class="block text-gray-700 font-bold">Pilih Mata Kuliah</label>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="show-all-check" class="w-4 h-4">
                    <label for="show-all-check" class="text-sm text-blue-600 font-semibold cursor-pointer">Tampilkan Semua (Lintas Smt)</label>
                </div>
            </div>
            
            <div id="schedule-list" class="border border-gray-300 p-2 rounded max-h-60 overflow-y-auto space-y-2">
                <!-- Checkboxes loaded via AJAX -->
            </div>
            <p class="text-xs text-gray-500 mt-1">*Centang mata kuliah yang diambil jika jadwal tidak otomatis.</p>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded hover:bg-blue-700 transition flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
            </svg>
            Simpan & Cetak Kartu
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const prodiSelect = document.getElementById('prodi_id');
    const semesterSelect = document.getElementById('semester');
    const scheduleContainer = document.getElementById('schedule-container');
    const scheduleList = document.getElementById('schedule-list');
    const showAllCheck = document.getElementById('show-all-check');

    // 1. Fetch Semesters when Prodi Changes
    prodiSelect.addEventListener('change', function() {
        const prodiId = this.value;
        semesterSelect.innerHTML = '<option value="">-- Memuat Semester... --</option>';
        
        if (!prodiId) {
            semesterSelect.innerHTML = '<option value="">-- Pilih Prodi Terlebih Dahulu --</option>';
            scheduleContainer.classList.add('hidden');
            return;
        }

        fetch(`student_print.php?action=get_semesters&prodi_id=${prodiId}`)
            .then(res => res.json())
            .then(data => {
                semesterSelect.innerHTML = '<option value="">-- Pilih Semester --</option>';
                data.forEach(sem => {
                   const opt = document.createElement('option');
                   opt.value = sem;
                   opt.textContent = sem;
                   semesterSelect.appendChild(opt);
                });
                // Reset schedule list
                scheduleList.innerHTML = '';
                scheduleContainer.classList.add('hidden');
                showAllCheck.checked = false; // Reset checkbox
            });
    });

    // 2. Fetch Schedules
    function fetchSchedules() {
        const prodiId = prodiSelect.value;
        const semester = semesterSelect.value;
        const showAll = showAllCheck.checked;

        if (!prodiId) {
            scheduleContainer.classList.add('hidden');
            return;
        }

        // Must have semester OR Show All checked
        if (!semester && !showAll) {
             scheduleContainer.classList.add('hidden');
             return;
        }

        // URL to fetch schedules
        let url = `student_print.php?action=get_schedules&prodi_id=${prodiId}&show_all=${showAll}`;
        if (semester && !showAll) {
            url += `&semester=${encodeURIComponent(semester)}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                scheduleList.innerHTML = '';
                if (data.length > 0) {
                    scheduleContainer.classList.remove('hidden');
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'flex items-start gap-2 border-b border-gray-100 pb-1 last:border-0';
                        div.innerHTML = `
                            <input type="checkbox" name="jadwal_ids[]" value="${item.id}" id="j_${item.id}" class="mt-1">
                            <label for="j_${item.id}" class="text-sm cursor-pointer w-full">
                                <span class="font-bold block text-gray-800">${item.kode_matkul} - ${item.nama_matkul}</span>
                                <span class="text-xs text-blue-600 font-semibold bg-blue-50 px-1 rounded">${item.semester}</span>
                                <span class="text-xs text-gray-500"> | ${item.waktu}</span>
                            </label>
                        `;
                        scheduleList.appendChild(div);
                    });
                } else {
                    scheduleList.innerHTML = '<p class="text-sm text-gray-500">Tidak ada jadwal ditemukan.</p>';
                    scheduleContainer.classList.remove('hidden');
                }
            })
            .catch(err => console.error(err));
    }

    semesterSelect.addEventListener('change', fetchSchedules);
    showAllCheck.addEventListener('change', fetchSchedules);
});
</script>

<?php include 'footer.php'; ?>
