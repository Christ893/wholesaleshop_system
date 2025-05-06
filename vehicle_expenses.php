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
    $description = trim($_POST['description']);
    $amount = floatval($_POST['amount']);
    $expense_date = $_POST['expense_date'];

    if (!$description || !$amount || !$expense_date) {
        $errors[] = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO vehicle_expenses (description, amount, expense_date) VALUES (?, ?, ?)");
        if ($stmt->execute([$description, $amount, $expense_date])) {
            $success = "Expense recorded successfully.";
        } else {
            $errors[] = "Failed to record expense.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Expense Entry</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Base layout: center form in all viewports */
body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f4f8;
    font-family: 'Segoe UI', sans-serif;
}

/* Container with responsive padding */
.container {
    width: 90%;
    max-width: 500px;
    padding: 25px;
    background: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    box-sizing: border-box;
}

/* Title */
.container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #004466;
    font-size: 1.5rem;
}

/* Form fields */
.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
    color: #333;
}

input[type="text"],
input[type="number"],
input[type="date"] {
    width: 100%;
    padding: 10px 12px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-sizing: border-box;
    transition: border-color 0.3s;
}

input:focus {
    border-color: #004466;
    outline: none;
}

/* Submit button */
button[type="submit"] {
    width: 100%;
    padding: 12px;
    background-color: #004466;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s;
}

button[type="submit"]:hover {
    background-color: #006699;
}

/* Alert messages */
.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-size: 0.95rem;
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

/* Responsive typography and spacing */
@media (max-width: 480px) {
    .container {
        padding: 20px;
    }

    .container h2 {
        font-size: 1.3rem;
    }

    input,
    button {
        font-size: 0.95rem;
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
    <h2>🚚 Vehicle Expense Entry</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="form-container">
        <div class="form-group">
            <label>Description:</label>
            <input type="text" name="description" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Amount:</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Date:</label>
            <input type="date" name="expense_date" class="form-control" required>
        </div>

        <button type="submit">Save Expense</button>
        <a href="dashboard.php">← Back to Dashboard</a>
    </form>
</div>
</body>
</html>
