<?php
session_start();
include("db_connect.php");

// Redirect if not logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get admin details
$email = $_SESSION['email'];
$admin_name = "Admin";
$admin_pic = "default_avatar.png";

$stmt = $conn->prepare("SELECT full_name, profile_picture FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['full_name'];
    if (!empty($row['profile_picture'])) {
        $admin_pic = $row['profile_picture'];
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Transaction Logs | E-Transcript System</title>
<style>
  body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(to right,#d6f0e8,#b7d6f2);
    margin: 0;
  }

  /* ===== HEADER ===== */
  .header {
    background: #1e40af;
    color: white;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .header h1 {
    font-size: 22px;
    margin: 0;
  }

  .right-section {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .admin-name {
    font-weight: 500;
    font-size: 15px;
  }

  .admin-pic {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
  }

  .logout {
    background: white;
    color: #1e40af;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s;
  }
  .logout:hover {
    background: #ff0707ff;
    color: white;
  }

  /* ===== CONTENT ===== */
  .container {
    width: 90%;
    margin: 30px auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  h2 { color: #1e3a8a; }

  .nav {
    display: flex;
    gap: 15px;
    background: #e0e7ff;
    padding: 10px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
  }

  .nav a {
    text-decoration: none;
    color: #1e3a8a;
    font-weight: 500;
    transition: 0.3s;
  }

  .nav a:hover, .nav a.active {
    color: #1d4ed8;
    text-decoration: underline;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }

  th, td {
    border: 1px solid #cbd5e1;
    padding: 10px;
    text-align: center;
  }

  th {
    background-color: #e0e7ff;
    color: #1e3a8a;
  }

  tr:hover {
    background: #f1f5f9;
  }
</style>
</head>
<body>

<div class="header">
  <h1>🕒 Transaction Logs</h1>
  <div class="right-section">
    <span class="admin-name"><?= htmlspecialchars($admin_name); ?></span>
    <img src="<?= htmlspecialchars($admin_pic); ?>" alt="Admin" class="admin-pic">
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <div class="nav">
    <a href="admin_dashboard.php">🏠 Home</a>
    <a href="manage_request.php">📂 Manage Requests</a>
    <a href="student_list.php">🎓 Students List</a>
    <a href="transaction_log.php" class="active">🕒 Transaction Logs</a>
    <a href="admin_profile.php">👤 Profile</a>
  </div>

  <h2>Recent Admin Actions</h2>

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
    $sql_logs = "SELECT 
                    l.log_id, 
                    s.full_name, 
                    r.purpose, 
                    l.action, 
                    l.date_time, 
                    l.remarks
                 FROM transaction_log l
                 JOIN request r ON l.request_id = r.request_id
                 JOIN student s ON r.student_id = s.student_id
                 ORDER BY l.date_time DESC";
    $res_logs = mysqli_query($conn, $sql_logs);

    if (mysqli_num_rows($res_logs) > 0) {
        while ($log = mysqli_fetch_assoc($res_logs)) {
            echo "<tr>
                    <td>{$log['log_id']}</td>
                    <td>{$log['full_name']}</td>
                    <td>{$log['purpose']}</td>
                    <td>{$log['action']}</td>
                    <td>{$log['date_time']}</td>
                    <td>{$log['remarks']}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No transaction logs found.</td></tr>";
    }
    ?>
  </table>
</div>

</body>
</html>
