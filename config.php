<?php
/*
 * File Konfigurasi Database
 * Ganti nilai-nilai di bawah ini dengan kredensial database MySQL Anda.
 */

// Mendefinisikan konstanta untuk koneksi database
define('DB_SERVER', 'localhost');   // Server database, biasanya 'localhost'
define('DB_USERNAME', 'root');      // Username database Anda
define('DB_PASSWORD', '');          // Password database Anda
define('DB_NAME', 'tracer_study_db'); // Nama database Anda

// Membuat koneksi ke database MySQL
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Memeriksa koneksi
if ($conn->connect_error) {
    // Jika koneksi gagal, hentikan skrip dan tampilkan pesan error
    die("Koneksi Gagal: " . $conn->connect_error);
}

// Mengatur charset ke utf8mb4 untuk mendukung karakter internasional
$conn->set_charset("utf8mb4");

?>
