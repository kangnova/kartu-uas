<?php
require_once 'config/database.php';

echo "Seeding Database...\n";

// 1. Seed Prodi (Already in SQL but let's ensure)
// The SQL schema already inserts them, so we skip or check.
$prodi_pai_id = 1; // Assuming PAI is ID 1 from the AUTO_INCREMENT logic in SQL
$prodi_piaud_id = 2;

// 2. Seed Mahasiswa
$nim = '202511012';
$nama = 'ABDULLAH FARHAN';
$semester = 1;

$check = $conn->query("SELECT * FROM mahasiswa WHERE nim = '$nim'");
if ($check->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO mahasiswa (prodi_id, semester, nama, nim) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $prodi_pai_id, $semester, $nama, $nim);
    $stmt->execute();
    echo "Inserted Student: $nama\n";
    $mahasiswa_id = $stmt->insert_id;
    $stmt->close();
} else {
    echo "Student $nama already exists.\n";
}

// 3. Seed Jadwal UAS (Based on the image)
$jadwal_data = [
    ['PAI101', 'Studi Qur\'an', 3, '2026-01-05 08:00:00'],
    ['PAI102', 'Studi Hadits', 3, '2026-01-05 10:00:00'],
    ['PAI103', 'Muhammadiyah Studies', 3, '2026-01-06 08:00:00'],
    ['PAI104', 'Filsafat Ilmu', 2, '2026-01-06 10:00:00'],
    ['PAI105', 'Pancasila', 2, '2026-01-07 08:00:00'],
    ['PAI106', 'Bahasa Arab', 2, '2026-01-07 10:00:00'],
    ['PAI107', 'Bahasa Inggris', 2, '2026-01-08 08:00:00'],
    ['PAI108', 'Psikologi Pendidikan Islam', 3, '2026-01-08 10:00:00'],
];

foreach ($jadwal_data as $j) {
    $kode = $j[0];
    $check = $conn->query("SELECT * FROM jadwal_uas WHERE kode_matkul = '$kode' AND prodi_id = $prodi_pai_id AND semester = $semester");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO jadwal_uas (prodi_id, semester, kode_matkul, nama_matkul, sks, waktu) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $prodi_pai_id, $semester, $j[0], $j[1], $j[2], $j[3]);
        $stmt->execute();
        $stmt->close();
    }
}
echo "Inserted Schedule for PAI Semester 1.\n";

echo "Seeding Completed.\n";
echo "You can now search for NIM: $nim in the Cetak Kartu page.\n";
?>
