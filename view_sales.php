<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';
$filterClause = '';
$params = [];

if ($startDate && $endDate) {
    $filterClause .= " AND s.sale_date BETWEEN ? AND ?";
    $params[] = $startDate . " 00:00:00";
    $params[] = $endDate . " 23:59:59";
}

if ($search) {
    $filterClause .= " AND (p.name LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query = "
    SELECT 
        s.id, s.sale_date, s.quantity, s.sale_price,
        p.name AS product_name,
        c.name AS customer_name
    FROM sales s
    JOIN products p ON s.product_id = p.id
    JOIN customers c ON s.customer_id = c.id
    WHERE 1=1 $filterClause
    ORDER BY s.sale_date DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Sales</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 1200px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 20px;
        }
        input, button {
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        table {
            width: 100%;
        }
        a {
            margin-bottom: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .actions a, .actions button {
            padding: 4px 8px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            color: white;
        }
        .edit-btn {
            background-color: #007bff;
        }
        .delete-btn {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 Sales Records</h2>
    <a href="dashboard.php">← Back to Dashboard</a>
    <form method="GET">
        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
        <input type="text" name="search" placeholder="Search product or customer" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">🔍 Filter</button>
    </form>

    <table id="salesTable" class="display nowrap">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Customer</th>
                <th>Quantity</th>
                <th>Sale Price</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $row): ?>
            <tr>
                <td><?= $row['sale_date'] ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= number_format($row['sale_price'], 2) ?></td>
                <td><?= number_format($row['quantity'] * $row['sale_price'], 2) ?></td>
                <td class="actions">
                    <a href="edit_sale.php?id=<?= $row['id'] ?>" class="edit-btn">✏️ Edit</a>
                    <form action="delete_sale.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this sale?');" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" class="delete-btn">🗑 Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $('#salesTable').DataTable({
            dom: 'Bfrtip',
            buttons: ['excelHtml5', 'print'],
            pageLength: 10,
            responsive: true
        });
    });
</script>
</body>
</html>
