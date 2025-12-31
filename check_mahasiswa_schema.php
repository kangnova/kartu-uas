<?php
require_once 'config/database.php';
$result = $conn->query("SHOW COLUMNS FROM mahasiswa");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
