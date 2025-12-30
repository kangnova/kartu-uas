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
$_SESSION['prodi_name'] = $nama_prodi; // For PDF filename etc if needed

// Fetch Schedules (Ordered by Time, then Semester)
$sql = "SELECT * FROM jadwal_uas WHERE prodi_id = ? ORDER BY waktu ASC, semester ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $prodi_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$semesters = [];
$dates = [];

while ($row = $result->fetch_assoc()) {
    $timeFull = $row['waktu'];
    $dateOnly = date('Y-m-d', strtotime($timeFull));
    $timeOnly = date('H:i', strtotime($timeFull)); // 14:00
    
    // Normalize Semester (in case mixed case)
    $sem = strtoupper($row['semester']);
    if (!in_array($sem, $semesters)) $semesters[] = $sem;

    // Structure: Date -> Time -> Semester -> Data
    $data[$dateOnly][$timeOnly][$sem] = $row;
    
    // Keep track of dates and times for iteration
    if (!isset($dates[$dateOnly])) $dates[$dateOnly] = [];
    if (!in_array($timeOnly, $dates[$dateOnly])) $dates[$dateOnly][] = $timeOnly;
}

// Sort Semesters Naturally (I, II, ... 7, 7 Non Reg)
usort($semesters, function($a, $b) {
    return strnatcmp($a, $b);
});

$daysIndo = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$monthsIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

function formatTanggalIndo($dateStr, $days, $months) {
    $ts = strtotime($dateStr);
    $dayName = $days[date('w', $ts)];
    $d = date('j', $ts);
    $m = $months[(int)date('n', $ts)];
    $y = date('Y', $ts);
    return "$dayName, $d<br>$m<br>$y";
}

// Mapping Jam Ke (Logic based on start time approximation)
function getJamKe($time) {
    // Example logic based on standard UAS slots
    if ($time >= '08:00' && $time < '09:30') return 1;
    if ($time >= '09:45' && $time < '11:15') return 2;
    if ($time >= '13:00' && $time < '14:30') return 1; // Afternoon session restart? or continue?
    // Let's use simple counter per day for now as per image '1', '2'
    return 1; 
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal UAS - <?= htmlspecialchars($nama_prodi) ?></title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h2 { margin: 5px 0 15px 0; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; border: 2px solid #000; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        th { background-color: #ffffff; font-weight: bold; height: 30px; }
        .date-col { width: 100px; font-weight: bold; background-color: #fff; }
        .jam-col { width: 30px; font-weight: bold; }
        .waktu-col { width: 100px; }
        .matkul-cell { height: 50px; }
        .matkul-name { font-weight: bold; font-size: 12px; }
        .matkul-code { font-size: 10px; margin-top: 2px; font-weight: bold; }
        .sks-code { font-size: 10px; margin-top: 2px; }
        .pengawas-row td { background-color: #fcfcfc; height: 25px; font-style: italic; }
        .print-cls { margin-bottom: 10px; }
        @media print { .print-cls { display: none; } }
    </style>
</head>
<body>
    <div class="print-cls">
         <button onclick="window.print()" style="padding: 10px 20px; font-weight: bold;">Cetak Jadwal</button>
    </div>

    <center>
        <h2>JADWAL UJIAN AKHIR SEMESTER (UAS) GANJIL T.A 2024/2025<br>FAKULTAS AGAMA ISLAM<br>PRODI <?= strtoupper($nama_prodi) ?></h2>
    </center>

    <table>
        <thead>
            <tr>
                <th rowspan="2">HARI / TANGGAL</th>
                <th rowspan="2">JAM</th>
                <th rowspan="2">WAKTU</th>
                <th colspan="<?= count($semesters) ?>">SEMESTER</th>
            </tr>
            <tr>
                <?php foreach ($semesters as $sem): ?>
                    <th><?= $sem ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dates as $date => $times): 
                // Calculate RowSpan: (Number of times * 2) because each time has a data row AND a pengawas row
                $rowSpan = count($times) * 2; 
                $datePrinted = false;
                $jamCounter = 1;
            ?>
                <?php foreach ($times as $time): 
                     // Calculate End Time (Start + 90 mins usually, or check layout)
                     // Example: 14.00 - 15.30
                     $endTime = date('H.i', strtotime($time) + (90 * 60)); 
                     $range = date('H.i', strtotime($time)) . " - " . $endTime;
                ?>
                    <!-- Data Row -->
                    <tr>
                        <?php if (!$datePrinted): ?>
                            <td rowspan="<?= $rowSpan ?>" class="date-col">
                                <?= formatTanggalIndo($date, $daysIndo, $monthsIndo) ?>
                            </td>
                            <?php $datePrinted = true; ?>
                        <?php endif; ?>
                        
                        <td class="jam-col"><?= $jamCounter++ ?></td>
                        <td class="waktu-col"><?= $range ?></td>
                        
                        <?php foreach ($semesters as $sem): ?>
                            <td class="matkul-cell">
                                <?php if (isset($data[$date][$time][$sem])): 
                                    $row = $data[$date][$time][$sem];
                                ?>
                                    <div class="matkul-cell-content">
                                        <div class="matkul-name"><?= $row['nama_matkul'] ?></div>
                                        <div class="matkul-code"><?= $row['kode_matkul'] ?></div>
                                        <!-- Placeholder for Class Code if exists, e.g. F & 4 -->
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- Pengawas Row -->
                    <tr class="pengawas-row">
                        <!-- Date col is spanned -->
                        <td colspan="2"><strong>Pengawas</strong></td>
                        <?php foreach ($semesters as $sem): ?>
                            <td>
                                <?php if (isset($data[$date][$time][$sem])): 
                                    $row = $data[$date][$time][$sem];
                                ?>
                                    <span style="font-size: 11px;"><?= htmlspecialchars($row['pengawas'] ?? '') ?></span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
