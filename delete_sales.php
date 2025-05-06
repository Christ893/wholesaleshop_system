<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit("Unauthorized access.");
}

if (!isset($_GET['id'])) {
    exit("Invalid request.");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
$stmt->execute([$id]);

header("Location: view_sales.php");
exit();
?>
