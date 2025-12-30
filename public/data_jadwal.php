<?php 
require_once 'auth_check.php';
checkAdminAuth();
require_once '../config/database.php';

// Params Filter
$filter_prodi = $_GET['prodi'] ?? '';
$filter_semester = $_GET['semester'] ?? '';

// Build Query
$sql = "SELECT j.*, p.nama_prodi FROM jadwal_uas j 
        JOIN prodi p ON j.prodi_id = p.id 
        WHERE 1=1";

$types = "";
$params = [];

if ($filter_prodi) {
    // Check if ID or Code
    if (is_numeric($filter_prodi)) {
        $sql .= " AND j.prodi_id = ?";
        $types .= "i";
        $params[] = $filter_prodi;
    } 
}

if ($filter_semester) {
    $sql .= " AND j.semester = ?";
    $types .= "i";
    $params[] = $filter_semester;
}

$sql .= " ORDER BY p.nama_prodi ASC, j.semester ASC, j.waktu ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch Prodis for Filter
$prodis = $conn->query("SELECT * FROM prodi");
?>

<?php include 'header.php'; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Data Jadwal UAS</h2>
        
        <form method="GET" class="flex gap-2 items-center">
            <select name="prodi" class="border p-2 rounded">
                <option value="">Semua Prodi</option>
                <?php while($p = $prodis->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>" <?= $filter_prodi == $p['id'] ? 'selected' : '' ?>>
                        <?= $p['nama_prodi'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <select name="semester" class="border p-2 rounded">
                <option value="">Semua Semester</option>
                <?php for($i=1; $i<=8; $i++): ?>
                    <option value="<?= $i ?>" <?= $filter_semester == $i ? 'selected' : '' ?>>
                        Semester <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>
            
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
            <?php if ($filter_prodi): ?>
                <a href="export_jadwal_html.php?prodi=<?= $filter_prodi ?>" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2.5 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm7 0a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm-9-9h.01"></path></svg>
                    Export Matrix
                </a>
            <?php endif; ?>
            <a href="data_jadwal.php" class="text-gray-500 hover:text-gray-700 ml-2">Reset</a>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 text-left text-gray-600">
                    <th class="p-3 border-b">No</th>
                    <th class="p-3 border-b">Hari/Tanggal</th>
                    <th class="p-3 border-b">Jam</th>
                    <th class="p-3 border-b">Kode MK</th>
                    <th class="p-3 border-b">Mata Kuliah</th>
                    <th class="p-3 border-b">SKS</th>
                    <th class="p-3 border-b">Prodi</th>
                    <th class="p-3 border-b">Sem</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 border-b">
                        <td class="p-3"><?= $no++ ?></td>
                        <td class="p-3 font-medium">
                            <?php 
                                $date = new DateTime($row['waktu']);
                                $days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                                echo $days[$date->format('w')] . ', ' . $date->format('d/m/Y');
                            ?>
                        </td>
                        <td class="p-3 text-blue-600 font-bold">
                            <?= date('H:i', strtotime($row['waktu'])) ?>
                        </td>
                        <td class="p-3 text-sm text-gray-500"><?= htmlspecialchars($row['kode_matkul']) ?></td>
                        <td class="p-3 font-bold text-gray-700"><?= htmlspecialchars($row['nama_matkul']) ?></td>
                        <td class="p-3"><?= $row['sks'] ?></td>
                        <td class="p-3 text-sm">
                            <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-xs">
                                <?= $row['nama_prodi'] ?>
                            </span>
                        </td>
                        <td class="p-3 text-center"><?= $row['semester'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-400">Belum ada data jadwal. Silahkan import data.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
