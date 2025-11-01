<?php
session_start();

// ✅ Dynamically locate db_connect.php (works in root or subfolder)
if (file_exists("db_connect.php")) {
    include("db_connect.php");
} elseif (file_exists("../db_connect.php")) {
    include("../db_connect.php");
} else {
    die("❌ Database connection file not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit;
    }

    $login_success = false;
    $user_role = "";
    $user_data = [];

    // 🔍 Check ADMIN table
    $stmt = $conn->prepare("SELECT full_name, email, password FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin_result = $stmt->get_result();

    if ($admin_result->num_rows === 1) {
        $row = $admin_result->fetch_assoc();
        $stored_hash = $row['password'];

        if (password_verify($password, $stored_hash) || md5($password) === $stored_hash) {
            $login_success = true;
            $user_role = "admin";
            $user_data = $row;
        }
    }

    // 🔍 If not found in admin, check STUDENT table
    if (!$login_success) {
        $stmt = $conn->prepare("SELECT full_name, email, password FROM student WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $student_result = $stmt->get_result();

        if ($student_result->num_rows === 1) {
            $row = $student_result->fetch_assoc();
            $stored_hash = $row['password'];

            if (password_verify($password, $stored_hash) || md5($password) === $stored_hash) {
                $login_success = true;
                $user_role = "student";
                $user_data = $row;
            }
        }
    }

    // 🔍 If not found in admin or student, check FACULTY table
    if (!$login_success) {
        $stmt = $conn->prepare("SELECT full_name, email, password FROM faculty WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $faculty_result = $stmt->get_result();

        if ($faculty_result->num_rows === 1) {
            $row = $faculty_result->fetch_assoc();
            $stored_hash = $row['password'];

            if (password_verify($password, $stored_hash) || md5($password) === $stored_hash) {
                $login_success = true;
                $user_role = "faculty";
                $user_data = $row;
            }
        }
    }

    // ✅ If login successful
    if ($login_success) {
        $_SESSION['email'] = $user_data['email'];
        $_SESSION['full_name'] = $user_data['full_name'];
        $_SESSION['user'] = $user_role;

        if ($user_role === "admin") {
            header("Location: admin/admin_dashboard.php");
        } elseif ($user_role === "student") {
            header("Location: student/student_dashboard.php");
        } elseif ($user_role === "faculty") {
            header("Location: faculty/faculty_dashboard.php");
        }
        exit;
    } else {
        echo "<script>alert('❌ Invalid email or password.'); window.history.back();</script>";
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>
