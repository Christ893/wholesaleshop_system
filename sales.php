<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Fetch customers
$customersStmt = $pdo->query("SELECT id, name FROM customers");
$customers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products
$productsStmt = $pdo->query("SELECT id, name FROM products");
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Record Sale</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    /* Full page flex layout */
    body {
        margin: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f4f8;
        font-family: 'Segoe UI', sans-serif;
    }

    /* Container Styling */
    .container {
        width: 100%;
        max-width: 500px;
        padding: 25px;
        background: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
    }

    .container h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #004466;
    }

    form label {
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
        color: #333;
    }

    form input[type="number"],
    form select {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    form input[type="number"]:focus,
    form select:focus {
        border-color: #004466;
        outline: none;
    }

    form button[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: #004466;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    form button[type="submit"]:hover {
        background-color: #006699;
    }
    a {
            margin-bottom: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }

    @media (max-width: 600px) {
        .container {
            margin: 20px;
            padding: 20px;
        }

        form button[type="submit"] {
            font-size: 15px;
        }
        a {
            margin-bottom: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }
    }
</style>

</head>
<body>
    <div class="container">
        <h2>💰 Record New Sale</h2>
        <form action="../actions/add_sale.php" method="POST">
            <label>Customer</label>
            <select name="customer_id" required>
                <option value="">Select Customer</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Product</label>
            <select name="product_id" required>
                <option value="">Select Product</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Quantity Sold</label>
            <input type="number" name="quantity" min="1" required>

            <label>Sale Price (per unit)</label>
            <input type="number" name="sale_price" step="0.01" required>

            <label>Sale Date</label>
            <input type="date" name="sale_date" required>

            <input type="hidden" name="sold_by" value="<?= $_SESSION['user_id'] ?>">

            <button type="submit">Save Sale</button>
            <a href="dashboard.php">← Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
