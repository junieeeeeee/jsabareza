<?php
include 'vintage_db.php';
include 'admin_navbar.php'; 

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit();
}

// Function to update order status and payment status
function updateOrderStatus($conn, $order_id, $status, $update_payment = false) {
    $update_query = "UPDATE orders SET order_status = '$status'";
    if ($update_payment) {
        $update_query .= ", payment_status = 1"; // Update payment status to 1 when confirmed
    }
    $update_query .= " WHERE id = '$order_id'";
    $result = mysqli_query($conn, $update_query);

}

if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    updateOrderStatus($conn, $order_id, 0); // Set order status to cancelled
}

if (isset($_POST['deliver_order'])) {
    $order_id = $_POST['order_id'];
    updateOrderStatus($conn, $order_id, 2); // Set order status to out for delivery
}

if (isset($_POST['received_order'])) {
    $order_id = $_POST['order_id'];
    updateOrderStatus($conn, $order_id, 4); // Set order status to received
}

if (isset($_POST['confirm_payment'])) {
    $order_id = $_POST['order_id'];
    updateOrderStatus($conn, $order_id, 1, true); // Set order status to pending and payment status to confirmed
}

// Function to get order status name
function getOrderStatusName($status) {
    switch ($status) {
        case 0:
            return 'Cancelled';
        case 1:
            return 'Pending';
        case 2:
            return 'Out for Delivery';
        case 3:
            return 'Delivered';
        case 4:
            return 'Received';
        default:
            return 'Unknown';
    }
}

// Function to get payment status name
function getPaymentStatusName($status) {
    return $status == 0 ? 'Not Confirmed' : 'Confirmed';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Page</title>
    <link rel="stylesheet" href="css/bill.css">
    <script>
        function showActions(orderId) {
            var actionButtons = document.getElementsByClassName('order-actions-' + orderId);
            for (var i = 0; i < actionButtons.length; i++) {
                actionButtons[i].style.display = 'inline-block';
            }
        }
    </script>
</head>
<body>
    
    <div class="container">
        <h2>Billing</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Method</th>
                    <th>Province</th>
                    <th>City</th>
                    <th>Barangay</th>
                    <th>Street</th>
                    <th>Total Products</th>
                    <th>Total Price</th>
                    <th>Order Status</th>
                    <th>Payment Status</th>
                    <th>Reference Number</th> <!-- New column -->
                    <th>Order Date</th>
                    <th>Action</th>
                    <th>Confirm Payment</th> <!-- New column -->
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM orders";
                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $order_id = $row['id'];
                        $order_status_name = getOrderStatusName($row['order_status']);
                        $order_status = $row['order_status'];
                        $payment_status_name = getPaymentStatusName($row['payment_status']);
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>{$row['name']}</td>";
                        echo "<td>{$row['phone_number']}</td>";
                        echo "<td>{$row['method']}</td>";
                        echo "<td>{$row['province']}</td>";
                        echo "<td>{$row['city']}</td>";
                        echo "<td>{$row['barangay']}</td>";
                        echo "<td>{$row['street']}</td>";
                        echo "<td>{$row['total_products']}</td>";
                        echo "<td>{$row['total_price']}</td>";
                        echo "<td>{$order_status_name}</td>";
                        echo "<td>{$payment_status_name}</td>";
                        echo "<td>{$row['reference_number']}</td>"; // Display reference number
                        echo "<td>{$row['order_date']}</td>";
                        echo "<td>
                                <form method='post'>
                                    <input type='hidden' name='order_id' value='{$row['id']}'>";
                        echo "<div class='action-buttons'>";
                        if ($order_status == 1) {
                            echo "<button type='submit' name='deliver_order' class='order-actions order-actions-{$order_id} deliver'>Out for Delivery</button>
                                  <button type='submit' name='cancel_order' class='order-actions order-actions-{$order_id} cancel'>Cancel</button>";
                        }
                        if ($order_status == 2) {
                            echo "<button type='submit' name='received_order' class='order-actions order-actions-{$order_id} received'>Received</button>";
                        }
                        echo "</div>
                                </form>
                            </td>";
                        // Confirm Payment Button
                        echo "<td>
                                <form method='post'>
                                    <input type='hidden' name='order_id' value='{$row['id']}'>
                                    <button type='submit' name='confirm_payment' class='confirm-payment-btn'>Confirm Payment</button>
                                </form>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='15'>No orders found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
