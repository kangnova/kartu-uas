<?php
require_once 'config/database.php';

$sql = "ALTER TABLE mahasiswa MODIFY COLUMN semester VARCHAR(20) NOT NULL";
if ($conn->query($sql) === TRUE) {
    echo "Table mahasiswa modified successfully";
} else {
    echo "Error modifying table: " . $conn->error;
}
?>
