<?php
session_start();
include("db_connect.php");
require("fpdf186/fpdf.php"); // ✅ Include FPDF library

// ✅ Check admin login
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// ✅ Ensure request ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin/admin_dashboard.php");
    exit();
}

$request_id = $_GET['id'];

// ✅ Fetch admin info
$email = $_SESSION['email'];
$admin_query = mysqli_query($conn, "SELECT admin_id, full_name FROM admin WHERE email='$email'");
$admin = mysqli_fetch_assoc($admin_query);
$admin_id = $admin['admin_id'];
$admin_name = $admin['full_name'];

// ✅ Fetch request details
$req_query = mysqli_query($conn, "
    SELECT r.*, s.full_name AS student_name, s.email, s.course
    FROM request r 
    JOIN student s ON r.student_id = s.student_id
    WHERE r.request_id='$request_id'
");
$req = mysqli_fetch_assoc($req_query);

if (!$req) {
    echo "<script>alert('Request not found!'); window.location='admin/admin_dashboard.php';</script>";
    exit();
}

// ✅ Update request status to Approved
$remarks = "Request approved by $admin_name. Auto-generated transcript approval PDF created.";
$update = "
    UPDATE request 
    SET status='Approved', remarks='$remarks', admin_id='$admin_id' 
    WHERE request_id='$request_id'
";
if (!mysqli_query($conn, $update)) {
    echo "<script>alert('❌ Failed to update request.'); window.location='admin/admin_dashboard.php';</script>";
    exit();
}

// ✅ Log the transaction
$action = "Approved";
$log_msg = "Request approved by Admin: $admin_name";
$purpose = $req['purpose']; // ✅ Get purpose from the request

mysqli_query($conn, "
    INSERT INTO transaction_log (request_id, purpose, action, date_time, remarks)
    VALUES ('$request_id', '$purpose', '$action', NOW(), '$log_msg')
");

// ✅ Generate PDF automatically
$pdfDir = "uploads/generated_pdfs/";
if (!is_dir($pdfDir)) mkdir($pdfDir, 0755, true);
$pdfFile = $pdfDir . "approval_" . $request_id . ".pdf";

$pdf = new FPDF();
$pdf->AddPage();

// Header
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, 'E-Transcript Approval Notice', 0, 1, 'C');
$pdf->Ln(10);

// Request Details
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Request ID: ' . $req['request_id'], 0, 1);
$pdf->Cell(0, 8, 'Student Name: ' . $req['student_name'], 0, 1);
$pdf->Cell(0, 8, 'Email: ' . $req['email'], 0, 1);
$pdf->Cell(0, 8, 'Course: ' . $req['course'], 0, 1);
$pdf->Cell(0, 8, 'Purpose: ' . $req['purpose'], 0, 1);
$pdf->Cell(0, 8, 'Delivery Option: ' . $req['delivery_option'], 0, 1);
$pdf->Ln(5);
$pdf->Cell(0, 8, 'Status: APPROVED', 0, 1);
$pdf->Cell(0, 8, 'Approved By: ' . $admin_name, 0, 1);
$pdf->Cell(0, 8, 'Date Approved: ' . date('Y-m-d H:i:s'), 0, 1);
$pdf->Ln(10);

// Remarks
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Remarks:', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, $remarks);
$pdf->Ln(10);

// Instructions
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Next Steps / Instructions:', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, 
    "1. Your transcript request has been approved.\n" .
    "2. You may download your official transcript from the system or collect it from the registrar’s office.\n" .
    "3. Please bring a valid ID and this approval notice for verification.\n" .
    "4. For any questions, contact registrar@school.edu.\n\n" .
    "Thank you for using the E-Transcript Management System!"
);

// Save PDF
$pdf->Output('F', $pdfFile);

// ✅ Success message and redirect
echo "<script>alert('✅ Request approved and PDF generated successfully!'); 
      window.location='admin/admin_dashboard.php';</script>";
exit();
?>
