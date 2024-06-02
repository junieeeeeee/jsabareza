<?php
include 'vintage_db.php';
session_start();

if(!isset($_SESSION['user_id'])){
   header('location:login.php');
   exit();
}

$user_id = $_SESSION['user_id'];

if(isset($_GET['remove'])){
   $remove_id = intval($_GET['remove']);
   
   $stmt = $conn->prepare("DELETE FROM `cart` WHERE id = ? AND user_id = ?");
   $stmt->bind_param('ii', $remove_id, $user_id);
   
   if($stmt->execute()){
      header('location:cart.php');
      exit();
   } else {
      echo "Error deleting record: " . $conn->error;
   }
   $stmt->close();
}

if(isset($_GET['delete_all'])){
   $stmt = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $stmt->bind_param('i', $user_id);
   
   if($stmt->execute()){
      header('location:cart.php');
      exit();
   } else {
      echo "Error deleting records: " . $conn->error;
   }
   $stmt->close();
}

if(isset($_POST['delete_selected'])){
   if(!empty($_POST['selected_items'])){
      $selected_items = $_POST['selected_items'];
      $placeholders = implode(',', array_fill(0, count($selected_items), '?'));
      $types = str_repeat('i', count($selected_items));
      
      $stmt = $conn->prepare("DELETE FROM `cart` WHERE id IN ($placeholders) AND user_id = ?");
      $params = array_merge($selected_items, [$user_id]);
      $stmt->bind_param($types . 'i', ...$params);
      
      if($stmt->execute()){
         header('location:cart.php');
         exit();
      } else {
         echo "Error deleting selected items: " . $conn->error;
      }
      $stmt->close();
   }
}
?>

<?php include 'user_header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart</title>

   <!-- Font Awesome CDN Link -->
   <link href="https://db.onlinewebfonts.com/c/11eae19d5201ee5e6b1c2ae903ff4ea6?family=Metal+Vengeance" rel="stylesheet">
   
   <!-- Custom CSS File Link -->
   <link rel="stylesheet" href="nav_user.css">
</head>
<body>

<div class="container">

<section class="shopping-cart">

   <h1 class="heading">Shopping Cart</h1>

   <form action="checkout.php" method="post" id="cart-form">
      <table>
         <thead>
            <tr>
               <th>Select</th>
               <th>Image</th>
               <th>Name</th>
               <th>Price</th>
               <th>Quantity</th>
               <th>Total Price</th>
               <th>Action</th>
            </tr>
         </thead>
         <tbody>
            <?php 
            $grand_total = 0;
            $stmt = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0){
               while($row = $result->fetch_assoc()){
            ?>
            <tr>
               <td><input type="checkbox" name="selected_items[]" value="<?php echo $row['id']; ?>" class="item-checkbox" data-price="<?php echo $row['price'] * $row['quantity']; ?>"></td>
               <td><img src="uploaded_img/<?php echo htmlspecialchars($row['image']); ?>" height="100" alt=""></td>
               <td><?php echo htmlspecialchars($row['name']); ?></td>
               <td>$<?php echo number_format($row['price'], 2); ?>/-</td>
               <td><?php echo $row['quantity']; ?></td>
               <td>$<?php echo number_format($row['price'] * $row['quantity'], 2); ?>/-</td>
               <td><a href="cart.php?remove=<?php echo $row['id']; ?>" onclick="return confirm('Remove item from cart?')" class="delete-btn"><i class="fas fa-trash"></i> Remove</a></td>
            </tr>
            <?php
                  $grand_total += $row['price'] * $row['quantity'];
               }
            }
            $stmt->close();
            ?>
            <tr class="table-bottom">
               <td><button type="submit" formaction="cart.php" formmethod="post" name="delete_selected" class="delete-btn"><i class="fas fa-trash"></i> Delete Selected</button></td>
               <td><a href="products.php" class="option-btn">Continue Shopping</a></td>
               <td colspan="3">Grand Total</td>
               <td>$<span id="grand-total"><?php echo number_format($grand_total, 2); ?></span>/-</td>
               <td><a href="cart.php?delete_all" onclick="return confirm('Are you sure you want to delete all?');" class="delete-btn"><i class="fas fa-trash"></i> Delete All</a></td>
            </tr>
         </tbody>
      </table>
   </form>

   <div class="checkout-btn">
      <form action="checkout.php" method="post">
         <input type="hidden" name="selected_items_checkout" id="selected_items_checkout" value="">
         <button type="submit" class="btn">Proceed to Checkout</button>
      </form>
   </div>

</section>

</div>

<!-- Custom JS File Link -->
<script src="js/script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
   const checkboxes = document.querySelectorAll('.item-checkbox');
   const grandTotalElement = document.getElementById('grand-total');
   const selectedItemsCheckout = document.getElementById('selected_items_checkout');

   let grandTotal = parseFloat(grandTotalElement.textContent);

   checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function() {
         const itemPrice = parseFloat(this.getAttribute('data-price'));
         if (this.checked) {
            grandTotal += itemPrice;
         } else {
            grandTotal -= itemPrice;
         }
         grandTotalElement.textContent = grandTotal.toFixed(2);
      });
   });

   document.querySelector('.checkout-btn button').addEventListener('click', function(event) {
      event.preventDefault();
      const selectedItems = Array.from(checkboxes)
                                 .filter(checkbox => checkbox.checked)
                                 .map(checkbox => checkbox.value);
      if (selectedItems.length > 0) {
         selectedItemsCheckout.value = JSON.stringify(selectedItems);
         event.target.closest('form').submit();
      } else {
         alert('Please select at least one item to proceed to checkout.');
      }
   });
});
</script>

</body>
</html>
