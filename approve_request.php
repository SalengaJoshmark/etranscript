<?php
session_start();
include("db_connect.php");

// Check admin login
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get request ID from URL
if (isset($_GET['id'])) {
    $request_id = $_GET['id'];

    // Fetch admin ID (based on logged-in admin email)
    $email = $_SESSION['email'];
    $admin_query = mysqli_query($conn, "SELECT admin_id FROM admin WHERE email='$email'");
    $admin = mysqli_fetch_assoc($admin_query);
    $admin_id = $admin['admin_id'];

    // Update status and assign admin_id
    $update = "UPDATE request SET status='Approved', admin_id='$admin_id' WHERE request_id='$request_id'";
    if (mysqli_query($conn, $update)) {
        echo "<script>alert('Request approved successfully!'); window.location='admin_dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>
