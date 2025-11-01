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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $remarks_input = mysqli_real_escape_string($conn, $_POST['remarks']);
        $remarks = "Rejected by Admin: $admin_name — Reason: $remarks_input";

        // Fetch request purpose
        $req_query = mysqli_query($conn, "SELECT purpose FROM request WHERE request_id='$request_id'");
        $req = mysqli_fetch_assoc($req_query);
        $purpose = $req['purpose'];

        // Update status
        $update = "UPDATE request SET status='Rejected', admin_id='$admin_id' WHERE request_id='$request_id'";
        if (mysqli_query($conn, $update)) {
            // Insert into log
            $action = "Rejected";
            $insert_log = "INSERT INTO transaction_log (request_id, action, date_time, remarks) 
                           VALUES ('$request_id', '$action', NOW(), '$remarks')";
            mysqli_query($conn, $insert_log);

            echo "<script>alert('Request rejected successfully by $admin_name!'); window.location='admin/admin_dashboard.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
        exit();
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Reject Request | E-Transcript</title>
        <style>
            body {
                font-family: "Poppins", sans-serif;
                background: #f0f4ff;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            form {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                width: 400px;
                text-align: center;
            }
            textarea {
                width: 100%;
                height: 100px;
                padding: 10px;
                border-radius: 6px;
                border: 1px solid #cbd5e1;
                margin-top: 10px;
                font-family: inherit;
                resize: none;
            }
            button {
                background: #dc2626;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                margin-top: 15px;
                transition: 0.3s;
            }
            button:hover {
                background: #b91c1c;
            }
        </style>
    </head>
    <body>
        <form method="POST">
            <h2>Reject Request</h2>
            <p>Please provide a reason for rejection:</p>
            <textarea name="remarks" placeholder="Enter remarks..." required></textarea>
            <br>
            <button type="submit">Submit</button>
        </form>
    </body>
    </html>
    <?php
} else {
    header("Location: admin/admin_dashboard.php");
    exit();
}
?>
