<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['username'] == 'USER' && $_POST['password'] == 'ADMIN') {
        $_SESSION['loggedin'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username is : USER , Password is : ADMIN";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Admin Login</title>
</head>
<body>
    <div class="container" style="background:transparent;">
        <h2>Admin Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required style="color:white; width:50%; padding:13px; border-radius:5px; background:transparent;">
            <input type="password" name="password" placeholder="Password" required style="color:white; width:50%; padding:13px; border-radius:5px; background:transparent">
            <br>
            <button type="submit">Login</button>
        </form>
        <?php if (isset($error)) echo "<b style='color:red;'>$error</b>"; ?>
    </div>
</body>
</html>
