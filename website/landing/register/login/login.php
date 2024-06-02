<?php
session_start();
@include 'page/vintage_db.php';

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    $select = "SELECT * FROM user WHERE email = '$email' AND password = '$pass'";

    $result = mysqli_query($conn, $select);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $user_id = $row['id'];
        
        if ($row['user_type'] == 'a') {
            $_SESSION['admin_name'] = $row['name'];
            $_SESSION['user_id'] = $user_id;
            $_SESSION['admin'] = true; // Set session variable for admin
            header('location: page/admin_page.php');
            exit;
        } elseif ($row['user_type'] == 'u') {
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_id'] = $user_id;
            header('location: page/products.php');
            exit;
        }
    } else {
        $error = 'Incorrect email or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login Form</title>
   <link rel="stylesheet" href="login.css">
   <style>
      body {
         background-image: url('page/vtg pics/bg.jpg'); 
         background-size: cover;
         background-position: center;
         background-repeat: no-repeat;
      }
   </style>
</head>
<body>
<div class="nav">
   <div class="nav__logo"><a href="#">LASTWHISPER.VTG</a></div>
<link href="https://db.onlinewebfonts.com/c/11eae19d5201ee5e6b1c2ae903ff4ea6?family=Metal+Vengeance" rel="stylesheet"></div>

<div class="form-container">
   <form action="" method="post">
      <h3>Login</h3>
      <?php if (isset($error)) : ?>
         <span class="error-msg"><?php echo $error; ?></span>
      <?php endif; ?>
      <input type="email" name="email" required placeholder="Enter your email">
      <input type="password" name="password" required placeholder="Enter your password">
      <input type="submit" name="submit" value="Login Now" class="form-btn">
      <p>Don't have an account? <a href="../register.php">Register</a></p>
   </form>
</div>

</body>
</html>
