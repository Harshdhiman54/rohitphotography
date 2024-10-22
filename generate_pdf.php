<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';
require('fpdf/fpdf.php');

// Fetch the quotation details based on ID
if (isset($_GET['id'])) {
    $quotation_id = intval($_GET['id']); // Ensure the ID is an integer

    // Fetch quotation details including event date, advance payment, and due payment
    $sql = "SELECT q.customer_name, q.mobile_number, q.customer_address, q.total_amount, q.event_date, q.advance_payment, q.due_payment,
               s.service_name, s.price
        FROM quotations q
        LEFT JOIN quotation_services qs ON q.id = qs.quotation_id
        LEFT JOIN services s ON qs.service_id = s.id
        WHERE q.id = ?";


    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $quotation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch quotation and services in an associative array
    $quotation = [];
    while ($row = $result->fetch_assoc()) {
        $quotation['customer_name'] = $row['customer_name'];
        $quotation['mobile_number'] = $row['mobile_number'];
        $quotation['customer_address'] = $row['customer_address'];  // Add customer address
        $quotation['total_amount'] = $row['total_amount'];
        $quotation['event_date'] = $row['event_date'];
        $quotation['advance_payment'] = $row['advance_payment'];
        $quotation['due_payment'] = $row['due_payment'];
        $quotation['services'][] = [
            'name' => $row['service_name'],
            'price' => $row['price']
        ];
    }
    

    // Check if data exists
    if (empty($quotation)) {
        die("No such quotation found.");
    }
} else {
    die("Quotation ID is missing.");
}

// Your business details
$business_name = "Rohit Photography Barara"; // Use this for the header
$business_signature = "Owner Signature: Rohit Photography Barara";
$customer_signature = "Customer Signature: __________________";
$mobile_number = "Mobile: +91 9068177415 "; // Update with your mobile number

class PDF extends FPDF
{
    function Header()
    {
        //global $business_name;
        $logoWidth = 250; // Desired logo width
        $logoX = ($this->GetPageWidth() - $logoWidth) / 2; // Centered logo
        
        $this->Image('logo.png', $logoX, 0, $logoWidth); // Main logo

        // Add business name
        //$this->SetFont('Arial', 'B', 14);
        //$this->Cell(0, 10, $business_name, 0, 1, 'C');
        //$this->Ln(10);
        // Main logo
        //$logoWidth = 50; // Adjust the width of the main logo
        //$this->Image('logo.png', 10, 10, $logoWidth); // Place the first logo

        // Additional logo (replace the business name with this)
        $logo2Width = 100; // Adjust the width of the second logo
        $this->Image('second_logo.png', 80, 2, 50); // Adjust X, Y coordinates to position the second logo
        $this->Ln(0); // Adjust this value to give enough space after the logo
    }

    function Footer()
    {
        global $mobile_number;
        $this->SetY(-15); // Adjust position of the footer
        $this->SetFont('Arial', 'I', 10);
        
        // Set left aligned (Mobile Number)
        $this->Cell(0, 10, $mobile_number, 0, 0, 'L');
        
        // Set centered (Today's Date)
        $this->SetX(0); // Reset X position to the left
        $this->Cell(0, 10, 'Date: ' . date('d-m-Y'), 0, 0, 'C');
        
        // Set right aligned (Page Number)
        $this->SetX(-15); // Move X position to the right
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'R');
    }

    // Function to display quotation details and services in a table with Serial Number
    function QuotationDetails($customer_name, $mobile_number, $customer_address, $event_date, $advance_payment, $due_payment, $services, $total_amount)
    {
        $this->SetFont('Arial', '', 12);
        $this->Cell(190, 10, "Customer Name: $customer_name", 0, 1);
        $this->Cell(190, 10, "Mobile Number: $mobile_number", 0, 1);
        $this->Cell(190, 10, "Address: $customer_address", 0, 1);  // Display customer address
        $this->Cell(190, 10, "Event/Function Date: " . date('d-m-Y', strtotime($event_date)), 0, 1); // Event date
        $this->Ln(10);

        // Services provided in table format
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(20, 10, 'S.No.', 1, 0, 'C');  // Serial number column
        $this->Cell(85, 10, 'Service Name', 1, 0, 'C');
        $this->Cell(85, 10, 'Price (INR)', 1, 1, 'C');
        $this->SetFont('Arial', '', 12);

        // Check if services exist and display in a table
        if (!empty($services)) {
            $serial_number = 1; // Initialize serial number
            foreach ($services as $service) {
                $this->Cell(20, 10, $serial_number++, 1, 0, 'C'); // Serial number
                $this->Cell(85, 10, $service['name'], 1, 0, 'C');
                $this->Cell(85, 10, number_format($service['price'], 2), 1, 1, 'C');
            }
        } else {
            $this->Cell(190, 10, 'No services provided.', 1, 1, 'C');
        }

        $this->Ln(5);

        // Total Amount, Advance Payment, Due Payment
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(190, 10, "Total Amount: INR " . number_format($total_amount, 2), 0, 1);
        $this->Cell(190, 10, "Advance Payment: INR " . number_format($advance_payment, 2), 0, 1); // Advance payment
        $this->Cell(190, 10, "Due Payment: INR " . number_format($due_payment, 2), 0, 1); // Due payment
        $this->Ln(10);
    }

    

    // Function to display terms and conditions
    function TermsAndConditions()
    {
        $this->SetFont('times', 'B', 12);
        $this->Cell(0, 10, "Terms and Conditions", 0, 1, 'L');
        $this->Ln(2); // Reduced line gap
        
        $this->SetFont('Arial', '', 10);
        $terms = [
            "1. Booking & Payment: To book a session, we require a 85% deposit, which is non-refundable. You will also need to make the remaining balance before the date of the event. Any further overdue payments attract a 5% fee on each week of the outstanding payment until payment is made in full.",
            "2. Services: Do indicate the service that you want done. For addresses outside Barara, there are extra traveling expenses, and final photographs would be available within 4-6 weeks.",
            "",
            "If you have further questions, do not hesitate to get in touch!"
        ];
        
        foreach ($terms as $line) {
            $this->MultiCell(0, 6, $line); // Set line height to 6
        }
        
        $this->Ln(5); // Adjusted spacing after terms
    }
    function Signatures($owner_signature, $customer_signature)
    {
        $this->Ln(10);
        $this->Cell(95, 10, $owner_signature, 0, 0, 'L');
        $this->Cell(95, 10, $customer_signature, 0, 1, 'R');
        $this->Ln(10);
    }
}

// Generate PDF
// Generate PDF
$pdf = new PDF();
$pdf->AddPage();

// Add quotation details
$pdf->QuotationDetails(
    $quotation['customer_name'],
    $quotation['mobile_number'],
    $quotation['customer_address'],  // Pass the customer address
    $quotation['event_date'], // Event date
    $quotation['advance_payment'], // Advance payment
    $quotation['due_payment'], // Due payment
    $quotation['services'],
    $quotation['total_amount']
);

// Add terms and conditions first
$pdf->TermsAndConditions();

// Add signatures last
$pdf->Signatures($business_signature, $customer_signature);

// Output PDF to browser
$pdf->Output("Quotation_" . $quotation['customer_name'] . ".pdf", 'I');

?>
