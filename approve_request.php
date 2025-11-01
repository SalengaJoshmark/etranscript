<?php
session_start();
include("db_connect.php");
require("fpdf186/fpdf.php");

// ✅ Check admin login
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin/admin_dashboard.php");
    exit();
}

$request_id = intval($_GET['id']);

// ✅ Get admin info
$email = $_SESSION['email'];
$admin_query = mysqli_query($conn, "SELECT admin_id, full_name FROM admin WHERE email='$email'");
$admin = mysqli_fetch_assoc($admin_query);
$admin_id = $admin['admin_id'];
$admin_name = $admin['full_name'];

// ✅ Fetch request and student
$req_query = mysqli_query($conn, "
    SELECT r.*, s.full_name AS student_name, s.email, s.course
    FROM request r 
    JOIN student s ON r.student_id = s.student_id
    WHERE r.request_id = '$request_id'
");
$req = mysqli_fetch_assoc($req_query);
if (!$req) {
    echo "<script>alert('Request not found!'); window.location='admin/admin_dashboard.php';</script>";
    exit();
}

// ✅ Update request status
$remarks = "Request approved by $admin_name. Auto-generated PDF created.";
$update = "
    UPDATE request 
    SET status='Approved', remarks='$remarks', admin_id='$admin_id' 
    WHERE request_id='$request_id'
";
mysqli_query($conn, $update);

// ✅ Log
$action = "Approved";
$log_msg = "Request approved by Admin: $admin_name";
mysqli_query($conn, "
    INSERT INTO transaction_log (request_id, purpose, action, date_time, remarks)
    VALUES ('$request_id', '{$req['purpose']}', '$action', NOW(), '$log_msg')
");

// ============================
// 🧠 SMART INSTRUCTION LOGIC
// ============================
$purpose = strtolower(trim($req['purpose']));
$delivery = strtolower(trim($req['delivery_option']));
$instructionText = "";

// --- Purpose-specific ---
if (strpos($purpose, 'transcript') !== false) {
    $instructionText .= "Your Transcript of Records has been reviewed and approved by the registrar.\n";
    $instructionText .= "Ensure that all courses, grades, and remarks are accurate. If any issue arises, kindly report it within 5 working days.\n\n";
} elseif (strpos($purpose, 'good moral') !== false) {
    $instructionText .= "Your Certificate of Good Moral has been approved.\n";
    $instructionText .= "Please make sure all school clearances and disciplinary records are in order before collection.\n\n";
} elseif (strpos($purpose, 'certificate of grades') !== false || strpos($purpose, 'grades') !== false) {
    $instructionText .= "Your Certificate of Grades has been approved.\n";
    $instructionText .= "This serves as an official summary of your academic performance for scholarship, application, or review purposes.\n\n";
} else {
    $instructionText .= "Your requested document (“" . ucfirst($req['purpose']) . "”) has been approved.\n";
    $instructionText .= "Please follow the instructions below for receiving your document.\n\n";
}

// --- Delivery-specific ---
if (strpos($delivery, 'pick') !== false) {
    $instructionText .= "📍 Delivery Option: Pick-Up\n";
    $instructionText .= "Please bring the following when claiming your document:\n";
    $instructionText .= " - Valid student ID or government-issued ID\n";
    $instructionText .= " - This approval notice (printed or digital)\n";
    $instructionText .= " - Any supporting document if specified during your request\n\n";
    $instructionText .= "Pick-up hours are Monday–Friday, 8:00 AM to 4:00 PM at the Registrar’s Office.";
} elseif (strpos($delivery, 'email') !== false) {
    $instructionText .= "📧 Delivery Option: Email\n";
    $instructionText .= "Your approved document will be sent to your registered email address (" . $req['email'] . ") within 24 hours.\n";
    $instructionText .= "Please:\n";
    $instructionText .= " - Check both your inbox and spam folder\n";
    $instructionText .= " - Ensure your email can receive attachments up to 10MB\n";
    $instructionText .= " - Contact registrar@school.edu if you do not receive it after 24 hours\n";
} else {
    $instructionText .= "Your document will be processed according to registrar procedures. Please check your account for updates.";
}

$instructionText .= "\n\nThank you for using the E-Transcript Request System.";

// ============================
// 🧾 PDF GENERATION
// ============================
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

// ✅ Smart Instructions
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Next Steps / Instructions:', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, $instructionText);

// Save PDF
$pdf->Output('F', $pdfFile);

echo "<script>alert('✅ Request approved and smart PDF generated successfully!'); 
      window.location='admin/admin_dashboard.php';</script>";
exit();
?>
