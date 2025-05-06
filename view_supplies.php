<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Fetch supply records from inventory table with related product and supplier names
$query = "
    SELECT 
        i.id,
        p.name AS product_name,
        s.name AS supplier_name,
        i.quantity,
        i.cost_price,
        i.supply_date
    FROM inventory i
    JOIN products p ON i.product_id = p.id
    JOIN suppliers s ON i.supplier_id = s.id
    ORDER BY i.supply_date DESC
";
$stmt = $pdo->query($query);
$supplies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Supplies</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background-color: #f4f4f4;
        }
        .container {
            background: #fff;
            padding: 20px;
            max-width: 1100px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        input[type="text"] {
            width: 300px;
            padding: 8px;
            margin-bottom: 15px;
        }
        .btn-group {
            margin-bottom: 15px;
        }
        .btn {
            padding: 8px 12px;
            margin-right: 10px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #1e8449;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #34495e;
            color: white;
        }
        a {
            margin-top: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }
    </style>
    <script>
        function filterTable() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.querySelectorAll("tbody tr");
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? "" : "none";
            });
        }

        function exportToExcel() {
            let table = document.getElementById("supplyTable").outerHTML;
            let dataType = 'application/vnd.ms-excel';
            let blob = new Blob(['\ufeff', table], { type: dataType });
            let url = URL.createObjectURL(blob);
            let a = document.createElement("a");
            a.href = url;
            a.download = "supply_data.xls";
            a.click();
        }

        function printTable() {
            let printContents = document.getElementById("supplyTable").outerHTML;
            let originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>
</head>
<body>
<div class="container">
    <h2>📦 Supply Records</h2>

    <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="🔍 Search by any field...">

    <div class="btn-group">
        <button class="btn" onclick="exportToExcel()">📥 Export to Excel</button>
        <button class="btn" onclick="printTable()">🖨️ Print</button>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

    <table id="supplyTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Supplier</th>
                <th>Quantity</th>
                <th>Cost Price</th>
                <th>Supply Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($supplies as $index => $supply): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($supply['product_name']) ?></td>
                    <td><?= htmlspecialchars($supply['supplier_name']) ?></td>
                    <td><?= $supply['quantity'] ?></td>
                    <td><?= number_format($supply['cost_price'], 2) ?></td>
                    <td><?= $supply['supply_date'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
