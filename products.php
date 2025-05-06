<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $unit = trim($_POST['unit']);

    if (!$name || !$unit) {
        $errors[] = "Product name and unit are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, unit) VALUES (?, ?)");
        if ($stmt->execute([$name, $unit])) {
            $success = "Product added successfully.";
        } else {
            $errors[] = "Failed to add product.";
        }
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f9fb;
            display: flex;
            justify-content: center;
            padding: 30px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #004466;
        }

        form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        input[type="text"] {
    padding: 8px;
    width: 200px;
    border: 1px solid #ccc;
    border-radius: 6px;
}


        button {
            padding: 10px 16px;
            background-color: #004466;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #eee;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #e0f0f8;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }

        a {
            margin-bottom: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📦 Product Management</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="POST">
        <a href="dashboard.php">← Back to Dashboard</a>
        <input type="text" name="name" placeholder="Product Name" required>
        <input type="text" name="unit" placeholder="Unit (e.g. kg, pcs)" required>
        <button type="submit">Add Product</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Unit</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['unit']) ?></td>
                <td><?= $product['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
