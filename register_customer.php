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
    <title>Register Customer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* style.css */

/* Reset default margins and apply global styles */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* Main container with max width */
.container {
    background-color: #ffffff;
    padding: 30px 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 450px;
}

/* Heading style */
h2 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 24px;
    color: #333;
}

/* Form layout */
form {
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 6px;
    font-weight: bold;
    color: #444;
}

input[type="text"],
textarea {
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
    resize: vertical;
}

/* Button style */
button {
    padding: 12px;
    background-color: #007BFF;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #0056b3;
}
a {
            margin-top: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }

/* Responsive tweaks (optional but safe) */
@media (max-width: 480px) {
    .container {
        padding: 20px 15px;
    }

    h2 {
        font-size: 20px;
    }

    input,
    textarea,
    button {
        font-size: 15px;
    }
    a {
            margin-top: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }
}

    </style>
</head>
<body>
<div class="container">
    <h2>➕ Register New Customer</h2>

    <form action="../actions/add_customer.php" method="POST">
        <label>Customer Name</label>
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
