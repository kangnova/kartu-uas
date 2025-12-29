<?php include 'header.php'; ?>

<div class="bg-white p-8 rounded-lg shadow-md text-center">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Selamat Datang di Sistem Informasi Kartu UAS</h1>
    <h2 class="text-xl text-gray-600 mb-8">Fakultas Agama Islam - Universitas Muhammadiyah Klaten</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
            <h3 class="text-lg font-semibold text-purple-800 mb-2">Cetak Kartu UAS</h3>
            <p class="text-gray-600">Cari mahasiswa dan cetak kartu ujian mereka.</p>
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>