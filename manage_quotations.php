<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Fetch all quotations with their associated services
$sql = "SELECT q.id, q.customer_name, q.mobile_number, q.total_amount, q.event_date, q.advance_payment, q.due_payment,
               GROUP_CONCAT(s.service_name SEPARATOR ', ') AS services
        FROM quotations q
        LEFT JOIN quotation_services qs ON q.id = qs.quotation_id
        LEFT JOIN services s ON qs.service_id = s.id
        GROUP BY q.id";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('Prepare failed: ' . $conn->error); // Output the error
}

$stmt->execute();
$result = $stmt->get_result();

// Check if any quotations are found
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Manage Quotations</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            text-align: left;
        }
        th, td {
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .action-links a {
            margin-right: 10px;
            text-decoration: none;
            color: #007BFF;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container" style="background:transparent;">
        <h2>Manage Quotations</h2>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Mobile Number</th>
                        <th>Event Date</th>
                        <th>Total Amount (INR)</th>
                        <th>Advance Payment (INR)</th>
                        <th>Due Payment (INR)</th>
                        <th>Services</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['mobile_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                        <td><?php echo number_format($row['total_amount'], 2); ?></td>
                        <td><?php echo number_format($row['advance_payment'], 2); ?></td>
                        <td><?php echo number_format($row['due_payment'], 2); ?></td>
                        <td><?php echo !empty($row['services']) ? htmlspecialchars($row['services']) : 'No services provided'; ?></td>
                        <td class="action-links">
                            <a href="generate_pdf.php?id=<?php echo $row['id']; ?>">Generate PDF</a>
                            <a href="edit_quotation.php?id=<?php echo $row['id']; ?>">Edit</a>
                            <a href="delete_quotation.php?id=<?php echo $row['id']; ?>">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No quotations found.</p>
        <?php endif; ?>
        <div style="margin-top: 20px;">
            <a href="dashboard.php" style="text-decoration: none; color: white; background-color: #007BFF; padding: 10px 20px; border-radius: 5px;">Back</a>
        </div>
    </div>
</body>
</html>
