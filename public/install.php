<?php
require_once '../config/database.php';

echo "<div style='font-family: sans-serif; line-height: 1.6; padding: 20px;'>";
echo "<h1>System Installation</h1>";

// 1. Create Prodi Table
$sql_prodi = "CREATE TABLE IF NOT EXISTS prodi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_prodi VARCHAR(10) NOT NULL,
    nama_prodi VARCHAR(100) NOT NULL
)";
if ($conn->query($sql_prodi) === TRUE) {
    echo "Table 'prodi' check/creation passed.<br>";
} else {
    echo "Error creating table 'prodi': " . $conn->error . "<br>";
}

// 2. Insert Default Prodis
$check_prodi = $conn->query("SELECT count(*) as total FROM prodi");
$row_prodi = $check_prodi->fetch_assoc();
if ($row_prodi['total'] == 0) {
    $sql_insert_prodi = "INSERT INTO prodi (kode_prodi, nama_prodi) VALUES 
        ('PAI', 'Pendidikan Agama Islam'),
        ('PIAUD', 'Pendidikan Islam Anak Usia Dini')";
    if ($conn->query($sql_insert_prodi) === TRUE) {
        echo "Default Prodi data inserted.<br>";
    } else {
        echo "Error inserting Prodi data: " . $conn->error . "<br>";
    }
} else {
    echo "Prodi data already exists.<br>";
}

// 3. Create Jadwal Table
$sql_jadwal = "CREATE TABLE IF NOT EXISTS jadwal_uas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prodi_id INT NOT NULL,
    semester INT NOT NULL,
    kode_matkul VARCHAR(20) NOT NULL,
    nama_matkul VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    waktu DATETIME NOT NULL,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id)
)";
if ($conn->query($sql_jadwal) === TRUE) {
    echo "Table 'jadwal_uas' check/creation passed.<br>";
} else {
    echo "Error creating table 'jadwal_uas': " . $conn->error . "<br>";
}

// 4. Create Mahasiswa Table
$sql_mhs = "CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prodi_id INT NOT NULL,
    semester INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nim VARCHAR(20) NOT NULL UNIQUE,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id)
)";
if ($conn->query($sql_mhs) === TRUE) {
    echo "Table 'mahasiswa' check/creation passed.<br>";
} else {
    echo "Error creating table 'mahasiswa': " . $conn->error . "<br>";
}

echo "<hr>";
echo "<h3 style='color: green;'>Installation Completed!</h3>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a>";
echo "</div>";
?>
