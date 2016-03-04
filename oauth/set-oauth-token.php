<?php

require_once __DIR__ . '/../lib/client.php';

if (isset($_GET['error'])) {
    exit("Issue with purchase: {$_GET['error_description']}");
}

try {
    $tokenResponse = $client->post('/v2/oauth/access_token',
        [],
        [
            'form_params' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code',
                'code' => $_GET['code'],
            ]
        ]
    );

    if ($tokenResponse->getStatusCode() != 200) {
        throw new Exception("Unexpected response from token endpoint: {$tokenResponse->getBody()}");
    }
} catch (Exception $e) {
    exit("There was a problem talking with the API: {$e->getMessage()}");
}

$token = $tokenResponse->getBody()->jsonSerialize()['access_token'];
setcookie('token', $token, 0, '/');

echo '<script>window.close();</script>';
