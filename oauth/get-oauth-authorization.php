<?php

/**
 * Some more information about some of the oauth/authorize parameters
 *
 * scope - lets you specify exactly what type of access the application needs
         - see https://developers.shutterstock.com/guides/authentication#scopes
 * redirect_uri - a URI that your application uses to store the return code
                - see https://developers.shutterstock.com/guides/authentication#redirect-uri-mismatch
 */
require_once __DIR__ . '/../lib/client.php';

$redirect_uri = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$redirect_uri .= "://{$_SERVER['SERVER_NAME']}";
if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) {
    $redirect_uri .= ":{$_SERVER['SERVER_PORT']}";
}
$redirect_uri .= '/oauth/set-oauth-token.php';

try {
    $authorizeResponse = $client->get('/v2/oauth/authorize',
        [
            'scope' => 'licenses.create licenses.view purchases.view',
            'state' => ('demo_date_' . (new DateTime())->getTimestamp()),
            'response_type' => 'code',
            'redirect_uri' => $redirect_uri,
            'client_id' => $clientId,
        ],
        ['allow_redirects' => false]
    );

    if ($authorizeResponse->getStatusCode() != 302) {
        throw new Exception("Unexpected response from authorize endpoint: {$authorizeResponse->getBody()}");
    }
} catch (Exception $e) {
    exit("There was a problem talking with the API: {$e->getMessage()}");
}

$redirect = $authorizeResponse->getHeader('Location');
$redirect = reset($redirect);

header('Content-Type: application/json');
echo json_encode(['url' => $redirect]);
