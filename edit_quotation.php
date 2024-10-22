<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Fetch the quotation to edit
if (isset($_GET['id'])) {
    $quotation_id = $_GET['id'];

    // Fetch the quotation data
    $stmt = $conn->prepare("SELECT * FROM quotations WHERE id = ?");
    $stmt->bind_param("i", $quotation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quotation = $result->fetch_assoc();

    // Fetch the associated services
    $stmt_services = $conn->prepare("SELECT service_id FROM quotation_services WHERE quotation_id = ?");
    $stmt_services->bind_param("i", $quotation_id);
    $stmt_services->execute();
    $services_result = $stmt_services->get_result();
    $selected_services = [];
    while ($row = $services_result->fetch_assoc()) {
        $selected_services[] = $row['service_id'];
    }
}

// Fetch all services for the dropdown
$services = $conn->query("SELECT * FROM services");

// Handle the form submission for updating the quotation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $mobile_number = $_POST['mobile_number'];
    $total_amount = $_POST['total_amount'];
    $services = $_POST['services']; // Array of selected services

    // Update the quotation
    $stmt = $conn->prepare("UPDATE quotations SET customer_name = ?, mobile_number = ?, total_amount = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $customer_name, $mobile_number, $total_amount, $quotation_id);
    if ($stmt->execute()) {
        // First, delete all previous services linked to this quotation
        $stmt_delete_services = $conn->prepare("DELETE FROM quotation_services WHERE quotation_id = ?");
        $stmt_delete_services->bind_param("i", $quotation_id);
        $stmt_delete_services->execute();

        // Insert the updated selected services
        foreach ($services as $service_id) {
            $stmt_insert_service = $conn->prepare("INSERT INTO quotation_services (quotation_id, service_id) VALUES (?, ?)");
            $stmt_insert_service->bind_param("ii", $quotation_id, $service_id);
            $stmt_insert_service->execute();
        }

        // Redirect back to manage quotations page
        header("Location: manage_quotations.php");
        exit;
    } else {
        echo "Error updating quotation: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Edit Quotation</title>
</head>
<body>
    <div class="container">
        <h2>Edit Quotation</h2>
        <form method="POST">
            <input type="text" name="customer_name" value="<?php echo htmlspecialchars($quotation['customer_name']); ?>" required>
            <input type="text" name="mobile_number" value="<?php echo htmlspecialchars($quotation['mobile_number']); ?>" required>

            <h4>Services</h4>
            <select id="service_select" name="services[]" multiple>
                <?php while ($row = $services->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" 
                    <?php echo in_array($row['id'], $selected_services) ? 'selected' : ''; ?>>
                    <?php echo $row['service_name'] . " - INR " . $row['price']; ?>
                </option>
                <?php endwhile; ?>
            </select>

            <p><strong>Total Amount:</strong> INR 
            <input type="number" name="total_amount" value="<?php echo htmlspecialchars($quotation['total_amount']); ?>" required></p>

            <button type="submit">Update Quotation</button>
        </form>

        <!-- Back Button -->
        <div style="margin-top: 20px;">
            <a href="manage_quotations.php" style="text-decoration: none; color: white; background-color: #007BFF; padding: 10px 20px; border-radius: 5px;">Back</a>
        </div>
    </div>
</body>
</html>
