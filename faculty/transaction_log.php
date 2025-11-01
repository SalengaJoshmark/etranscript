<?php
session_start();
include("../db_connect.php");

// ✅ Redirect if not logged in as faculty
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'faculty') {
    header("Location: ../index.php");
    exit();
}

// ✅ Get faculty info
$email = $_SESSION['email'];
$faculty_name = "Faculty Member";
$faculty_pic = "../uploads/profile_pics/default_avatar.png";
$faculty_department = "";
$faculty_dept_id = 0;

// ✅ Fetch faculty details with department_id
$stmt = $conn->prepare("SELECT full_name, profile_picture, department, department_id FROM faculty WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $faculty_name = $row['full_name'];
    $faculty_department = $row['department'];
    $faculty_dept_id = $row['department_id'];
    if (!empty($row['profile_picture'])) {
        $faculty_pic = "../uploads/profile_pics/" . basename($row['profile_picture']);
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Transaction Logs | Faculty</title>
<style>
body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(to right,#d6f0e8,#b7d6f2);
    margin:0;
    color:#1e293b;
}

.navbar {
    background:#1e40af;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:12px 40px;
    color:white;
}
.navbar .links a {
    color:white;
    text-decoration:none;
    margin-right:20px;
    font-weight:500;
    transition:0.3s;
}
.navbar .links a:hover, .navbar .links a.active { color:#fffccc; text-decoration:underline; }
.logout { background:white; color:#1e40af; padding:6px 14px; border-radius:6px; text-decoration:none; font-weight:600; }

.container {
    width:90%;
    margin:30px auto;
    background:white;
    padding:25px 35px 35px 35px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    position:relative;
}

.faculty-pic {
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #fff;
    position:absolute;
    top:20px;
    right:20px;
}

h2 { color:#1e3a8a; margin-bottom:15px; }

table {
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
    font-size:14px;
}
th, td { border:1px solid #cbd5e1; padding:10px; text-align:center; }
th { background-color:#e0e7ff; color:#1e3a8a; }
tr:hover { background:#f1f5f9; }

.section-title {
    font-size:16px;
    font-weight:600;
    margin-top:25px;
    margin-bottom:10px;
    color:#1e40af;
}
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">E-Scription</div>
    <div class="links">
        <a href="faculty_dashboard.php">🏠 Home</a>
        <a href="department_requests.php">📄 Department Requests</a>
        <a href="transaction_log.php" class="active">🕒 Transaction Logs</a>
        <a href="faculty_profile.php">👤 Profile</a>
    </div>
    <a href="../logout.php" class="logout">Logout</a>
</div>

<div class="container">
    <img src="<?= htmlspecialchars($faculty_pic); ?>" alt="Faculty" class="faculty-pic">

    <h2>Department Transaction Logs</h2>
    <div class="section-title">📌 Recent Actions for <?= htmlspecialchars($faculty_department); ?></div>

    <table>
        <tr>
            <th>Log ID</th>
            <th>Student Name</th>
            <th>Purpose</th>
            <th>Action</th>
            <th>Date & Time</th>
            <th>Remarks</th>
        </tr>

        <?php
        // ✅ Filter logs only for students in the same department as the faculty
        $sql_logs = "
            SELECT 
                l.log_id,
                s.full_name AS student_name,
                l.purpose,
                l.action,
                l.date_time,
                l.remarks
            FROM transaction_log l
            INNER JOIN request r ON l.request_id = r.request_id
            INNER JOIN student s ON r.student_id = s.student_id
            WHERE s.department_id = ?
            ORDER BY l.date_time DESC
        ";

        $stmt = $conn->prepare($sql_logs);
        $stmt->bind_param("i", $faculty_dept_id);
        $stmt->execute();
        $res_logs = $stmt->get_result();

        if ($res_logs && $res_logs->num_rows > 0) {
            while ($log = $res_logs->fetch_assoc()) {
                echo "<tr>
                        <td>{$log['log_id']}</td>
                        <td>{$log['student_name']}</td>
                        <td>{$log['purpose']}</td>
                        <td>{$log['action']}</td>
                        <td>{$log['date_time']}</td>
                        <td>{$log['remarks']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No transaction logs found for your department.</td></tr>";
        }
        $stmt->close();
        ?>
    </table>
</div>

</body>
</html>
