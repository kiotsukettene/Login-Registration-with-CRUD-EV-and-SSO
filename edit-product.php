<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$mysqli = require __DIR__ . "/database.php";
$errors = [];

// Fetch product data
if (isset($_GET['id'])) {
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $_GET['id'], $_SESSION["user_id"]);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        header("Location: admin-dashboard.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
    
    // If no errors, update the product
    if (empty($errors)) {
        $sql = "UPDATE products SET name = ?, price = ? WHERE id = ? AND user_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sdii", $name, $price, $_GET['id'], $_SESSION["user_id"]);
        
        if ($stmt->execute()) {
            header("Location: admin-dashboard.php");
            exit;
        } else {
            $errors[] = "Error updating product";
        }
    }
}

// Get user info
$stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$isAdmin = ($user["email"] === "admin@gmail.com");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1>Edit Product</h1>
    <p>Editing as: <?= htmlspecialchars($user["fullname"]) ?></p>
    
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
                   value="<?= htmlspecialchars($product["name"]) ?>">
        </div>
        
        <div>
            <label for="price">Price</label>
            <input type="number" id="price" name="price" step="0.01" min="0"
                   value="<?= htmlspecialchars($product["price"]) ?>">
        </div>
        
        <button>Update Product</button>
        <a href="admin-dashboard.php">Cancel</a>
    </form>
</body>
</html> 