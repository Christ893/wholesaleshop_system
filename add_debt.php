<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch customers to populate dropdown
$stmt = $pdo->query("SELECT id, name FROM customers ORDER BY name");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Debt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #ecf0f1;
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 15px;
            font-weight: bold;
        }

        select, input[type="number"], textarea {
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            background: #004466;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: rgb(11, 121, 176);
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #2980b9;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>➕ Add New Debt</h2>
        <form action="store_debt.php" method="POST">
            <label for="customer_id">Customer</label>
            <select name="customer_id" required>
                <option value="">Select a customer</option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['id'] ?>"><?= htmlspecialchars($customer['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="amount">Amount</label>
            <input type="number" name="amount" step="0.01" required>

            <label for="description">Description (optional)</label>
            <textarea name="description" rows="3" placeholder="Enter description..."></textarea>

            <button type="submit">💾 Save Debt</button>
        </form>
        <a href="view_debts.php">← Back to Debts</a>
    </div>
</body>
</html>
