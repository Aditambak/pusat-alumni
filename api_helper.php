<?php


require_once('Careerjet_API.php');


function getWeatherData($city, $api_key) {
    $api_url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . $api_key . "&units=metric&lang=id";
    

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



function getCareerjetJobs($keywords, $location, $page, $affid) {

    $api = new Careerjet_API('en_GB'); 
    

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
