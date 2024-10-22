<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture customer details
    $customer_name = $_POST['customer_name'];
    $mobile_number = $_POST['mobile_number'];
    $customer_address = $_POST['customer_address']; // Capture customer address
    $event_date = $_POST['event_date']; // Capture event date
    $advance_payment = $_POST['advance_payment']; // Capture advance payment
    $total_amount = $_POST['total_amount']; // Total amount from the form (calculated by JS)
    $due_payment = $total_amount - $advance_payment; // Calculate due payment
    $services = $_POST['services']; // Array of selected services

    // Insert the quotation into the database, including the customer address
    $sql = "INSERT INTO quotations (customer_name, mobile_number, customer_address, event_date, advance_payment, due_payment, total_amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssdds", $customer_name, $mobile_number, $customer_address, $event_date, $advance_payment, $due_payment, $total_amount);

    if ($stmt->execute()) {
        $quotation_id = $stmt->insert_id; // Get the ID of the inserted quotation

        // Insert selected services into the quotation_services table
        if (!empty($services)) {
            foreach ($services as $service_id) {
                $sql_service = "INSERT INTO quotation_services (quotation_id, service_id) VALUES (?, ?)";
                $stmt_service = $conn->prepare($sql_service);
                $stmt_service->bind_param("ii", $quotation_id, $service_id);
                $stmt_service->execute();
                $stmt_service->close();
            }
        }

        echo "Quotation created successfully!";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}

// Fetch services for the dropdown
$services = $conn->query("SELECT * FROM services");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Create Quotation</title>
</head>
<body>
    <div class="container" style="background:transparent;">
        <h2>Create Quotation</h2>
        <form method="POST">
            <!-- Customer Name, Mobile Number, and Address Fields -->
            Customer Name:
            <input type="text" name="customer_name" placeholder="Customer Name" required style="width:50%; background:transparent; color:white">
            <br>
            Mobile Number:
            <input type="text" name="mobile_number" placeholder="Mobile Number" required style="width:50%; background:transparent; color:white">
            <br>
            Customer Address:
            <textarea name="customer_address" placeholder="Customer Address" required style="width:50%; background:transparent; color:white"></textarea>

            <!-- Event Date Field -->
            <br><br>
            Event Date:
            <input type="date" name="event_date" required style="width:20%; height:30px; background:transparent; color:white; border-radius:5px">
            
            <!-- Services Selection -->
            <h4>Services</h4>
            <select id="service_select" style="color:black; width:50%; padding:13px; border-radius:5px; background:transparent">
                <?php while ($row = $services->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['price']; ?>">
                    <?php echo $row['service_name'] . " - INR " . $row['price']; ?>
                </option>
                <?php endwhile; ?>
            </select>
            <button type="button" onclick="addService()">Add Service</button>

            <div id="services_list"></div>

            <!-- Advance Payment Field -->
            <p><strong>Advance Payment:</strong> 
                INR <input type="number" name="advance_payment" id="advance_payment" value="0" step="0.01" required style="width:20%; background:transparent; color:white;">
            </p>

            <!-- Total Amount -->
            <p><strong>Total Amount:</strong> INR <span id="total_amount_display">0</span></p>
            <input type="hidden" name="total_amount" id="total_amount" value="0">

            <!-- Due Payment Calculation (Auto-calculated) -->
            <p><strong>Due Payment:</strong> INR <span id="due_payment_display">0</span></p>

            <button type="submit">Create Quotation</button>
        </form>

        <!-- Back Button -->
        <div style="margin-top: 20px;">
            <a href="dashboard.php" style="text-decoration: none; color: white; background-color: #007BFF; padding: 10px 20px; border-radius: 5px;">Back</a>
        </div>
    </div>

    <script>
        let totalAmount = 0;
        const selectedServices = new Set(); // Use a Set to store selected service IDs for uniqueness

        function addService() {
            const serviceSelect = document.getElementById('service_select');
            const selectedService = serviceSelect.options[serviceSelect.selectedIndex];
            const serviceId = selectedService.value;
            const servicePrice = parseFloat(selectedService.getAttribute('data-price'));

            // Check if the service is already selected
            if (selectedServices.has(serviceId)) {
                alert('This service is already selected!');
                return; // Prevent adding the same service again
            }

            // Add service to the set (to prevent duplicates)
            selectedServices.add(serviceId);

            // Add service to the list
            const servicesList = document.getElementById('services_list');
            const serviceItem = document.createElement('div');
            serviceItem.setAttribute('data-id', serviceId);
            serviceItem.setAttribute('data-price', servicePrice);
            serviceItem.innerHTML = `Service: ${selectedService.text} 
                <button type="button" onclick="removeService(this, ${servicePrice}, ${serviceId})">Remove</button>`;
            servicesList.appendChild(serviceItem);

            // Add hidden input for service to be submitted in the form
            const serviceInput = document.createElement('input');
            serviceInput.type = 'hidden';
            serviceInput.name = 'services[]';
            serviceInput.value = serviceId;
            servicesList.appendChild(serviceInput);

            // Update total amount
            totalAmount += servicePrice;
            document.getElementById('total_amount_display').innerText = totalAmount.toFixed(2);
            document.getElementById('total_amount').value = totalAmount.toFixed(2);

            // Update due payment calculation
            updateDuePayment();
        }

        function removeService(button, servicePrice, serviceId) {
            const serviceItem = button.parentElement;

            // Remove the service from the list
            serviceItem.remove();

            // Remove hidden input for the service
            const hiddenInput = document.querySelector(`input[name="services[]"][value="${serviceId}"]`);
            hiddenInput.remove();

            // Remove the service from the set
            selectedServices.delete(serviceId);

            // Update total amount
            totalAmount -= servicePrice;
            document.getElementById('total_amount_display').innerText = totalAmount.toFixed(2);
            document.getElementById('total_amount').value = totalAmount.toFixed(2);

            // Update due payment calculation
            updateDuePayment();
        }

        function updateDuePayment() {
            const advancePayment = parseFloat(document.getElementById('advance_payment').value);
            const duePayment = totalAmount - advancePayment;
            document.getElementById('due_payment_display').innerText = duePayment.toFixed(2);
        }

        // Update due payment whenever the advance payment is changed
        document.getElementById('advance_payment').addEventListener('input', updateDuePayment);
    </script>
</body>
</html>
