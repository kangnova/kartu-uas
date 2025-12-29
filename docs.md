# Sistem Informasi Kartu UAS (SI-UAS)

## Deskripsi
Sistem Informasi ini dibuat untuk mempermudah Fakultas Agama Islam (FAI) dalam mengelola jadwal UAS dan mencetak Kartu UAS mahasiswa untuk Program Studi PAI dan PIAUD.

## Fitur
1.  **Input Data Jadwal UAS**: Menginput matakuliah, SKS, waktu ujian berdasarkan Prodi dan Semester.
2.  **Input Data Mahasiswa**: Menginput data diri mahasiswa (Nama, NIM) berdasarkan Prodi dan Semester.
3.  **Cetak Kartu UAS**: Menampilkan dan mencetak kartu ujian sesuai format yang ditentukan.

## Teknologi
-   PHP 8.2
-   MySQL Database
-   HTML/CSS (Native/Bootstrap/Tailwind)

## Instalasi
1.  Pastikan XAMPP terinstall dengan PHP 8.2.
2.  Buat database dengan nama `kartu_uas` di phpMyAdmin.
3.  Import file `database.sql` ke dalam database `kartu_uas`.
4.  Buka browser dan akses `http://localhost/kartu_uas/public/`.

## Struktur Folder
-   `config/`: Konfigurasi database.
-   `public/`: File akses publik (halaman web utama).
-   `src/`: Logika sistem (jika ada pemisahan logic).
