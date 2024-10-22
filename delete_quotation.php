<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Ensure quotation ID is passed
if (isset($_GET['id'])) {
    $quotation_id = $_GET['id'];

    // Start a transaction since we need to delete from two tables
    $conn->begin_transaction();

    try {
        // Delete from quotation_services table first to maintain integrity
        $stmt_services = $conn->prepare("DELETE FROM quotation_services WHERE quotation_id = ?");
        $stmt_services->bind_param("i", $quotation_id);
        $stmt_services->execute();

        // Delete the quotation from the quotations table
        $stmt_quotation = $conn->prepare("DELETE FROM quotations WHERE id = ?");
        $stmt_quotation->bind_param("i", $quotation_id);
        $stmt_quotation->execute();

        // Commit the transaction
        $conn->commit();

        // Redirect back to the quotations management page after deletion
        header("Location: manage_quotations.php");
        exit;
    } catch (Exception $e) {
        // Rollback transaction in case of an error
        $conn->rollback();
        echo "Error deleting quotation: " . $conn->error;
    }
}
?>
