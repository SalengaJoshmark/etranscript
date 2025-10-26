<?php
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $course = trim($_POST['course']);
    $student_number = trim($_POST['student_number']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<script>
            alert('❌ Passwords do not match. Please try again.');
            window.history.back();
        </script>";
        exit();
    }

    // Hash password using MD5 (⚠️ can be upgraded to password_hash later)
    $hashed_password = md5($password);

    // Check for duplicate email or student number
    $checkQuery = "SELECT * FROM student WHERE email='$email' OR student_number='$student_number'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>
            alert('⚠️ Email or Student Number already exists.');
            window.history.back();
        </script>";
        exit();
    }

    // Insert new student account
    $insertQuery = "INSERT INTO student (full_name, course, student_number, email, password) 
                    VALUES ('$full_name', '$course', '$student_number', '$email', '$hashed_password')";

    if (mysqli_query($conn, $insertQuery)) {
        echo "<script>
            alert('✅ Account created successfully! You can now log in.');
            window.location.href = 'index.php';
        </script>";
    } else {
        echo "<script>
            alert('❌ Something went wrong. Please try again.');
            window.history.back();
        </script>";
    }
}
?>
