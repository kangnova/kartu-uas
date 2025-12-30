<?php
require_once 'auth_check.php';
checkAdminAuth();
require_once '../config/database.php';

$prodi_id = $_GET['prodi'] ?? '';
if (!$prodi_id) {
    die("Pilih Prodi terlebih dahulu di halaman Data Jadwal.");
}

// Fetch Prodi Name
$pRow = $conn->query("SELECT nama_prodi FROM prodi WHERE id = $prodi_id")->fetch_assoc();
$nama_prodi = $pRow['nama_prodi'];

// Fetch Schedules
$sql = "SELECT * FROM jadwal_uas WHERE prodi_id = ? ORDER BY waktu ASC, semester ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $prodi_id);
$stmt->execute();
$result = $stmt->get_result();

$jadwal = [];
$semesters = [];
$times = [];

while ($row = $result->fetch_assoc()) {
    $waktu = substr($row['waktu'], 0, 16); // Y-m-d H:i
    $sem = strtoupper($row['semester']);
    
    $jadwal[$waktu][$sem] = $row;
    
    if (!in_array($sem, $semesters)) {
        $semesters[] = $sem;
    }
    
    if (!in_array($waktu, $times)) {
        $times[] = $waktu;
    }
}

// Sort Semesters (Numeric then String)
usort($semesters, function($a, $b) {
    return strnatcmp($a, $b);
});

// Sort Times
sort($times);

$days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal UAS - <?= htmlspecialchars($nama_prodi) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid black; padding: 5px; text-align: center; vertical-align: top; }
        th { background-color: #f0f0f0; }
        .date-col { width: 120px; font-weight: bold; }
        .time-col { width: 100px; }
        .matkul-name { font-weight: bold; font-size: 13px; margin-bottom: 4px; }
        .matkul-code { font-size: 11px; margin-bottom: 2px; }
        .pengawas-row { background-color: #fafafa; height: 30px; }
        .print-btn { margin-bottom: 20px; padding: 10px 20px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-btn">Cetak / Save PDF</button>

    <h2 style="text-align: center;">JADWAL UJIAN AKHIR SEMESTER (UAS)<br><?= htmlspecialchars($nama_prodi) ?></h2>

    <table>
        <thead>
            <tr>
                <th>HARI/TANGGAL</th>
                <th>JAM</th>
                <th>WAKTU</th>
                <?php foreach ($semesters as $sem): ?>
                    <th>SEMESTER <?= $sem ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            $currentDate = '';
            foreach ($times as $timeStr): 
                $dateObj = new DateTime($timeStr);
                $dateOnly = $dateObj->format('Y-m-d');
                $dayName = $days[$dateObj->format('w')];
                $formattedDate = $dayName . ', ' . $dateObj->format('d M Y');
                
                $jamKe = ''; // Logic jam ke (manual logic or based on time)
                $jamStart = $dateObj->format('H.i');
                // Calculate end time (assuming 90 mins for 2 SKS generally, or just show start)
                // For simplicity showing Raw Time Range if possible or just Start
                
                // Grouping by Date for Rowspan logic could be complex purely in loop. 
                // Simplified: Repeat date or handle logic visually.
                // Required image style: Date on left merged. 
            ?>
            <tr>
                <td class="date-col">
                    <?php if ($currentDate != $dateOnly): ?>
                        <?= $formattedDate ?>
                        <?php $currentDate = $dateOnly; ?>
                    <?php endif; ?>
                </td>
                <td><!-- Jam Ke logic needed? Use counter or mapping --></td>
                <td><?= $jamStart ?> WIB</td>
                
                <?php foreach ($semesters as $sem): ?>
                    <td>
                        <?php if (isset($jadwal[$timeStr][$sem])): 
                            $j = $jadwal[$timeStr][$sem];
                        ?>
                            <div class="matkul-name"><?= $j['nama_matkul'] ?></div>
                            <div class="matkul-code"><?= $j['kode_matkul'] ?></div>
                            <div><?= $j['sks'] ?> SKS</div>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <!-- Pengawas Row Placeholder matching image style -->
            <tr class="pengawas-row">
                <td></td>
                <td></td>
                <td><strong>Pengawas</strong></td>
                 <?php foreach ($semesters as $sem): ?>
                    <td></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
