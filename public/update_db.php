<?php
require_once 'config/database.php';

$sql = "ALTER TABLE mahasiswa 
        ADD COLUMN status_keuangan ENUM('LUNAS', 'DISPENSASI', 'BELUM_LUNAS') DEFAULT 'BELUM_LUNAS', 
        ADD COLUMN catatan_keuangan TEXT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Database updated successfully";
} else {
    echo "Error updating database: " . $conn->error;
}

$conn->close();
?>
