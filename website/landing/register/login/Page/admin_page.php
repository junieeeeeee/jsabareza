<?php
session_start();
if (!isset($_SESSION['admin_name'])) {
    header('location: login.php');
    exit;
}
include 'admin_navbar.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Page</title>
   <link href="https://db.onlinewebfonts.com/c/11eae19d5201ee5e6b1c2ae903ff4ea6?family=Metal+Vengeance" rel="stylesheet">
   <link rel="stylesheet" href="admin_page_and_nav.css">
</head>
<body>


<?php
include 'vintage_db.php';

// Create 'Uploaded_img' directory if it doesn't exist
if (!is_dir('uploaded_img')) {
    mkdir('uploaded_img');
}

if(isset($_POST['add_product'])){

   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_quantity = $_POST['product_quantity']; // Add this line
   $product_image = $_FILES['product_image']['name'];
   $product_image_tmp_name = $_FILES['product_image']['tmp_name'];
   $product_image_folder = 'uploaded_img/'.$product_image;

   if(empty($product_name) || empty($product_price) || empty($product_image) || empty($product_quantity)){ // Check if quantity is empty
      $message[] = 'Please fill out all fields.';
   } else {
      $insert = "INSERT INTO products(name, price, image, quantity, status) VALUES('$product_name', '$product_price', '$product_image', '$product_quantity', 'a')"; // Add quantity to the query
      $upload = mysqli_query($conn, $insert);
      if($upload){
         move_uploaded_file($product_image_tmp_name, $product_image_folder);
         $message[] = 'New product added successfully.';
      } else {
         $message[] = 'Could not add the product.';
      }
   }
}

if(isset($_GET['delete'])){
   $id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM products WHERE id = $id");
   header('location:admin_page.php');
}

// Fetch products
$select = mysqli_query($conn, "SELECT * FROM products");

?>

<div class="container">

   <div class="admin-product-form-container centered">

      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
         <h3>Add a new product</h3>
         <input type="text" placeholder="Enter product name" name="product_name" class="box">
         <input type="number" placeholder="Enter product price" name="product_price" class="box">
         <input type="number" placeholder="Enter product quantity" name="product_quantity" class="box"> <!-- Add this line -->
         <input type="file" accept="image/png, image/jpeg, image/jpg" name="product_image" class="box">
         <input type="submit" class="btn" name="add_product" value="Add product">
      </form>

   </div>

   <?php if (isset($message)) : ?>
   <div class="message">
      <?php foreach ($message as $msg) : ?>
      <p><?php echo $msg; ?></p>
      <?php endforeach; ?>
   </div>
   <?php endif; ?>

   <div class="product-display">
      <table class="product-display-table">
         <thead>
         <tr>
            <th>Product image</th>
            <th>Product name</th>
            <th>Product price</th>
            <th>Product quantity</th> <!-- Add this line -->
            <th>Action</th>
         </tr>
         </thead>
         <?php while($row = mysqli_fetch_assoc($select)) { ?>
         <tr>
            <td><img src="uploaded_img/<?php echo $row['image']; ?>" height="100" alt=""></td>
            <td><?php echo $row['name']; ?></td>
            <td>$<?php echo $row['price']; ?>/-</td>
            <td><?php echo $row['quantity']; ?></td> <!-- Add this line -->
            <td>
            <a href="admin_update.php?edit=<?php echo $row['id']; ?>" class="edit-button"> <i class="fas fa-edit"></i> Edit </a>
            <a href="admin_page.php?delete=<?php echo $row['id']; ?>" class="delete-button"> <i class="fas fa-trash"></i> Delete </a>
            
            </td>
         </tr>
      <?php } ?>
      </table>
   </div>

</div>

</body>
</html>
