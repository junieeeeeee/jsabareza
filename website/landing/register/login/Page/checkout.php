<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header('location: login.php');
    exit;
}

@include 'vintage_db.php';

function generateTrackingNumber() {
    // Function to generate a random tracking number
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $tracking_number = '';
    $length = 10;

    for ($i = 0; $i < $length; $i++) {
        $tracking_number .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $tracking_number;
}

$shipping_fees = [
    'Luzon' => 95,
    'Visayas' => 100,
    'Mindanao' => 105
];

if (isset($_POST['order_btn'])) {
    $name = $_POST['name'];
    $number = $_POST['number'];
    $method = $_POST['method'];
    $courier = $_POST['courier'];  // Get the selected courier service
    $island = $_POST['island'];    // Get the selected island
    $province = $_POST['province'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];

    // Check if the payment method is GCash and get the reference number if available
    $reference_number = ($method === 'gcash' && isset($_POST['reference_number'])) ? $_POST['reference_number'] : '';
    $gcash_amount = ($method === 'gcash' && isset($_POST['gcash_amount'])) ? $_POST['gcash_amount'] : 0;
    $gcash_account_name = ($method === 'gcash' && isset($_POST['gcash_account_name'])) ? $_POST['gcash_account_name'] : '';
    $gcash_account_number = ($method === 'gcash' && isset($_POST['gcash_account_number'])) ? $_POST['gcash_account_number'] : '';

    $selected_items_json = isset($_POST['selected_items_checkout']) ? $_POST['selected_items_checkout'] : '';
    $selected_items = json_decode($selected_items_json, true);
    
    if (!is_array($selected_items)) {
        die("Selected items data is invalid.");
    }

    $total_price = 0;
    foreach ($selected_items as $item_id) {
        $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE id = '$item_id'");
        if (!$cart_query) {
            die("Error fetching cart item: " . mysqli_error($conn));
        }

        while ($fetch_cart = mysqli_fetch_assoc($cart_query)) {
            $total_price += $fetch_cart['price'] * $fetch_cart['quantity'];
        }
    }

    // Add shipping fee based on selected island
    $shipping_fee = isset($shipping_fees[$island]) ? $shipping_fees[$island] : 0;
    $total_price += $shipping_fee;

    $order_query = mysqli_query($conn, "INSERT INTO `order` (user_name, number, payment_method, courier, island, province, street, city, barangay, reference_number, gcash_amount, gcash_account_name, gcash_account_number, total_price) VALUES ('$name', '$number', '$method', '$courier', '$island', '$province', '$street', '$city', '$barangay', '$reference_number', '$gcash_amount', '$gcash_account_name', '$gcash_account_number', '$total_price')");
    
    if ($order_query) {
        $tracking_number = generateTrackingNumber();
        mysqli_query($conn, "UPDATE `order` SET tracking_number = '$tracking_number' WHERE user_name = '$name' AND number = '$number'");
        echo "<script>alert('Order placed successfully! Your tracking number is $tracking_number');</script>";
    } else {
        echo "<script>alert('Order placement failed. Please try again.');</script>";
    }
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
   <link rel="stylesheet" href="css/checkout.css">

</head>
<body>

<?php include 'user_header.php'; ?>

<div class="container">

<section class="checkout-form">

   <h1 class="heading">Complete Your Order</h1>

   <form action="check_orderdetails.php" method="post" onsubmit="return validateGCashAmount()">

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
               // Add shipping fee to the grand total
               $island = $_POST['island'] ?? 'Luzon'; // Default to Luzon if not set
               $shipping_fee = $shipping_fees[$island];
               $grand_total += $shipping_fee;
            } else {
               echo "<div class='display-order'><span>Your cart is empty!</span></div>";
            }
         }
      ?>
      <span class="grand-total" id="grand_total" data-total="<?= $grand_total - $shipping_fee; ?>">Grand Total: Php<?= $grand_total; ?></span>
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
            <span>Your Number</span>
            <input type="number" placeholder="Enter your number" name="number" required>
         </div>
         <div class="inputBox">
            <span>Payment Method</span>
            <select name="method" id="payment_method" onchange="showPaymentDetails()">
               <option value="cash on delivery" selected>Cash on Delivery</option>
               <option value="gcash">Gcash</option>
            </select>
         </div>
         <div id="gcash_details" style="display: none;">
            <div class="inputBox">
               <span>Reference Number</span>
               <input type="text" placeholder="Enter reference number" name="reference_number" id="reference_number">
            </div>
            <div class="inputBox">
               <span>GCash Amount</span>
               <input type="number" placeholder="Enter GCash amount" name="gcash_amount" id="gcash_amount">
            </div>
            <div class="inputBox">
               <span>GCash Account Name</span>
               <input type="text" placeholder="Enter GCash account name" name="gcash_account_name" id="gcash_account_name">
            </div>
            <div class="inputBox">
               <span>GCash Account Number</span>
               <input type="text" placeholder="Enter GCash account number" name="gcash_account_number" id="gcash_account_number">
            </div>
            <!-- Replace 'your_qr_code_image.jpg' with the path to your QR code image -->
            <img src="vtg pics/qr.jpg" alt="QR Code" id="qr_code_image" style="display: none;">
         </div>
         <div class="inputBox">
            <span>Courier Service</span>
            <select name="courier" id="courier_service">
               <option value="J&T Express" selected>J&T Express</option>
               <option value="Flash Express">Flash Express</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Island</span>
            <select name="island" id="island_service" onchange="updateShippingFee()">
               <option value="Luzon" selected>Luzon</option>
               <option value="Visayas">Visayas</option>
               <option value="Mindanao">Mindanao</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Province</span>
            <input type="text" placeholder="e.g. Maharashtra" name="province" required>
         </div>
         <div class="inputBox">
            <span>City</span>
            <input type="text" placeholder="e.g. Mumbai" name="city" required>
         </div>
         <div class="inputBox">
            <span>Barangay</span>
            <input type="text" placeholder="e.g. Poblacion" name="barangay" required>
         </div>
         <div class="inputBox">
            <span>Street</span>
            <input type="text" placeholder="e.g. Main Street" name="street" required>
         </div>
      </div>
      <input type="hidden" name="selected_items_checkout" value='<?= htmlspecialchars(json_encode($selected_items), ENT_QUOTES, 'UTF-8'); ?>'>
      <input type="hidden" id="correct_gcash_amount" value="<?= $grand_total; ?>">
      <input type="submit" value="Order Now" name="order_btn" class="btn">
   </form>

</section>

</div>

<!-- Custom JavaScript file link -->
<script>
    function showPaymentDetails() {
        var paymentMethod = document.getElementById("payment_method").value;
        var gcashDetailsDiv = document.getElementById("gcash_details");
        var qrCodeImage = document.getElementById("qr_code_image");
        var referenceNumber = document.getElementById("reference_number");
        var gcashAmount = document.getElementById("gcash_amount");
        var gcashAccountName = document.getElementById("gcash_account_name");
        var gcashAccountNumber = document.getElementById("gcash_account_number");

        if (paymentMethod === "gcash") {
            gcashDetailsDiv.style.display = "block";
            qrCodeImage.style.display = "inline-block";

            // Add required attribute to GCash fields
            referenceNumber.setAttribute("required", "required");
            gcashAmount.setAttribute("required", "required");
            gcashAccountName.setAttribute("required", "required");
            gcashAccountNumber.setAttribute("required", "required");
        } else {
            gcashDetailsDiv.style.display = "none";
            qrCodeImage.style.display = "none";

            // Remove required attribute from GCash fields
            referenceNumber.removeAttribute("required");
            gcashAmount.removeAttribute("required");
            gcashAccountName.removeAttribute("required");
            gcashAccountNumber.removeAttribute("required");

            // Clear the GCash fields
            referenceNumber.value = "";
            gcashAmount.value = "";
            gcashAccountName.value = "";
            gcashAccountNumber.value = "";
        }
    }

    function updateShippingFee() {
        var islandService = document.getElementById("island_service").value;
        var shippingFee = 0;

        switch (islandService) {
            case "Luzon":
                shippingFee = 95;
                break;
            case "Visayas":
                shippingFee = 100;
                break;
            case "Mindanao":
                shippingFee = 105;
                break;
        }

        var grandTotalSpan = document.getElementById("grand_total");
        var initialTotal = parseFloat(grandTotalSpan.getAttribute("data-total"));
        
        grandTotalSpan.innerText = "Grand Total: Php" + (initialTotal + shippingFee);

        // Update the hidden correct GCash amount field
        document.getElementById("correct_gcash_amount").value = initialTotal + shippingFee;
    }

    function validateGCashAmount() {
        var paymentMethod = document.getElementById("payment_method").value;
        if (paymentMethod === "gcash") {
            var enteredAmount = parseFloat(document.getElementById("gcash_amount").value);
            var correctAmount = parseFloat(document.getElementById("correct_gcash_amount").value);
            
            if (enteredAmount !== correctAmount) {
                alert("Please enter the correct GCash amount: Php" + correctAmount);
                return false;
            }
        }
        return true;
    }
</script>
   
</body>
</html>
