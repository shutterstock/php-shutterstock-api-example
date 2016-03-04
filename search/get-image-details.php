<?php

if (!isset($_GET['id'])) {
    exit('You must provide an image id.');
}

require_once __DIR__ . '/../lib/client.php';

try {
    $imageResponse = $client->get("/v2/images/{$_GET['id']}");

    if ($imageResponse->getStatusCode() != 200) {
        throw new Exception("Unexpected response from images endpoint: {$imageResponse->getBody()}");
    }
} catch (Exception $e) {
    exit("There was a problem talking with the API: {$e->getMessage()}");
}

$image = $imageResponse->getBody()->jsonSerialize();
$imageResponse = [
    'id' => $image['id'],
    'description' => $image['description'],
    'preview' => $image['assets']['preview']['url'],
    'size' => [
        'height' => $image['assets']['preview']['height'],
        'width' => $image['assets']['preview']['width'],
    ],
    'keywords' => $image['keywords'],
];

header('Content-Type: application/json');
echo json_encode($imageResponse);
