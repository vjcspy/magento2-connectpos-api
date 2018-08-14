<?php
$license = $argv[0];
$baseUrl = $argv[1];
$params  = $argv[2];

sendPostViaSocket($base, $params);

function sendPostViaSocket($url, $params) {
    $content = json_encode($params);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $curl,
        CURLOPT_HTTPHEADER,
        ["Content-type: application/json"]);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

    $json_response = curl_exec($curl);

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($status != 200) {
        $this->logger->debug(
            "Error: call to URL $url failed with status $status, response $json_response, curl_error "
            . curl_error($curl)
            . ", curl_errno "
            . curl_errno($curl));
    }

    curl_close($curl);

    return $response = json_decode($json_response, true);
}

function getBaseUrl() {
    //return "http://xcloud.smartosc.com:2005/methods/client.trigger_realtime";
    return "http://cloud.connectpos.com/methods/client.trigger_realtime";
}

