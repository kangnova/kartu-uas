<?php
require_once __DIR__ . '/../config/database.php';

echo "Database Connection Check...\n";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Fetching Prodis...\n";
$result = $conn->query("SELECT id, kode_prodi, nama_prodi FROM prodi");

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Kode: '" . $row['kode_prodi'] . "' | Nama: " . $row['nama_prodi'] . "\n";
        echo "Hex Kode: " . bin2hex($row['kode_prodi']) . "\n";
    }
} else {
    echo "0 results in prodi table.\n";
}
?>
