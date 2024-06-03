<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit;
}

@include 'vintage_db.php';
@include 'user_header.php';

function generateTrackingNumber() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $tracking_number = '';
    $length = 10;

    for ($i = 0; $i < $length; $i++) {
        $tracking_number .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $tracking_number;
}

if (isset($_POST['order_btn'])) {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $number = $_POST['number'];
    $method = $_POST['method'];
    $courier = $_POST['courier'];
    $province = $_POST['province'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $island = $_POST['island'];

    $reference_number = '';
    $gcash_amount = '';
    $gcash_account_name = '';
    $gcash_account_number = '';

    if ($method === 'gcash') {
        $reference_number = $_POST['reference_number'] ?? '';
        $gcash_amount = $_POST['gcash_amount'] ?? '';
        $gcash_account_name = $_POST['gcash_account_name'] ?? '';
        $gcash_account_number = $_POST['gcash_account_number'] ?? '';
    }

    $selected_items_json = $_POST['selected_items_checkout'] ?? '';
    $selected_items = json_decode($selected_items_json, true);

    if (!is_array($selected_items)) {
        die("Selected items data is invalid.");
    }

    $price_total = 0;
    $product_name = [];

    if (!empty($selected_items)) {
        foreach ($selected_items as $item_id) {
            $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE id = '$item_id'");
            if (!$cart_query) {
                die("Error fetching cart item: " . mysqli_error($conn));
            }

            while ($product_item = mysqli_fetch_assoc($cart_query)) {
                $product_name[] = $product_item['name'] . ' (' . $product_item['quantity'] . ') ';
                $product_price = $product_item['price'] * $product_item['quantity'];
                $price_total += $product_price;
                mysqli_query($conn, "UPDATE products SET quantity = quantity - {$product_item['quantity']} WHERE name = '{$product_item['name']}'");
            }
        }
    }

    $total_product = implode(', ', $product_name);
    $tracking_number = generateTrackingNumber();

    // Define shipping fees
    $shipping_fees = [
        'Luzon' => 95,
        'Visayas' => 100,
        'Mindanao' => 105
    ];

    // Add shipping fee based on the selected island
    $shipping_fee = $shipping_fees[$island] ?? 0;
    $price_total += $shipping_fee;

    $detail_query = mysqli_query($conn, "INSERT INTO `orders` (user_id, name, phone_number, method, courier, province, city, barangay, street, total_products, total_price, order_status, payment_status, tracking_number, reference_number, gcash_amount, gcash_account_name, gcash_account_number, island) VALUES ('$user_id','$name','$number','$method','$courier','$province','$city','$barangay','$street','$total_product','$price_total', 1, 1, '$tracking_number', '$reference_number', '$gcash_amount', '$gcash_account_name', '$gcash_account_number', '$island')");

    if (!$detail_query) {
        die("Error inserting order details: " . mysqli_error($conn));
    }

    foreach ($selected_items as $item_id) {
        mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$item_id'");
    }

    echo "
        <div class='order-message-container'>
            <div class='message-container'>
                <h3>Thank you for shopping!</h3>
                <div class='order-detail'>
                    <span>".$total_product."</span>
                    <span class='total'>Total: Php".$price_total."/-</span>
                </div>
                <div class='customer-details'>
                    <p>Your Name: <span>".$name."</span></p>
                    <p>Your Number: <span>".$number."</span></p>
                    <p>Courier: <span>".$courier."</span></p>
                    <p>Your Address: <span>".$street.", ". $barangay.", ". $city.", ". $province.", ". $island."  </span></p>
                    <p>Your Payment Mode: <span>".$method."</span></p>";
                    if ($method === 'gcash') {
                        echo "<p>Your Reference Number: <span>".$reference_number."</span></p>
                            <p>GCash Amount: <span>".$gcash_amount."</span></p>
                            <p>GCash Account Name: <span>".$gcash_account_name."</span></p>
                            <p>GCash Account Number: <span>".$gcash_account_number."</span></p>";
                    }
                echo "<p>(*pay when product arrives*)</p>
                </div>
                <div class='tracking-details'>
                    <p>Tracking Number: <span>".$tracking_number."</span></p>
                </div>
                <a href='products.php' class='btn'>Continue Shopping</a>
            </div>
        </div>
    ";
}
?>
