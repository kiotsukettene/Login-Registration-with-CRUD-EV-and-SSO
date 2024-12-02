<?php


session_start();


if (isset($_SESSION["user_id"])) {
    
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = "SELECT * FROM users
            WHERE id = {$_SESSION["user_id"]}";
            
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION["email"]) && $_SESSION["email"] === "admin@gmail.com";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    
    <h1>Home</h1>
    
    <?php if (isset($user)): ?>
        
        <p>Hello <b><?= htmlspecialchars($user["fullname"]) ?></b></p>
        
        <?php if (isAdmin()): ?>
            <p>Welcome Admin! You have access to all administrative features.</p>
            <!-- Add admin-specific features here -->
            <p><a href="admin-dashboard.php">Admin Dashboard</a></p>
        <?php else: ?>
            <p>Welcome Client! You have access to client features.</p>
            <!-- Add client-specific features here -->
            <p><a href="client-dashboard.php">Client Dashboard</a></p>
        <?php endif; ?>
        
        <p><a href="logout.php">Log out</a></p>
        
    <?php else: ?>
        
        <p><a href="login.php">Log in</a> or <a href="signup.html">sign up</a></p>
        
    <?php endif; ?>
    
</body>
</html>