
CREATE DATABASE IF NOT EXISTS kartu_uas;
USE kartu_uas;

CREATE TABLE IF NOT EXISTS prodi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_prodi VARCHAR(10) NOT NULL,
    nama_prodi VARCHAR(100) NOT NULL
);

INSERT INTO prodi (kode_prodi, nama_prodi) VALUES 
('PAI', 'Pendidikan Agama Islam'),
('PIAUD', 'Pendidikan Islam Anak Usia Dini');

CREATE TABLE IF NOT EXISTS jadwal_uas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prodi_id INT NOT NULL,
    semester INT NOT NULL,
    kode_matkul VARCHAR(20) NOT NULL,
    nama_matkul VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    waktu DATETIME NOT NULL,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id)
);

CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prodi_id INT NOT NULL,
    semester INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nim VARCHAR(20) NOT NULL UNIQUE,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id)
);
