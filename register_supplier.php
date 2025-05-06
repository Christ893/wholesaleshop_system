<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Supplier</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* General container styling */
.container {
    max-width: 500px;
    margin: 40px auto;
    padding: 25px;
    background: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    font-family: 'Segoe UI', sans-serif;
}

/* Heading */
.container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #004466;
}

/* Form elements */
form label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
    color: #333;
}

form input[type="text"],
form textarea {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}

form input[type="text"]:focus,
form textarea:focus {
    border-color: #004466;
    outline: none;
}

/* Submit button */
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

/* Responsive tweaks */
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
    <h2>➕ Register New Supplier</h2>

    <form action="../actions/add_supplier.php" method="POST">
        <label>Supplier Name</label>
        <input type="text" name="name" required>

        <label>Contact (optional)</label>
        <input type="text" name="contact">

        <label>Address (optional)</label>
        <textarea name="address" rows="3"></textarea>

        <button type="submit">Register</button>
        <a href="dashboard.php">← Back to Dashboard</a>
    </form>
</div>
</body>
</html>
