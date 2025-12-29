<?php
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    die("ID Mahasiswa tidak ditemukan.");
}

$id = $_GET['id'];

// Get Student Data
$stmt = $conn->prepare("
    SELECT m.*, p.nama_prodi, p.kode_prodi 
    FROM mahasiswa m 
    JOIN prodi p ON m.prodi_id = p.id 
    WHERE m.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Mahasiswa tidak ditemukan.");
}

// Kaprodi Data
$kaprodi_list = [
    'PAI' => [
        'nama' => 'Sulistiono Shalladdin Albany, S.Pd.I, M.Pd.',
        'nidn' => '2118079103',
        'ttd'  => 'assets/img/ttd_bani.jpg'
    ],
    'PIAUD' => [
        'nama' => 'Muhammad Syafe’i, M.Pd.',
        'nidn' => '2111099002',
        'ttd'  => 'assets/img/ttd_syafei.png'
    ]
];

// Determine current Kaprodi
$current_kaprodi = $kaprodi_list[$student['kode_prodi']] ?? [
    'nama' => '.........................',
    'nidn' => '.........................',
    'ttd'  => null
];

// Get Schedule Data
$stmt_jadwal = $conn->prepare("
    SELECT * FROM jadwal_uas 
    WHERE prodi_id = ? AND semester = ?
    ORDER BY waktu ASC
");
$stmt_jadwal->bind_param("ii", $student['prodi_id'], $student['semester']);
$stmt_jadwal->execute();
$jadwal = $stmt_jadwal->get_result();
$total_sks = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu UAS - <?= $student['nim'] ?></title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 210mm; /* A4 Width usually, but let's fit to screen for print */
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }
        .header img {
            width: 80px;
            position: absolute;
            left: 10px;
            top: 0;
        }
        .header h1, .header h2, .header h3 {
            margin: 2px;
            font-weight: bold;
        }
        .header h1 { font-size: 16pt; text-transform: uppercase; }
        .header h2 { font-size: 14pt; }
        .header h3 { font-size: 12pt; text-decoration: underline; }
        
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .info-table td {
            padding: 2px 5px;
            vertical-align: top;
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .schedule-table th, .schedule-table td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }
        .schedule-table th {
            text-align: center;
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .text-center { text-align: center; }
        
        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .photo-box {
            width: 3cm;
            height: 4cm;
            border: 1px solid #ccc;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10pt;
            color: #666;
            margin-left: 50px;
        }
        .signature {
            text-align: left;
            margin-right: 50px;
            width: 250px;
        }
        .signature img {
            width: 100px;
            height: auto;
            display: block;
            margin: 10px 0;
        }
        .signature-name {
            margin-top: 10px;
            text-decoration: underline;
            font-weight: bold;
        }
        .notes {
            margin-top: 20px;
            font-size: 10pt;
        }
        .notes ol {
            padding-left: 20px;
        }
        
        @media print {
            @page { margin: 1cm; size: A4; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container">
        <!-- Header -->
        <div class="header">
            <!-- Logo -->
            <img src="assets/img/umkla.png" alt="Logo"> 
            <h1>UNIVERSITAS MUHAMMADIYAH KLATEN</h1>
            <h2>KARTU UAS (UAS)</h2>
            <h3>2025 GANJIL</h3>
        </div>

        <!-- Student Info -->
        <table class="info-table">
            <tr>
                <td width="150">Nama Mahasiswa</td>
                <td width="10">:</td>
                <td><?= $student['nama'] ?></td>
                <td width="120">Program Studi</td>
                <td width="10">:</td>
                <td><?= $student['nama_prodi'] ?></td>
            </tr>
            <tr>
                <td>NIM</td>
                <td>:</td>
                <td><?= $student['nim'] ?></td>
                <td>Semester</td>
                <td>:</td>
                <td><?= $student['semester'] ?></td>
            </tr>
        </table>

        <!-- Schedule Table -->
        <table class="schedule-table">
            <thead>
                <tr>
                    <th width="30">No</th>
                    <th width="80">Kode</th>
                    <th>NAMA MATA KULIAH</th>
                    <th width="100">Presensi</th>
                    <th width="40">SKS</th>
                    <th width="100">PARAF DOSEN</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = $jadwal->fetch_assoc()): 
                    $total_sks += $row['sks'];
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= $row['kode_matkul'] ?></td>
                    <td><?= $row['nama_matkul'] ?></td>
                    <td class="text-center">Memenuhi</td>
                    <td class="text-center"><?= $row['sks'] ?></td>
                    <td></td>
                </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold; border: none; padding-right: 15px;"></td>
                    <td class="text-center" style="font-weight: bold;"><?= $total_sks ?></td>
                    <td style="border: none;"></td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <div class="photo-box">
                FOTO 3x4
            </div>
            <div class="signature">
                <p>Klaten, <?= date('d F Y') ?></p>
                <p>Ketua Prodi <?= $student['nama_prodi'] ?></p>
                
                <?php if (!empty($current_kaprodi['ttd'])): ?>
                    <img src="<?= $current_kaprodi['ttd'] ?>" alt="Tanda Tangan Kaprodi">
                <?php else: ?>
                    <br><br><br>
                <?php endif; ?>

                <div class="signature-name">
                    <?= $current_kaprodi['nama'] ?>
                </div>
                <div>NIDN. <?= $current_kaprodi['nidn'] ?></div>
            </div>
        </div>

        <!-- Notes -->
        <div class="notes">
            <strong>Catatan :</strong>
            <ol>
                <li>Kartu ujian harus tetap dibawa selama ujian, dan diperlihatkan kepada pengawas sebelum soal dibagikan. Bagi mahasiswa yang belum memiliki kartu ujian/hilang/ketinggalan, silakan melapor pada panitia.</li>
                <li>Berpakaian sopan dan rapi selama ujian, tidak diperkenankan memakai sandal, berambut gondrong, serta memakai anting-anting (bagi laki-laki).</li>
                <li>Memakai jas almamater selama ujian berlangsung, atau kemeja putih dan celana hitam (bagi yang belum mendapatkan jas almamater).</li>
                <li>Tidak diperkenankan menggunakan alat komunikasi dan informasi apapun, mencontek, atau bekerja sama selama ujian berlangsung.</li>
                <li>Bagi Mahasiswa yang melanggar ketentuan diatas, tidak diperkenankan mengikuti ujian.</li>
            </ol>
        </div>
    </div>

</body>
</html>
