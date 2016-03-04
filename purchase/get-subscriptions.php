<?php

require_once __DIR__ . '/../lib/client.php';

$token = $_COOKIE['token'];

try {
    $subscriptionResponse = $client->get('/v2/user/subscriptions',
        [],
        [
            'auth' => null,
            'headers' => ['Authorization' => "Bearer {$token}"],
        ]
    );

    if ($subscriptionResponse->getStatusCode() != 200) {
        throw new Exception("Unexpected response from subscription endpoint: {$subscriptionResponse->getBody()}");
    }
} catch (Exception $e) {
    exit("There was a problem talking with the API: {$e->getMessage()}");
}

$subscriptions = $subscriptionResponse->getBody()->jsonSerialize()['data'];
$subscriptions = array_filter($subscriptions, function ($subscription) {
    $isExpired = (new DateTime($subscription['expiration_time']) > new DateTime());
    $isImage = array_reduce($subscription['formats'], function ($hasImage, $format) {
        return ($hasImage || ($format['media_type'] == 'image'));
    }, false);
    $hasBalance = (
        isset($subscription['allotment']) &&
        ($subscription['allotment']['downloads_left'] > 0)
    );

    return ($isExpired && $isImage && $hasBalance);
});
$subscriptions = array_map(function ($subscription) {
    return [
        'id' => $subscription['id'],
        'name' => $subscription['description'],
    ];
}, $subscriptions);
$subscriptions = array_values($subscriptions);

header('Content-Type: application/json');
echo json_encode($subscriptions);
