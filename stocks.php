<?php
require_once '../database/db.php';

$search = $_GET['search'] ?? '';

$query = "
    SELECT 
        ps.product_id AS id,
        p.name,
        p.unit,
        SUM(ps.quantity) AS total_quantity,
        MAX(ps.low_stock_threshold) AS low_stock_threshold
    FROM product_supply ps
    JOIN products p ON ps.product_id = p.id
";

if (!empty($search)) {
    $query .= " WHERE p.name LIKE :search";
}

$query .= " GROUP BY ps.product_id, p.name, p.unit";

$stmt = $pdo->prepare($query);

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}

$stmt->execute();
$stockItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html>
<head>
    <title>Stock List</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; padding: 20px; }
        .container { max-width: 960px; margin: auto; background: white; padding: 20px; box-shadow: 0 0 10px #ccc; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; }
        .low-stock { background-color: #fff3cd; }
        .out-of-stock { background-color: #f8d7da; }
        .actions { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; }
        input[type="text"] { padding: 8px; width: 200px; border-radius: 4px; border: 1px solid #ccc; }
        button, .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .edit-btn { background: #007bff; color: white; }
        .delete-btn { background: #dc3545; color: white; }
        .edit-btn:hover { background: #0056b3; }
        .delete-btn:hover { background: #c82333; }
        form.inline { display: inline; }
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
    <h2>📦 Stock Overview</h2>

    <div class="actions">
        <a href="dashboard.php">← Back to Dashboard</a>
        <form method="GET" style="display:flex; gap:10px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search product name...">
            <button type="submit">Search</button>
        </form>
    </div>

    <table>
    <thead>
    <tr>
        <th>Name</th>
        <th>Quantity</th>
        <th>Unit</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($stockItems as $product): 
    $status = 'In Stock';
    $class = '';
    if ($product['total_quantity'] <= 0) {
        $status = 'Out of Stock';
        $class = 'out-of-stock';
    } elseif ($product['total_quantity'] <= $product['low_stock_threshold']) {
        $status = 'Low Stock';
        $class = 'low-stock';
    }
?>
<tr class="<?= $class ?>">
    <td><?= htmlspecialchars($product['name']) ?></td>
    <td><?= $product['total_quantity'] ?></td>
    <td><?= htmlspecialchars($product['unit']) ?></td>
    <td><?= $status ?></td>
    <td>
        <a class="btn edit-btn" href="edit_product.php?id=<?= $product['id'] ?>">Edit</a>
        <form class="inline" method="POST" action="delete_product.php" onsubmit="return confirm('Delete this product?');">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
            <button type="submit" class="btn delete-btn">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody>

</table>

</div>
</body>
</html>
