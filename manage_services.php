<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_service'])) {
        $service_name = $_POST['service_name'];
        $price = $_POST['price'];
        $stmt = $conn->prepare("INSERT INTO services (service_name, price) VALUES (?, ?)");
        $stmt->bind_param("sd", $service_name, $price);
        $stmt->execute();
    }
}

$services = $conn->query("SELECT * FROM services");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Manage Services</title>
</head>
<body>
    <div class="container" style="background:transparent;">
        <h2>Manage Services</h2>
        <form method="POST">
            <input type="text" name="service_name" placeholder="Service Name" required>
            <input type="number" name="price" placeholder="Price (INR)" required>
            <button type="submit" name="add_service">Add Service</button>
        </form>
        <table>
            <tr>
                <th>Service Name</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $services->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['service_name']; ?></td>
                <td><?php echo $row['price']; ?></td>
                <td>
                <a href="edit_service.php?id=<?php echo $row['id']; ?>">Edit</a>
                <a href="delete_service.php?id=<?php echo $row['id']; ?>">Delete</a>

                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <!-- Back Button -->
<div style="margin-top: 20px;">
    <a href="dashboard.php" style="text-decoration: none; color: white; background-color: #007BFF; padding: 10px 20px; border-radius: 5px;">Back</a>
</div>

    </div>
</body>
</html>
