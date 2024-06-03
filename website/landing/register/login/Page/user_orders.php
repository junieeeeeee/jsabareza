<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit;
}

// Include database connection
include 'vintage_db.php';


// Check if cancel button is clicked
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    
    // Update order status to cancelled
    $cancel_query = mysqli_query($conn, "UPDATE `orders` SET `order_status` = 0 WHERE `id` = '$order_id'");
    if (!$cancel_query) {
        die("Error cancelling order: " . mysqli_error($conn));
    }
}

// Check if received button is clicked
if (isset($_POST['received_order'])) {
    $order_id = $_POST['order_id'];
    
    // Update order status to received
    $received_query = mysqli_query($conn, "UPDATE `orders` SET `order_status` = 4 WHERE `id` = '$order_id'");
    if (!$received_query) {
        die("Error updating order status: " . mysqli_error($conn));
    }
}

// Fetch orders for the current user
$user_id = $_SESSION['user_id'];
$order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE `user_id` = '$user_id'");
if (!$order_query) {
    die("Error fetching orders: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    
     <!-- Font Awesome CDN Link -->
   <link href="https://db.onlinewebfonts.com/c/11eae19d5201ee5e6b1c2ae903ff4ea6?family=Metal+Vengeance" rel="stylesheet">
    <!-- Link to your custom CSS file -->
    <link rel="stylesheet" href="css/user_orders.css">
    
</head>
<body>

<!-- Navigation or Header section -->

<?php include 'user_header.php'; ?>
<div class="container">
    <h2>My Orders</h2>
    <?php
    // Check if user has any orders
    if (mysqli_num_rows($order_query) > 0) {
        while ($order = mysqli_fetch_assoc($order_query)) {
            ?>
            <div class="order-item">
                <p><strong>Order ID:</strong> <?= $order['id']; ?></p>
                <p><strong>Order:</strong> <?= $order['total_products']; ?></p>
                <p><strong>Total Price:</strong> Php<?= $order['total_price']; ?></p>
                <p><strong>Status:</strong> 
                    <?php 
                        if ($order['order_status'] == 0) {
                            echo 'Cancelled';
                        } elseif ($order['order_status'] == 1) {
                            echo 'Pending';
                        } elseif ($order['order_status'] == 2) {
                            echo 'Out for Delivery';
                        } elseif ($order['order_status'] == 3) {
                            echo 'Delivered';
                        } elseif ($order['order_status'] == 4) {
                            echo 'Received';
                        }
                    ?>
                </p>
                <?php 
                    // Fetch product name from cart based on order_id
                    $order_id = $order['id'];
                    $product_query = mysqli_query($conn, "SELECT name FROM `cart` WHERE `id` = '$order_id'");
                    if ($product_query && mysqli_num_rows($product_query) > 0) {
                        $product = mysqli_fetch_assoc($product_query);
                        echo "<p><strong>Product Name:</strong> " . $product['total_products'] . "</p>";
                    }
                ?>
                <?php if ($order['order_status'] == 1) { ?>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                        <input type="submit" name="cancel_order" value="Cancel" class="cancel-btn">
                    </form>
                <?php } elseif ($order['order_status'] == 2) { ?>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                        <input type="submit" name="received_order" value="Received" class="received-btn">
                    </form>
                <?php } ?>
            </div>
            <?php
        }
    } else {
        echo "<p>No orders found.</p>";
    }
    ?>
</div>

</body>
</html>
