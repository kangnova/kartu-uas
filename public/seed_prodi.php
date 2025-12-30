<?php
require_once __DIR__ . '/../config/database.php';

// Check if table exists
$checkTable = $conn->query("SHOW TABLES LIKE 'prodi'");
if ($checkTable->num_rows == 0) {
    echo "Tabel 'prodi' tidak ditemukan. Membuat tabel...\n";
    $conn->query("CREATE TABLE IF NOT EXISTS prodi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode_prodi VARCHAR(10) NOT NULL,
        nama_prodi VARCHAR(100) NOT NULL
    )");
}

// Check if data empty
$result = $conn->query("SELECT * FROM prodi");
if ($result->num_rows == 0) {
    echo "Seeding data prodi...\n";
    $sql = "INSERT INTO prodi (kode_prodi, nama_prodi) VALUES 
    ('PAI', 'Pendidikan Agama Islam'),
    ('PIAUD', 'Pendidikan Islam Anak Usia Dini')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Data Prodi berhasil ditambahkan.\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
} else {
    echo "Data Prodi sudah ada.\n";
}
?>
