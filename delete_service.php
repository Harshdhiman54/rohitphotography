<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Ensure service ID is passed
if (isset($_GET['id'])) {
    $service_id = $_GET['id'];

    // Prepare and execute the delete statement
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param("i", $service_id);

    if ($stmt->execute()) {
        // Redirect back to manage services page after deletion
        header("Location: manage_services.php");
        exit;
    } else {
        echo "Error deleting service.";
    }
}
?>
