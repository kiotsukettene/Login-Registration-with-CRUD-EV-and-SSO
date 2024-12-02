<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mysqli = require __DIR__ . "/database.php";
    
    // Validate name
    $name = trim($_POST["name"]);
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    // Validate price
    $price = trim($_POST["price"]);
    if (empty($price)) {
        $errors[] = "Price is required";
    } elseif (!is_numeric($price)) {
        $errors[] = "Price must be a number";
    } elseif ($price <= 0) {
        $errors[] = "Price must be greater than zero";
    }
    
    // If no errors, insert the product
    if (empty($errors)) {
        $sql = "INSERT INTO products (name, price, user_id) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sdi", $name, $price, $_SESSION["user_id"]);
        
        if ($stmt->execute()) {
            header("Location: admin-dashboard.php");
            exit;
        } else {
            $errors[] = "Error saving product";
        }
    }
}

// Get user info
$mysqli = require __DIR__ . "/database.php";
$stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$isAdmin = ($user["email"] === "admin@gmail.com");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1>Add New Product</h1>
    <p>Adding as: <?= htmlspecialchars($user["fullname"]) ?></p>
    
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div>
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" 
                   value="<?= htmlspecialchars($_POST["name"] ?? "") ?>">
        </div>
        
        <div>
            <label for="price">Price</label>
            <input type="number" id="price" name="price" step="0.01" min="0"
                   value="<?= htmlspecialchars($_POST["price"] ?? "") ?>">
        </div>
        
        <button>Add Product</button>
        <a href="admin-dashboard.php">Cancel</a>
    </form>
</body>
</html> 