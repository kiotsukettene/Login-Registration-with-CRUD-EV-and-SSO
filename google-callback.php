<?php

require_once 'google-config.php';
use Google\Service\Oauth2 as Google_Service_Oauth2;

$client = getGoogleClient();

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Get user info
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $google_id = $google_account_info->id;

    // Connect to database
    $mysqli = require __DIR__ . "/database.php";

    // Check if user exists - check by Google ID first, then email
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
    $stmt->bind_param("ss", $google_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Update Google ID if not set
        if (empty($user['google_id'])) {
            $stmt = $mysqli->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $stmt->bind_param("si", $google_id, $user['id']);
            $stmt->execute();
        }
        // User exists - log them in
        session_start();
        session_regenerate_id();
        $_SESSION["user_id"] = $user["id"];
        header("Location: index.php");
        exit;
    } else {
        // Create new user with Google ID
        $stmt = $mysqli->prepare("INSERT INTO users (fullname, email, google_id, is_verified) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $name, $email, $google_id);
        $stmt->execute();

        session_start();
        session_regenerate_id();
        $_SESSION["user_id"] = $mysqli->insert_id;
        header("Location: index.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
} 