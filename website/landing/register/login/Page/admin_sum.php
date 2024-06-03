<?php
include 'vintage_db.php';
include 'admin_navbar.php'; 

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Summary</title>
    <link rel="stylesheet" href="css/bill.css">
</head>
<body>
    

    <div class="container">
        <h2>Sales Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query to get total products
                $totalProductsQuery = "SELECT COUNT(*) AS total_products FROM products";
                $totalProductsResult = mysqli_query($conn, $totalProductsQuery);
                $totalProductsRow = mysqli_fetch_assoc($totalProductsResult);
                $totalProducts = $totalProductsRow['total_products'];
                echo "<tr>";
                echo "<td>Total Products</td>";
                echo "<td>{$totalProducts}</td>";
                echo "</tr>";

                // Query to get total users
                $totalUsersQuery = "SELECT COUNT(*) AS total_users FROM user";
                $totalUsersResult = mysqli_query($conn, $totalUsersQuery);
                $totalUsersRow = mysqli_fetch_assoc($totalUsersResult);
                $totalUsers = $totalUsersRow['total_users'];
                echo "<tr>";
                echo "<td>Total Users</td>";
                echo "<td>{$totalUsers}</td>";
                echo "</tr>";

                // Query to get total delivered orders
                $totalDeliveredOrdersQuery = "SELECT COUNT(*) AS total_delivered_orders FROM orders WHERE order_status = 3";
                $totalDeliveredOrdersResult = mysqli_query($conn, $totalDeliveredOrdersQuery);
                $totalDeliveredOrdersRow = mysqli_fetch_assoc($totalDeliveredOrdersResult);
                $totalDeliveredOrders = $totalDeliveredOrdersRow['total_delivered_orders'];
                echo "<tr>";
                echo "<td>Total Delivered Orders</td>";
                echo "<td>{$totalDeliveredOrders}</td>";
                echo "</tr>";

                // Query to get total pending orders
                $totalPendingOrdersQuery = "SELECT COUNT(*) AS total_pending_orders FROM orders WHERE order_status = 1";
                $totalPendingOrdersResult = mysqli_query($conn, $totalPendingOrdersQuery);
                $totalPendingOrdersRow = mysqli_fetch_assoc($totalPendingOrdersResult);
                $totalPendingOrders = $totalPendingOrdersRow['total_pending_orders'];
                echo "<tr>";
                echo "<td>Total Pending Orders</td>";
                echo "<td>{$totalPendingOrders}</td>";
                echo "</tr>";
                ?>
            </tbody>
        </table>

        <h2>Sales Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Orders</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT DATE(order_date) AS date, COUNT(*) AS total_orders, SUM(total_price) AS total_sales FROM orders GROUP BY DATE(order_date)";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>{$row['date']}</td>";
                        echo "<td>{$row['total_orders']}</td>";
                        echo "<td>{$row['total_sales']}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No sales found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
