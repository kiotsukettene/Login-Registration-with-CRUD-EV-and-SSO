<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function getGoogleClient() {
    $client = new Google_Client();
    $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
    $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
    
    // Determine if we're on localhost or production
    $is_localhost = ($_SERVER['HTTP_HOST'] === 'localhost');
    $redirect_uri = $is_localhost 
        ? 'http://localhost/Activity3/google-callback.php'
        : 'https://your-domain.com/Activity3/google-callback.php';
    
    $client->setRedirectUri($redirect_uri);
    $client->addScope('email');
    $client->addScope('profile');
    
    return $client;
} 