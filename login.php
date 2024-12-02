<?php
require_once 'google-config.php';
$client = getGoogleClient();
$authUrl = $client->createAuthUrl();

$is_invalid = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = sprintf("SELECT * FROM users
                    WHERE email = '%s'",
                   $mysqli->real_escape_string($_POST["email"]));
    
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
    if ($user) {
        if (!$user["is_verified"]) {
            $is_invalid = true;
            $error_message = "Please verify your email before logging in.";
        } else if (password_verify($_POST["password"], $user["password_hash"])) {
            session_start();
            session_regenerate_id();
            $_SESSION["user_id"] = $user["id"];
            header("Location: index.php");
            exit;
        }
    }
    
    $is_invalid = true;
    $error_message = "Invalid login credentials";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
        .google-btn {
            display: inline-block;
            background: #4285f4;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .google-btn:hover {
            background: #357ae8;
        }
    </style>
</head>
<body>
    <?php if ($is_invalid): ?>
        <em><?= $error_message ?></em>
    <?php endif; ?>

    <h1>Login</h1>
    
    <!-- Regular login form -->
    <form method="post">
        <div>
            <input type="email" id="email" name="email" placeholder="Email Address" 
                   value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
        </div>
        <div>
            <input type="password" id="password" name="password" placeholder="Password">
        </div>
        <button>Login</button>
    </form>

    <!-- Google Sign-In button -->
    <div>
        <a href="<?= htmlspecialchars($authUrl) ?>" class="google-btn">
            Sign in with Google
        </a>
    </div>
</body>
</html>

