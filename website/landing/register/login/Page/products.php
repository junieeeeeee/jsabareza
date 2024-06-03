<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header('location: login.php');
    exit;
}
include 'vintage_db.php';

$message = array(); // Define an empty array for messages

if(isset($_POST['add_to_cart'])){
   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_POST['product_image'];
   $product_quantity = 1;
   $user_id = $_SESSION['user_id'];

   // Check if the user_id exists in the user table
   $check_user = mysqli_query($conn, "SELECT * FROM `user` WHERE id = '$user_id'");
   if(mysqli_num_rows($check_user) > 0){
      $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'");
      if(mysqli_num_rows($select_cart) > 0){
         $message[] = 'Product already added to cart';
      }else{
         $insert_product = mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, image, quantity) VALUES('$user_id', '$product_name', '$product_price', '$product_image', '$product_quantity')");
         if($insert_product){
            $message[] = 'Product added to cart successfully';
         } else {
            $message[] = 'Error: ' . mysqli_error($conn);
         }
      }
   } else {
      $message[] = 'User does not exist';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Products</title>

   <!-- Font Awesome CDN link -->
<link href="https://db.onlinewebfonts.com/c/11eae19d5201ee5e6b1c2ae903ff4ea6?family=Metal+Vengeance" rel="stylesheet">
   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/nav_user.css">
</head>
<body>
   
<?php

if(isset($message)){
   foreach($message as $message){
      echo '<div class="message"><span>'.$message.'</span> <i class="fas fa-times" onclick="this.parentElement.style.display = `none`;"></i> </div>';
   };
};

?>

<?php include 'user_header.php'; ?>

<div class="container">

<section class="products">

   <h1 class="heading">Latest Products</h1>

   <div class="box-container">

      <?php
      
      $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE quantity > 0");
      if(mysqli_num_rows($select_products) > 0){
         while($fetch_product = mysqli_fetch_assoc($select_products)){
      ?>

      <form action="" method="post">
         <div class="box">
            <img src="uploaded_img/<?php echo $fetch_product['image']; ?>" alt="">
            <h3><?php echo $fetch_product['name']; ?></h3>
            <div class="price">Php <?php echo $fetch_product['price']; ?>/-</div>
            <input type="hidden" name="product_name" value="<?php echo $fetch_product['name']; ?>">
            <input type="hidden" name="product_price" value="<?php echo $fetch_product['price']; ?>">
            <input type="hidden" name="product_image" value="<?php echo $fetch_product['image']; ?>">
            <input type="submit" class="btn" value="Add to Cart" name="add_to_cart">
         </div>
      </form>

      <?php
         };
      } else {
         echo '<p>No products available.</p>';
      }
      ?>

   </div>

</section>

</div>

<!-- Custom JavaScript file link -->
<script src="js/script.js"></script>

</body>
</html>
