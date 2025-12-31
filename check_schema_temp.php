<?php
require_once 'config/database.php';
$result = $conn->query("SHOW COLUMNS FROM jadwal_uas");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
$semesters = $conn->query("SELECT DISTINCT semester FROM jadwal_uas ORDER BY semester ASC");
echo "\nSemesters:\n";
while($row = $semesters->fetch_assoc()) {
    echo $row['semester'] . "\n";
}
?>
