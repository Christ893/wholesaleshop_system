<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: view_sales.php');
    exit();
}

$saleId = $_GET['id'];

// Fetch the sale
$stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
$stmt->execute([$saleId]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    die("Sale not found.");
}

// Fetch customers and products for form
$customers = $pdo->query("SELECT id, name FROM customers")->fetchAll(PDO::FETCH_ASSOC);
$products = $pdo->query("SELECT id, name FROM products")->fetchAll(PDO::FETCH_ASSOC);

// Restore stock from the old sale
function restoreStock($pdo, $product_id, $quantity) {
    // FIFO: restore to the most recent supply with space
    $stmt = $pdo->prepare("SELECT id FROM product_supply WHERE product_id = ? ORDER BY supply_date DESC");
    $stmt->execute([$product_id]);
    $supplies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($supplies as $supply) {
        $pdo->prepare("UPDATE product_supply SET quantity = quantity + ? WHERE id = ?")
            ->execute([$quantity, $supply['id']]);
        break; // We assume the full quantity can go back to one record
    }
}

// Deduct stock from product_supply (FIFO)
function deductStock($pdo, $product_id, $quantity) {
    $stmt = $pdo->prepare("SELECT id, quantity FROM product_supply WHERE product_id = ? AND quantity > 0 ORDER BY supply_date ASC");
    $stmt->execute([$product_id]);
    $supplies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $remaining = $quantity;
    foreach ($supplies as $supply) {
        if ($remaining <= 0) break;
        $deduct = min($remaining, $supply['quantity']);
        $pdo->prepare("UPDATE product_supply SET quantity = quantity - ? WHERE id = ?")
            ->execute([$deduct, $supply['id']]);
        $remaining -= $deduct;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $sale_price = $_POST['sale_price'];
    $sale_date = $_POST['sale_date'];

    // Restore stock from old sale
    restoreStock($pdo, $sale['product_id'], $sale['quantity']);

    // Deduct stock for new sale
    deductStock($pdo, $product_id, $quantity);

    // Update sale record
    $updateStmt = $pdo->prepare("UPDATE sales SET customer_id = ?, product_id = ?, quantity = ?, sale_price = ?, sale_date = ? WHERE id = ?");
    $updateStmt->execute([$customer_id, $product_id, $quantity, $sale_price, $sale_date, $saleId]);

    header("Location: view_sales.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Sale</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f8f8f8;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            padding: 40px;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            font-weight: bold;
            margin-top: 10px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #004466;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background-color: #006699;
        }
        a {
            display: inline-block;
            margin-top: 16px;
            text-decoration: none;
            color: #004466;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Sale</h2>
        <form method="POST">
            <label>Customer</label>
            <select name="customer_id" required>
                <option value="">Select Customer</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $sale['customer_id'] == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Product</label>
            <select name="product_id" required>
                <option value="">Select Product</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $sale['product_id'] == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Quantity</label>
            <input type="number" name="quantity" min="1" value="<?= $sale['quantity'] ?>" required>

            <label>Sale Price (per unit)</label>
            <input type="number" name="sale_price" step="0.01" value="<?= $sale['sale_price'] ?>" required>

            <label>Sale Date</label>
            <input type="date" name="sale_date" value="<?= $sale['sale_date'] ?>" required>

            <button type="submit">Update Sale</button>
        </form>
        <a href="view_sales.php">← Back to Sales</a>
    </div>
</body>
</html>
