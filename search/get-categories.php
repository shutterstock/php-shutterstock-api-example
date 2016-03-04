<?php

require_once __DIR__ . '/../lib/client.php';

try {
    $categoryResponse = $client->get('/v2/images/categories');

    if ($categoryResponse->getStatusCode() != 200) {
        throw new Exception("Unexpected response from category endpoint: {$categoryResponse->getBody()}");
    }
} catch (Exception $e) {
    exit("There was a problem talking with the API: {$e->getMessage()}");
}

$categoryList = $categoryResponse->getBody()->jsonSerialize()['data'];
usort($categoryList, function ($categoryA, $categoryB) {
    return $categoryA['name'] > $categoryB['name'];
});
$categoryList = array_filter($categoryList, function ($category) {
    return !in_array($category['name'], ['DELETED', 'NOT-CATEGORIZED']);
});
$categoryList = array_values($categoryList);

header('Content-Type: application/json');
echo json_encode($categoryList);
