<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'student';

    $course_id = isset($_POST['course']) ? intval($_POST['course']) : null;
    $department_id = isset($_POST['department']) ? intval($_POST['department']) : null;
    $username = isset($_POST['username']) ? trim($_POST['username']) : null;

    if ($password !== $confirm_password) {
        echo "<script>alert('❌ Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Handle profile picture
    $profile_picture = "uploads/profile_pics/default_avatar.png";
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/profile_pics/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed)) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = $target_file;
            }
        }
    }

    // ✅ Auto-generate student number
    if ($role === "student") {
        $yearPart = date("y");
        $prefix = $yearPart . "-";
        $query = "SELECT student_number FROM student WHERE student_number LIKE '$prefix%' ORDER BY student_id DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($row = mysqli_fetch_assoc($result)) {
            $lastNumber = intval(substr($row['student_number'], 3));
            $nextNumber = str_pad($lastNumber + 1, 3, "0", STR_PAD_LEFT);
        } else {
            $nextNumber = "001";
        }
        $student_number = $prefix . $nextNumber;
    }

    // ✅ Duplicate check
    if ($role === "student") {
        $checkQuery = "SELECT * FROM student WHERE email='$email' OR student_number='$student_number'";
    } elseif ($role === "faculty") {
        $checkQuery = "SELECT * FROM faculty WHERE email='$email'";
    } else {
        $checkQuery = "SELECT * FROM admin WHERE email='$email' OR username='$username'";
    }

    $checkResult = mysqli_query($conn, $checkQuery);
    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>alert('⚠️ Email or ID already exists.'); window.history.back();</script>";
        exit();
    }

    // ✅ Role-based limits
    if ($role === "admin") {
        $limitQuery = "SELECT COUNT(*) AS count FROM admin";
        $maxAllowed = 1;
    } elseif ($role === "faculty") {
        $limitQuery = "SELECT COUNT(*) AS count FROM faculty";
        $maxAllowed = 8;
    } else {
        $limitQuery = "";
        $maxAllowed = PHP_INT_MAX;
    }

    if (!empty($limitQuery)) {
        $res = mysqli_query($conn, $limitQuery);
        $data = mysqli_fetch_assoc($res);
        if ($data['count'] >= $maxAllowed) {
            echo "<script>alert('⚠️ Limit reached for $role accounts.'); window.history.back();</script>";
            exit();
        }
    }

    // ✅ Insert based on role with Pending status
    if ($role === "student") {
        $courseQuery = mysqli_query($conn, "SELECT course_name, department_id FROM course WHERE course_id = '$course_id'");
        $courseData = mysqli_fetch_assoc($courseQuery);

        if (!$courseData) {
            echo "<script>alert('❌ Invalid course selection.'); window.history.back();</script>";
            exit();
        }

        $course_name = mysqli_real_escape_string($conn, $courseData['course_name']);
        $department_id = $courseData['department_id'];

        $insertQuery = "INSERT INTO student (full_name, course_id, course, department_id, student_number, email, password, profile_picture, status)
                        VALUES ('$full_name', '$course_id', '$course_name', '$department_id', '$student_number', '$email', '$hashed_password', '$profile_picture', 'Pending')";

    } elseif ($role === "faculty") {
        $deptQuery = mysqli_query($conn, "SELECT department_name FROM department WHERE department_id = '$department_id'");
        $deptData = mysqli_fetch_assoc($deptQuery);

        if (!$deptData) {
            echo "<script>alert('❌ Invalid department selection.'); window.history.back();</script>";
            exit();
        }

        $department_name = mysqli_real_escape_string($conn, $deptData['department_name']);

        $insertQuery = "INSERT INTO faculty (full_name, department_id, department, email, password, profile_picture, status)
                        VALUES ('$full_name', '$department_id', '$department_name', '$email', '$hashed_password', '$profile_picture', 'Pending')";
    } else {
        $insertQuery = "INSERT INTO admin (username, full_name, email, password, profile_picture)
                        VALUES ('$username', '$full_name', '$email', '$hashed_password', '$profile_picture')";
    }

    // ✅ Execute insert
    if (mysqli_query($conn, $insertQuery)) {
        echo "<script>alert('✅ Registration successful! Your account is pending admin approval.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('❌ Something went wrong: " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
}
?>
