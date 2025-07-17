<?php
/**
 * Helper untuk mengambil data dari API eksternal.
 */

// Membutuhkan file kelas API dari Careerjet
require_once('Careerjet_API.php');

// Fungsi untuk API Cuaca (sudah ada)
function getWeatherData($city, $api_key) {
    $api_url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . $api_key . "&units=metric&lang=id";
    
    // Menggunakan cURL untuk pemanggilan API yang lebih andal
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $api_url);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($result, true);
    if ($data && $data['cod'] == 200) {
        return [
            'temperature' => round($data['main']['temp']),
            'description' => ucwords($data['weather'][0]['description']),
            'icon' => "https://openweathermap.org/img/wn/" . $data['weather'][0]['icon'] . "@2x.png",
            'city' => $data['name']
        ];
    }
    return null;
}


/**
 * Fungsi pembungkus untuk memanggil API Careerjet.
 * Ini adalah fungsi yang tidak dapat ditemukan oleh skrip Anda.
 */
function getCareerjetJobs($keywords, $location, $page, $affid) {
    // Membuat instance baru dari kelas Careerjet_API
    // Gunakan 'id_ID' untuk hasil dalam Bahasa Indonesia jika didukung, atau 'en_GB' untuk Inggris
    $api = new Careerjet_API('en_GB'); 
    
    // Memanggil metode 'search' dari objek API
    $result = $api->search(array(
        'keywords' => $keywords,
        'location' => $location,
        'page' => $page,
        'affid' => $affid,
        'user_ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    ));

    return $result;
}

?>
