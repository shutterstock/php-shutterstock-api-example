<?php

require_once __DIR__ . '/../lib/client.php';

try {
    $licenseResponse = $client->post("/v2/images/licenses?subscription_id={$_GET['subscription']}",
        [
            'images' => [['image_id' => $_GET['image']]]
        ],
        [
            'auth' => null,
            'headers' => ['Authorization' => "Bearer {$_COOKIE['token']}"],
        ]
    );

    if ($licenseResponse->getStatusCode() != 200) {
        throw new Exception("Unexpected response from license endpoint: {$licenseResponse->getBody()}");
    }
} catch (Exception $e) {
    exit("There was a problem talking with the API: {$e->getMessage()}");
}

$licenseData = $licenseResponse->getBody()->jsonSerialize()['data'];
$licenseData = reset($licenseData);
if (!isset($licenseData['download']['url'])) {
    exit("Error during licensing: {$licenseData['error']}");
}

$downloadUrl = $licenseData['download']['url'];

header('Content-Type: application/json');
echo json_encode(['url' => $downloadUrl]);
