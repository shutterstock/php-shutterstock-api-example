<?php

require_once __DIR__ . '/../lib/client.php';

$input = [
    'query' => '',
    'image_type' => '',
    'orientation' => '',
    'category' => '',
    'people_number' => '',
    'color' => '',
    'page' => 1,
    'per_page' => 25,
];

foreach ($input as $key => $value) {
    if (isset($_GET[$key])) {
        $input[$key] = $_GET[$key];
    }
}

$searchParams = array_filter($input, function ($value) {
    return (strlen($value) > 0 && $value != 'any');
});

try {
    $imageResponse = $client->get('/v2/images/search', $searchParams);

    if ($imageResponse->getStatusCode() != 200) {
        throw new Exception("Unexpected response from search endpoint: {$imageResponse->getBody()}");
    }
} catch (Exception $e) {
    exit("There was a problem talking with the API: {$e->getMessage()}");
}

$imageList = $imageResponse->getBody()->jsonSerialize()['data'];
$imageList = array_filter($imageList, function ($image) {
    return (
        isset($image['id']) &&
        isset($image['assets']['large_thumb']) &&
        isset($image['description'])
    );
});
$imageList = array_map(function ($image) {
    return [
        'id' => $image['id'],
        'description' => $image['description'],
        'thumb' => $image['assets']['large_thumb']['url'],
        'size' => [
            'height' => $image['assets']['large_thumb']['height'],
            'width' => $image['assets']['large_thumb']['width'],
        ],
    ];
}, $imageList);

header('Content-Type: application/json');
echo json_encode($imageList);
