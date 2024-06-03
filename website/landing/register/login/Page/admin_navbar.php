<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
   
   
    <link href="css/admin_page.css" rel="stylesheet" type="text/css"/>
    
    
    <style>
        .nav {
            background: var(--green);
            box-shadow: var(--box-shadow);
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }
        
        .nav__logo a {
            font-size: 2.5rem;
            color: var(--white);
            font-weight: bold;
            text-decoration: none;
        }
        
        .nav__list {
            list-style: none;
            display: flex;
            align-items: center;
        }
        
        .nav__list li {
            margin-left: 2rem;
        }
        
        .nav__list a {
            color: var(--white);
            font-size: 1.8rem;
            text-decoration: none;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        
        .nav__list a:hover {
            color: var(--black);
            transform: scale(1.1);
        }
        
        .nav__toggle {
            display: none;
            font-size: 2.5rem;
            color: var(--white);
            cursor: pointer;
        }
        
        @media (max-width:768px) {
            .nav__list {
                display: none;
                flex-direction: column;
                width: 100%;
                background: var(--green);
                position: absolute;
                top: 100%;
                left: 0;
            }
        
            .nav__list.show {
                display: flex;
            }
        
            .nav__list li {
                margin: 1rem 0;
                text-align: center;
            }
        
            .nav__toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
<nav class="nav">
    <div class="nav__logo"><a href="#">LASTWHISPER.VTG</a></div>
    <div class="nav__toggle" onclick="toggleMenu()">☰</div>
    <ul class="nav__list">
        <li><a href="admin_page.php">Home</a></li>
        <li><a href="admin_sum.php">Summary</a></li>
        <li><a href="billing.php">Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<script>
    function toggleMenu() {
        const navList = document.querySelector('.nav__list');
        navList.classList.toggle('show');
    }
</script>
</body>
</html>
