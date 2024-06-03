<link rel="stylesheet" href="css/nav_user.css">


<header class="header">
<link rel="stylesheet" href="nav_user.css">

   <div class="flex">

      <a href="#" class="logo">LASTWHISPER.VTG</a>

      <nav class="navbar">
         
         <a href="products.php">view products</a>
         <a href="user_orders.php">Orders</a>
         <?php
      
      // Fetch only the cart items associated with the logged-in user
      $user_id = $_SESSION['user_id'];
      $select_rows = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
      $row_count = mysqli_num_rows($select_rows);

      ?>

      <a href="cart.php" class="cart">cart <span><?php echo $row_count; ?></span> </a>
    
      <div id="menu-btn" class="fas fa-bars"></div>
      
         <a href="logout.php">logout</a>
      </nav>

    

   </div>

</header>
