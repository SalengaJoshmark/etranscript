<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user']) || $_SESSION['user'] != 'student') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $purpose = ($_POST['purpose'] == "Other") ? $_POST['custom_purpose'] : $_POST['purpose'];
    $delivery_option = $_POST['delivery_option'];
    $remarks = !empty($_POST['remarks']) ? $_POST['remarks'] : NULL;
    $date_needed = !empty($_POST['date_needed']) ? $_POST['date_needed'] : NULL; // ✅ added line

    // ✅ include date_needed in insert
    $sql = "INSERT INTO request (student_id, purpose, delivery_option, remarks, date_needed, request_date, status)
            VALUES ('$student_id', '$purpose', '$delivery_option', " . 
            ($remarks ? "'$remarks'" : "NULL") . ", " . 
            ($date_needed ? "'$date_needed'" : "NULL") . ", NOW(), 'Pending')";

    if (mysqli_query($conn, $sql)) {
        // ✅ Set session message for the dashboard
        $_SESSION['success_message'] = "Your request has been submitted successfully! Status: Pending.";
        header("Location: student/student_dashboard.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error submitting request: " . mysqli_error($conn);
        header("Location: student/new_request.php");
        exit();
    }
} else {
    header("Location: student/new_request.php");
    exit();
}
?>
