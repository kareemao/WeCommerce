<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connect to both databases
$users_conn = new mysqli("localhost", "root", "", "users_db");
$products_conn = new mysqli("localhost", "root", "", "redstore_db");

if ($users_conn->connect_error || $products_conn->connect_error) {
    die("Connection failed.");
}

// Fetch all users
$users_result = $users_conn->query("SELECT id, name, email, role FROM users");

// Fetch all products
$products_result = $products_conn->query("SELECT id, product_name, price, stock FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f8fa;
            margin: 0;
            padding: 20px;
            color: #1c1c1c;
        }
        h1 {
            color: #143b63;
            text-align: center;
            margin-bottom: 40px;
        }
        .section {
            margin-bottom: 50px;
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #e4eef6;
            color: #143b63;
        }
        a.btn {
            padding: 6px 12px;
            margin-right: 5px;
            text-decoration: none;
            color: white;
            border-radius: 6px;
            font-size: 14px;
        }
        .edit-btn {
            background-color: #2d89ef;
        }
        .delete-btn {
            background-color: #e74c3c;
        }
        .logout {
            text-align: right;
            margin-bottom: 20px;
        }
        .logout a {
            text-decoration: none;
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="logout">
    <a href="logout.php">Logout</a>
</div>

<h1>Welcome to the Admin Page</h1>

<div class="section">
    <h2>Registered Users</h2>
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th>
        </tr>
        <?php while ($user = $users_result->fetch_assoc()): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn edit-btn">Edit</a>
                <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="section">
    <h2>Listed Products</h2>
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Actions</th>
        </tr>
        <?php while ($product = $products_result->fetch_assoc()): ?>
        <tr>
            <td><?= $product['id'] ?></td>
            <td><?= htmlspecialchars($product['product_name']) ?></td>
            <td>$<?= number_format($product['price'], 2) ?></td>
            <td><?= $product['stock'] ?></td>
            <td>
                <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn edit-btn">Edit</a>
                <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>

<?php
$users_conn->close();
$products_conn->close();
?>
