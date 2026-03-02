<?php
require_once 'config.php';

function fetchLatestGames($pageSize = 10){
    
    $url = BASE_URL . "games?key=" . API_KEY ."&page_Size=";


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if(curl_errno($ch)){
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }
    curl_close($ch);

    if($httpCode === 200) {
        return json_decode($response, true);
    } else {
        error_log("API returned HTTP code: " . $httpCode);
        return false;
    }
}