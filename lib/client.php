<?php

require_once __DIR__ . '/../vendor/autoload.php';

$clientId = '';
$clientSecret = '';

if (empty($clientId) || empty($clientSecret)) {
    exit("You must provide a client id and secret to use this demo.");
}
$client = new Shutterstock\Api\Client($clientId, $clientSecret);
