<?php
require_once __DIR__ . '/../config/database.php';
$result = $conn->query("DESCRIBE jadwal_uas");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
