<?php
session_start();
require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: stock_list.php');
    exit();
}

$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Product not found.";
    exit();
}

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $unit = trim($_POST['unit']);
    $quantity = (int) $_POST['quantity'];
    $low_stock = (int) $_POST['low_stock_threshold'];

    if (!$name || !$unit) {
        $errors[] = "Name and unit are required.";
    } else {
        $update = $pdo->prepare("UPDATE products SET name = ?, unit = ?, quantity = ?, low_stock_threshold = ? WHERE id = ?");
        if ($update->execute([$name, $unit, $quantity, $low_stock, $id])) {
            $success = "Product updated.";
            $stmt->execute([$id]);
            $product = $stmt->fetch(); // refresh data
        } else {
            $errors[] = "Failed to update.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 500px; margin: auto; background: white; padding: 20px; box-shadow: 0 0 10px #ccc; border-radius: 8px; }
        input, button { width: 100%; padding: 10px; margin: 8px 0; }
        .success { color: green; }
        .error { color: red; }
        a { display: block; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>✏️ Edit Product</h2>

    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if ($errors): ?><div class="error"><?= implode('<br>', $errors) ?></div><?php endif; ?>

    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label>Unit:</label>
        <input type="text" name="unit" value="<?= htmlspecialchars($product['unit']) ?>" required>

        <label>Quantity:</label>
        <input type="number" name="quantity" value="<?= $product['quantity'] ?>" required>

        <label>Low Stock Threshold:</label>
        <input type="number" name="low_stock_threshold" value="<?= $product['low_stock_threshold'] ?>" required>

        <button type="submit">Save Changes</button>
    </form>

    <a href="stocks.php">← Back to Stock List</a>
</div>
</body>
</html>
