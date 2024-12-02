<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$mysqli = require __DIR__ . "/database.php";

// Delete product if requested
if (isset($_POST['delete'])) {
    $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $_POST['delete'], $_SESSION["user_id"]);
    $stmt->execute();
}

// Fetch products for current user
$stmt = $mysqli->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

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
    <title><?= $isAdmin ? 'Admin' : 'Client' ?> Dashboard</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1><?= $isAdmin ? 'Admin' : 'Client' ?> Product Management</h1>
    <p>Welcome, <?= htmlspecialchars($user["fullname"]) ?></p>
    <a href="add-product.php" class="button">Add New Product</a>
    <a href="index.php">Back to Home</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="4">No products found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product["id"]) ?></td>
                    <td><?= htmlspecialchars($product["name"]) ?></td>
                    <td>$<?= htmlspecialchars($product["price"]) ?></td>
                    <td>
                        <a href="edit-product.php?id=<?= $product["id"] ?>">Edit</a>
                        <form method="post" style="display: inline;">
                            <button type="submit" name="delete" value="<?= $product["id"] ?>" 
                                    onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html> 