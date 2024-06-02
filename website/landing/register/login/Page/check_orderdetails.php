<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit;
}

// Include database connection
include 'vintage_db.php';

// Function to generate a random tracking number
function generateTrackingNumber() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $tracking_number = '';
    $length = 10;

    for ($i = 0; $i < $length; $i++) {
        $tracking_number .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $tracking_number;
}

// Check if order details are submitted
if (isset($_POST['order_btn'])) {
    // Retrieve order details from POST
    $user_id = $_SESSION['user_id']; // Get user_id from session
    $name = $_POST['name'];
    $number = $_POST['number'];
    $method = $_POST['method'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $street = $_POST['street'];
    $reference_number = $_POST['reference_number']; // Retrieve reference number from form
    $selected_items_json = isset($_POST['selected_items_checkout']) ? $_POST['selected_items_checkout'] : '';
    $selected_items = json_decode($selected_items_json, true);

    if (!is_array($selected_items)) {
        die("Selected items data is invalid.");
    }

    // Calculate total price and product names
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
                // Subtract the ordered quantity from the available quantity
                mysqli_query($conn, "UPDATE products SET quantity = quantity - {$product_item['quantity']} WHERE name = '{$product_item['name']}'");
            }
        }
    }

    $total_product = implode(', ', $product_name);
    $tracking_number = generateTrackingNumber(); // Generate tracking number

    // Insert order details into database with user_id
    $detail_query = mysqli_query($conn, "INSERT INTO `orders` (user_id, name, phone_number, method, province, city, barangay, street, total_products, total_price, order_status, payment_status, tracking_number, reference_number) VALUES ('$user_id','$name','$number','$method','$province','$city','$barangay','$street','$total_product','$price_total', 1, 0, '$tracking_number', '$reference_number')");

    if (!$detail_query) {
        die("Error inserting order details: " . mysqli_error($conn));
    }

    // Clear the selected items from the cart after successful order
    foreach ($selected_items as $item_id) {
        mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$item_id'");
    }

    // Display order confirmation
    echo "
        <div class='order-message-container'>
            <div class='message-container'>
                <h3>Thank you for shopping!</h3>
                <div class='order-detail'>
                    <span>".$total_product."</span>
                    <span class='total'>Total: $".$price_total."/-</span>
                </div>
                <div class='customer-details'>
                    <p>Your Name: <span>".$name."</span></p>
                    <p>Your Number: <span>".$number."</span></p>
                    <p>Your Address: <span>".$province.", ".$city.", ".$barangay.", ".$street."</span></p>
                    <p>Your Payment Mode: <span>".$method."</span></p>
                    <p>Reference Number: <span>".$reference_number."</span></p>
                    <p>(*pay when product arrives*)</p>
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

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>

   <!-- Font Awesome CDN link -->
   <link href="https://db.onlinewebfonts.com/c/11eae19d5201ee5e6b1c2ae903ff4ea6?family=Metal+Vengeance" rel="stylesheet">
   
   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/nav_user.css">

</head>
<body>

<?php include 'user_header.php'; ?>

<div class="container">

<section class="checkout-form">

   <h1 class="heading">Complete Your Order</h1>

   <form action="check_orderdetails.php" method="post">

   <div class="display-order">
      <?php
         if(isset($_POST['selected_items_checkout'])){
            $selected_items = json_decode($_POST['selected_items_checkout'], true);
            $total = 0;
            $grand_total = 0;

            if(!empty($selected_items)) {
               foreach($selected_items as $item_id){
                  $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE id = '$item_id'");
                  if (!$cart_query) {
                      die("Error fetching cart item: " . mysqli_error($conn));
                  }
                  
                  while($fetch_cart = mysqli_fetch_assoc($cart_query)){
                     $total_price = $fetch_cart['price'] * $fetch_cart['quantity'];
                     $grand_total = $total += $total_price;
      ?>
      <span><?= $fetch_cart['name']; ?>(<?= $fetch_cart['quantity']; ?>)</span>
      <?php
                  }
               }
            } else {
               echo "<div class='display-order'><span>Your cart is empty!</span></div>";
            }
         }
      ?>
      <span class="grand-total">Grand Total: $<?= $grand_total; ?></span>
      <!-- Display Tracking Number -->
      <?php if (isset($tracking_number)) : ?>
      <span>Tracking Number: <?= $tracking_number; ?></span>
      <?php endif; ?>
   </div>

      <div class="flex">
         <div class="inputBox">
            <span>Your Name</span>
            <input type="text" placeholder="Enter your name" name="name" required>
         </div>
         <div class="inputBox">
            <span>Your Number</span
