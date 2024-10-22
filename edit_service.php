<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Fetch the service to edit
if (isset($_GET['id'])) {
    $service_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
}

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_name = $_POST['service_name'];
    $price = $_POST['price'];
    $stmt = $conn->prepare("UPDATE services SET service_name = ?, price = ? WHERE id = ?");
    $stmt->bind_param("sdi", $service_name, $price, $service_id);

    if ($stmt->execute()) {
        // Redirect back to manage services page after editing
        header("Location: manage_services.php");
        exit;
    } else {
        echo "Error updating service.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Edit Service</title>
</head>
<body>
    <div class="container" style="background:transparent;">
        <h2>Edit Service</h2>
        <form method="POST">
            <input type="text" name="service_name" value="<?php echo htmlspecialchars($service['service_name']); ?>" required>
            <input type="number" name="price" value="<?php echo htmlspecialchars($service['price']); ?>" required>
            <button type="submit">Update Service</button>
        </form>

        <!-- Back Button -->
        <div style="margin-top: 20px;">
            <a href="manage_services.php" style="text-decoration: none; color: white; background-color: #007BFF; padding: 10px 20px; border-radius: 5px;">Back</a>
        </div>
    </div>
</body>
</html>
