<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="template_jadwal_uas.csv"');

$output = fopen('php://output', 'w');

// Header CSV
fputcsv($output, array('Kode Prodi', 'Semester', 'Kode Matkul', 'Nama Matkul', 'SKS', 'Waktu', 'Pengawas'));

// Contoh Data (Optional, delete if not needed)
fputcsv($output, array('PAI', '1', 'PAI101', 'Ilmu Pendidikan Islam', '2', '2024-01-20 08:00'));
fputcsv($output, array('PIAUD', '3', 'PIAUD305', 'Psikologi Perkembangan', '3', '2024-01-21 10:00'));

fclose($output);
exit;
?>
