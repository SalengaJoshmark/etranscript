<?php
session_start();
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($email) || empty($password) || empty($role)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit;
    }

    // Choose table based on role
    $table = ($role === "admin") ? "admin" : "student";

    // Prepare secure query
    $stmt = $conn->prepare("SELECT full_name, email, password FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_hash = $row['password'];
        $full_name = $row['full_name'];
        $login_success = false;

        // ✅ Case 1: password_hash() (modern)
        if (password_verify($password, $stored_hash)) {
            $login_success = true;

            // Rehash if outdated
            if (password_needs_rehash($stored_hash, PASSWORD_DEFAULT)) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE $table SET password = ? WHERE email = ?");
                $update_stmt->bind_param("ss", $new_hash, $email);
                $update_stmt->execute();
            }

        // ✅ Case 2: MD5 legacy
        } elseif (md5($password) === $stored_hash) {
            $login_success = true;

            // Upgrade MD5 → password_hash()
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE $table SET password = ? WHERE email = ?");
            $update_stmt->bind_param("ss", $new_hash, $email);
            $update_stmt->execute();
        }

        // ✅ Login success — set session
        if ($login_success) {
            if ($role === "student") {
                $_SESSION['user'] = 'student';
                $_SESSION['email'] = $row['email'];
                $_SESSION['student_name'] = $full_name;
                header("Location: student_dashboard.php");
            } else {
                $_SESSION['user'] = 'admin';
                $_SESSION['email'] = $row['email'];
                $_SESSION['admin_name'] = $full_name;
                header("Location: admin_dashboard.php");
            }
            exit;
        } else {
            echo "<script>alert('❌ Incorrect password. Please try again.'); window.history.back();</script>";
            exit;
        }

    } else {
        echo "<script>alert('❌ No account found with that email.'); window.history.back();</script>";
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit;
}
?>
