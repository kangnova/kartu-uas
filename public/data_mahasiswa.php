<?php 
require_once 'auth_check.php';
checkAdminAuth();
require_once '../config/database.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id_delete = $_GET['delete'];
    $stmt_del = $conn->prepare("DELETE FROM mahasiswa WHERE id = ?");
    $stmt_del->bind_param("i", $id_delete);
    if ($stmt_del->execute()) {
        header("Location: data_mahasiswa.php?msg=deleted");
        exit;
    }
}

// Params Filter
$filter_prodi = $_GET['prodi'] ?? '';
$filter_semester = $_GET['semester'] ?? '';
$search_query = $_GET['q'] ?? '';

// Build Query
$sql = "SELECT m.*, p.nama_prodi FROM mahasiswa m 
        JOIN prodi p ON m.prodi_id = p.id 
        WHERE 1=1";

$types = "";
$params = [];

if ($filter_prodi) {
    if (is_numeric($filter_prodi)) {
        $sql .= " AND m.prodi_id = ?";
        $types .= "i";
        $params[] = $filter_prodi;
    } 
}

if ($filter_semester) {
    $sql .= " AND m.semester = ?";
    $types .= "i";
    $params[] = $filter_semester;
}

if ($search_query) {
    $sql .= " AND (m.nama LIKE ? OR m.nim LIKE ?)";
    $types .= "ss";
    $like_query = "%" . $search_query . "%";
    $params[] = $like_query;
    $params[] = $like_query;
}

$sql .= " ORDER BY p.nama_prodi ASC, m.semester ASC, m.nama ASC";

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
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Data Mahasiswa</h2>
        
        <form method="GET" class="flex flex-wrap gap-2 items-center">
            <input type="text" name="q" value="<?= htmlspecialchars($search_query) ?>" placeholder="Cari Nama / NIM..." class="border p-2 rounded w-40 md:w-auto">
            
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
            <a href="data_mahasiswa.php" class="text-gray-500 hover:text-gray-700 ml-2">Reset</a>
        </form>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            Data Mahasiswa berhasil dihapus.
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-100 text-left text-gray-600">
                    <th class="p-3 border-b">No</th>
                    <th class="p-3 border-b">NIM</th>
                    <th class="p-3 border-b">Nama Mahasiswa</th>
                    <th class="p-3 border-b">Prodi</th>
                    <th class="p-3 border-b">Sem</th>
                    <th class="p-3 border-b">Status Keuangan</th>
                    <th class="p-3 border-b">Catatan</th>
                    <th class="p-3 border-b text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 border-b">
                        <td class="p-3"><?= $no++ ?></td>
                        <td class="p-3 font-semibold text-gray-700"><?= htmlspecialchars($row['nim']) ?></td>
                        <td class="p-3 font-medium text-gray-900"><?= htmlspecialchars($row['nama']) ?></td>
                        <td class="p-3 text-gray-600"><?= htmlspecialchars($row['nama_prodi']) ?></td>
                        <td class="p-3"><?= $row['semester'] ?></td>
                        <td class="p-3">
                            <?php 
                                $status_color = match($row['status_keuangan']) {
                                    'LUNAS' => 'bg-green-100 text-green-800',
                                    'DISPENSASI' => 'bg-orange-100 text-orange-800',
                                    default => 'bg-red-100 text-red-800'
                                };
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $status_color ?>">
                                <?= $row['status_keuangan'] ?>
                            </span>
                        </td>
                        <td class="p-3 text-gray-500 italic"><?= htmlspecialchars($row['catatan_keuangan'] ?? '-') ?></td>
                        <td class="p-3 text-center space-x-2">
                            <!-- Edit Button could be added here if edit page exists -->
                             <a href="data_mahasiswa.php?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus data ini?')" 
                                class="text-red-600 hover:text-red-900" title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                             </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-400">
                            Data tidak ditemukan. Silahkan <a href="input_mahasiswa.php" class="text-blue-500 underline">Input</a> atau <a href="input_mahasiswa.php" class="text-blue-500 underline">Import</a> data.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
