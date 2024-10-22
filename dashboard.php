<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Dashboard</title>
</head>
<body>
    
    <div class="container" style="background:transparent;">
        <h2 style="color:white"><u>WELCOME TO ROHIT PHOTOGRAPHY BARARA</u></h2>
        <nav>
        <a href="create_quotation.php" class="btn-grad">Create Quotation</a>
        <a href="manage_quotations.php" class="btn-grad">Manage Quotations</a>
        <a href="manage_services.php" class="btn-grad">Manage Services</a>
        <a href="logout.php" class="btn-grad">Logout</a>
        </nav>
    </div>
</body>
</html>
         