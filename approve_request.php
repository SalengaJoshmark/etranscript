<?php
session_start();
include("db_connect.php");

// Check admin login
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $request_id = $_GET['id'];

    // Fetch admin info
    $email = $_SESSION['email'];
    $admin_query = mysqli_query($conn, "SELECT admin_id, full_name FROM admin WHERE email='$email'");
    $admin = mysqli_fetch_assoc($admin_query);
    $admin_id = $admin['admin_id'];
    $admin_name = $admin['full_name'];

    // Fetch request purpose
    $req_query = mysqli_query($conn, "SELECT purpose FROM request WHERE request_id='$request_id'");
    $req = mysqli_fetch_assoc($req_query);
    $purpose = $req['purpose'];

    // Update status and assign admin_id
    $update = "UPDATE request SET status='Approved', admin_id='$admin_id' WHERE request_id='$request_id'";
    if (mysqli_query($conn, $update)) {
        // Log transaction
        $action = "Approved";
        $remarks = "Request approved by Admin: $admin_name";
        $insert_log = "INSERT INTO transaction_log (request_id, action, date_time, remarks) 
                       VALUES ('$request_id', '$action', NOW(), '$remarks')";
        mysqli_query($conn, $insert_log);

        echo "<script>alert('Request approved successfully by $admin_name!'); window.location='admin_dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>
