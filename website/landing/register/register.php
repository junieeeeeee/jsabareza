<?php

// Include database connection file
@include 'vintage_db.php';

// Start session
session_start();

// Initialize error array
$errors = [];

// Check if the form is submitted
if (isset($_POST['submit'])) {
    // Sanitize inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];
    $cpass = $_POST['cpassword'];

    // Check if email already exists
    $select = "SELECT * FROM user WHERE email = '$email'";
    $result = mysqli_query($conn, $select);

    if (!$result) {
        // Handle database query error
        $errors[] = 'Database query error: ' . mysqli_error($conn);
    } else {
        if (mysqli_num_rows($result) > 0) {
            $errors[] = 'User already exists!';
        } else {
            // Check if passwords match
            if ($pass != $cpass) {
                $errors[] = 'Passwords do not match!';
            } else {
                // Insert user into database
                $insert = "INSERT INTO user (name, email, password) VALUES ('$name', '$email', '$pass')";
                $insert_result = mysqli_query($conn, $insert);

                if (!$insert_result) {
                    // Handle database insert error
                    $errors[] = 'Database insert error: ' . mysqli_error($conn);
                } else {
                    // Redirect to login page after successful registration
                    header('location: login/login.php');
                    exit(); // Ensure script stops execution after redirection
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form</title> <!-- custom css file link  -->
     <link rel="stylesheet" href="register.css">

    <div class="nav">
    <div class="nav__logo"><a href="#">LASTWHISPER.VTG</a></div>
    <link rel="stylesheet" href="https://db.onlinewebfonts.com/c/11eae19d5201ee5e6b1c2ae903ff4ea6?family=Metal+Vengeance"> <!-- Font Awesome CDN link -->
    <style>
        body{
        background-image: url('login/Page/vtg pics/bg.jpg'); 
         background-size: cover;
         background-position: center;
         background-repeat: no-repeat;
        }
    </style>
        
</head>

<body>


    <div class="form-container">

        <form action="" method="post">
            <h3>Register An Account</h3>
            <?php
            // Display errors
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo '<span class="error-msg">' . $error . '</span>';
                }
            }
            ?>
            <input type="text" name="name" required placeholder="Enter your name">
            <input type="email" name="email" required placeholder="Enter your email">
            <input type="password" name="password" required placeholder="Enter your password">
            <input type="password" name="cpassword" required placeholder="Confirm your password">
            <input type="submit" name="submit" value="Register Now" class="form-btn">
            <p>Already have an account? <a href= "login/login.php" >Login now</a></p>
        </form>

    </div>
    
</body>

</html>