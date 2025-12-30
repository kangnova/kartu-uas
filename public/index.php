<?php include 'header.php'; ?>

<div class="bg-white p-8 rounded-lg shadow-md text-center">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Selamat Datang di Sistem Informasi Kartu UAS</h1>
    <h2 class="text-xl text-gray-600 mb-8">Fakultas Agama Islam - Universitas Muhammadiyah Klaten</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Student Card - Always Visible -->
        <a href="student_print.php"
            class="block p-6 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition">
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">Mahasiswa: Cetak Mandiri</h3>
            <p class="text-gray-600">Input data diri dan cetak kartu ujian secara mandiri.</p>
        </a>

        <?php if (isset($is_admin) && $is_admin): ?>
            <a href="input_jadwal.php"
                class="block p-6 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                <h3 class="text-lg font-semibold text-blue-800 mb-2">Input Jadwal UAS</h3>
                <p class="text-gray-600">Input data matakuliah dan jadwal ujian per semester dan prodi.</p>
            </a>

            <a href="input_mahasiswa.php"
                class="block p-6 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                <h3 class="text-lg font-semibold text-green-800 mb-2">Input Data Mahasiswa</h3>
                <p class="text-gray-600">Registrasi data mahasiswa untuk pembuatan kartu ujian.</p>
            </a>

            <a href="cetak_kartu.php"
                class="block p-6 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition">
                <h3 class="text-lg font-semibold text-purple-800 mb-2">Cetak Kartu UAS (Admin)</h3>
                <p class="text-gray-600">Cari mahasiswa dan cetak kartu ujian mereka (Fitur Admin).</p>
            </a>
        <?php endif; ?>
    </div>

    <!-- List of Eligible Students -->
    <div class="mt-12 text-left">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Daftar Mahasiswa Siap Cetak Kartu</h3>
        
        <?php
        require_once '../config/database.php';
        
        $sql_eligible = "SELECT m.id, m.nama, m.nim, p.nama_prodi, m.semester, m.status_keuangan 
                         FROM mahasiswa m 
                         JOIN prodi p ON m.prodi_id = p.id 
                         WHERE m.status_keuangan IN ('LUNAS', 'DISPENSASI')
                         ORDER BY p.nama_prodi, m.semester, m.nama";
        $result_eligible = $conn->query($sql_eligible);
        ?>

        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            No
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Nama Mahasiswa
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            NIM
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Prodi
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Semester
                        </th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_eligible && $result_eligible->num_rows > 0): ?>
                        <?php $no = 1; while($row = $result_eligible->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    <?= $no++ ?>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($row['nama']) ?>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm text-gray-500">
                                    <?= htmlspecialchars($row['nim']) ?>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm text-gray-500">
                                    <?= htmlspecialchars($row['nama_prodi']) ?>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm text-gray-500">
                                    <?= htmlspecialchars($row['semester']) ?>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 text-sm">
                                    <a href="print_card.php?id=<?= $row['id'] ?>" target="_blank" class="inline-block bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition text-sm font-semibold shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline-block mr-1">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                        </svg>
                                        Cetak Kartu
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-5 py-5 border-b border-gray-200 text-sm text-center text-gray-500">
                                Belum ada mahasiswa yang terdaftar LUNAS / DISPENSASI.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>