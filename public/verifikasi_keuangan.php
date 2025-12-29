<?php 
require_once 'auth_check.php';
checkBendaharaAuth();

require_once '../config/database.php';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['student_id'];
    $status = $_POST['status_keuangan'];
    $catatan = $_POST['catatan_keuangan'];

    $stmt = $conn->prepare("UPDATE mahasiswa SET status_keuangan = ?, catatan_keuangan = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $catatan, $id);
    $stmt->execute();
    $stmt->close();
}

// Filter handling
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build Query
$sql = "SELECT m.*, p.nama_prodi 
        FROM mahasiswa m 
        JOIN prodi p ON m.prodi_id = p.id 
        WHERE 1=1 ";

if ($filter_status != '') {
    $sql .= "AND status_keuangan = '$filter_status' ";
}

if ($search != '') {
    $sql .= "AND (m.nama LIKE '%$search%' OR m.nim LIKE '%$search%') ";
}

$sql .= "ORDER BY m.nama ASC";

$result = $conn->query($sql);
?>

<?php include 'header.php'; ?>

<div class="max-w-7xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Verifikasi Keuangan Mahasiswa</h2>
        
        <form method="GET" class="flex gap-2 mt-4 md:mt-0">
            <input type="text" name="search" placeholder="Cari Nama/NIM..." value="<?= htmlspecialchars($search) ?>" class="border p-2 rounded">
            <select name="status" class="border p-2 rounded">
                <option value="">Semua Status</option>
                <option value="BELUM_LUNAS" <?= $filter_status == 'BELUM_LUNAS' ? 'selected' : '' ?>>Belum Lunas</option>
                <option value="LUNAS" <?= $filter_status == 'LUNAS' ? 'selected' : '' ?>>Lunas</option>
                <option value="DISPENSASI" <?= $filter_status == 'DISPENSASI' ? 'selected' : '' ?>>Dispensasi</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200 text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border p-2">No</th>
                    <th class="border p-2 text-left">Nama / NIM</th>
                    <th class="border p-2 text-center">Prodi</th>
                    <th class="border p-2 text-center">Semester</th>
                    <th class="border p-2 text-center">Status Saat Ini</th>
                    <th class="border p-2 text-center w-1/3">Update Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="border p-2 text-center"><?= $no++ ?></td>
                        <td class="border p-2">
                            <div class="font-bold"><?= $row['nama'] ?></div>
                            <div class="text-gray-500"><?= $row['nim'] ?></div>
                        </td>
                        <td class="border p-2 text-center"><?= $row['nama_prodi'] ?></td>
                        <td class="border p-2 text-center"><?= $row['semester'] ?></td>
                        <td class="border p-2 text-center">
                            <?php
                            $badge_class = 'bg-gray-100 text-gray-800';
                            if ($row['status_keuangan'] == 'LUNAS') $badge_class = 'bg-green-100 text-green-800';
                            if ($row['status_keuangan'] == 'DISPENSASI') $badge_class = 'bg-yellow-100 text-yellow-800';
                            if ($row['status_keuangan'] == 'BELUM_LUNAS') $badge_class = 'bg-red-100 text-red-800';
                            ?>
                            <span class="px-2 py-1 rounded text-xs font-bold <?= $badge_class ?>">
                                <?= str_replace('_', ' ', $row['status_keuangan']) ?>
                            </span>
                            <?php if($row['catatan_keuangan']): ?>
                                <div class="text-xs text-gray-500 mt-1 italic">"<?= $row['catatan_keuangan'] ?>"</div>
                            <?php endif; ?>
                        </td>
                        <td class="border p-2">
                            <form method="POST" class="flex gap-2 items-start">
                                <input type="hidden" name="student_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="update_status" value="1">
                                
                                <div class="flex-1">
                                    <select name="status_keuangan" class="w-full border p-1 rounded text-sm mb-1" required>
                                        <option value="BELUM_LUNAS" <?= $row['status_keuangan'] == 'BELUM_LUNAS' ? 'selected' : '' ?>>Belum Lunas</option>
                                        <option value="LUNAS" <?= $row['status_keuangan'] == 'LUNAS' ? 'selected' : '' ?>>Lunas</option>
                                        <option value="DISPENSASI" <?= $row['status_keuangan'] == 'DISPENSASI' ? 'selected' : '' ?>>Dispensasi</option>
                                    </select>
                                    <input type="text" name="catatan_keuangan" placeholder="Catatan (Opsional)" value="<?= $row['catatan_keuangan'] ?>" class="w-full border p-1 rounded text-xs">
                                </div>
                                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-xs mt-1 hover:bg-blue-700">Simpan</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="border p-8 text-center text-gray-500">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
