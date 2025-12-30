<?php
require_once __DIR__ . '/config/database.php';

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM jadwal_uas LIKE 'pengawas'");
if ($result->num_rows == 0) {
    echo "Adding 'pengawas' column...\n";
    $sql = "ALTER TABLE jadwal_uas ADD COLUMN pengawas VARCHAR(100) DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'pengawas' added successfully.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'pengawas' already exists.\n";
}
?>
