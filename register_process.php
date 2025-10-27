<?php
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'student';

    // Student-only fields
    $course = isset($_POST['course']) ? trim($_POST['course']) : null;
    $student_number = isset($_POST['student_number']) ? trim($_POST['student_number']) : null;

    // Admin-only field
    $username = isset($_POST['username']) ? trim($_POST['username']) : null;

    // ✅ Check password match
    if ($password !== $confirm_password) {
        echo "<script>
            alert('❌ Passwords do not match. Please try again.');
            window.history.back();
        </script>";
        exit();
    }

    // ✅ Securely hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Handle profile picture upload
    $profile_picture = "default_avatar.png";
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/profile_pics/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "gif"];

        if (in_array($imageFileType, $allowed)) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $file_name;
            }
        }
    }

    // ✅ Check duplicates
    if ($role === "student") {
        $checkQuery = "SELECT * FROM student WHERE email='$email' OR student_number='$student_number'";
    } else {
        $checkQuery = "SELECT * FROM admin WHERE email='$email' OR username='$username'";
    }

    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>
            alert('⚠️ Email, Student Number, or Username already exists.');
            window.history.back();
        </script>";
        exit();
    }

    // ✅ Insert into proper table
    if ($role === "student") {
        $insertQuery = "INSERT INTO student (full_name, course, student_number, email, password, profile_picture)
                        VALUES ('$full_name', '$course', '$student_number', '$email', '$hashed_password', '$profile_picture')";
    } else {
        $insertQuery = "INSERT INTO admin (username, full_name, email, password, profile_picture)
                        VALUES ('$username', '$full_name', '$email', '$hashed_password', '$profile_picture')";
    }

    if (mysqli_query($conn, $insertQuery)) {
        echo "<script>
            alert('✅ $role account created successfully! You can now log in.');
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
