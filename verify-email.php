<?php
$mysqli = require __DIR__ . "/database.php";

if (isset($_GET["token"])) {
    $token = $_GET["token"];
    
    // Find user with this token
    $sql = "SELECT * FROM users WHERE verification_token = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // Mark user as verified
        $sql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $user["id"]);
        $stmt->execute();
        
        header("Location: verification-success.php");
        exit;
    } else {
        die("Invalid verification token");
    }
} else {
    die("No verification token provided");
} 