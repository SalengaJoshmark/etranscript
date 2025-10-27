<?php
session_start();
include("db_connect.php");

// Redirect if not logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Check if email exists in session
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Fetch admin info
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
<title>Admin Dashboard | E-Transcript Request System</title>
<style>
  * { box-sizing: border-box; }
  body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(to right, #d6f0e8, #b7d6f2);
    margin: 0;
    color: #1e293b;
  }

  /* HEADER */
  .header {
    background: #1e40af;
    color: white;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .header h1 { font-size: 22px; margin: 0; }
  .right-section { display: flex; align-items: center; gap: 20px; }
  .clock { font-size: 14px; color: #e0e7ff; }
  .logout {
    background: white; color: #1e40af;
    padding: 8px 16px; border-radius: 6px;
    text-decoration: none; font-weight: 500;
    transition: 0.3s;
  }
  .logout:hover { background: #ff0707ff; color: white; }

  .admin-pic {
    width: 40px; height: 40px;
    border-radius: 50%; object-fit: cover;
    border: 2px solid #fff;
  }

  /* CONTAINER */
  .container { width: 90%; margin: 30px auto; max-width: 1300px; }

  .welcome {
    background: white; border-radius: 10px;
    padding: 25px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
  }
  .welcome h2 { color: #1e3a8a; margin-bottom: 5px; }

  /* NAVIGATION */
  .nav {
    display: flex; gap: 15px;
    background: #e0e7ff;
    padding: 10px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
  }
  .nav a {
    text-decoration: none; color: #1e3a8a;
    font-weight: 500; transition: 0.3s;
  }
  .nav a:hover { color: #1d4ed8; text-decoration: underline; }

  /* SECTION CARD */
  .section-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    padding: 25px;
    margin-bottom: 25px;
  }
  .section-card h2 {
    color: #1e3a8a;
    margin-bottom: 15px;
    font-size: 20px;
  }

  /* TABLE */
  table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 8px;
    overflow: hidden;
  }
  th, td {
    border-bottom: 1px solid #335b90ff;
    padding: 10px;
    text-align: center;
  }
  th {
    background-color: #bbdcfdff;
    color: #1e3a8a;
  }
  tr:hover { background-color: #eff6ff; }

  /* BUTTONS */
  .btn {
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    margin: 0 3px;
    color: white;
    font-weight: 500;
    transition: 0.3s;
    display: inline-block;
  }
  .btn.view { background: #2563eb; }
  .btn.view:hover { background: #1d4ed8; }
  .btn.approve { background: #16a34a; }
  .btn.approve:hover { background: #15803d; }
  .btn.reject { background: #dc2626; }
  .btn.reject:hover { background: #b91c1c; }

  /* Disabled buttons with tooltip */
  .btn.disabled {
    background: #94a3b8 !important;
    cursor: not-allowed;
    pointer-events: none;
    opacity: 0.8;
    position: relative;
  }
  .btn.disabled::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 130%;
    left: 50%;
    transform: translateX(-50%);
    background: #1e3a8a;
    color: #fff;
    padding: 4px 8px;
    border-radius: 5px;
    font-size: 12px;
    opacity: 0;
    white-space: nowrap;
    pointer-events: none;
    transition: opacity 0.3s;
  }
  .btn.disabled:hover::after { opacity: 1; }

  @media (max-width: 768px) {
    .nav { flex-direction: column; align-items: center; }
    table { font-size: 13px; }
  }
</style>
</head>
<body>

<div class="header">
  <h1>📘 E-Transcript Admin Dashboard</h1>
  <div class="right-section">
    <img src="<?= htmlspecialchars($admin_pic); ?>" alt="Admin" class="admin-pic">
    <div class="clock" id="clock"></div>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <div class="welcome">
    <h2>Welcome, <?= htmlspecialchars($admin_name); ?> 👋</h2>
    <p>Manage transcript requests and student accounts efficiently below.</p>
  </div>

  <div class="nav">
    <a href="admin_dashboard.php">🏠 Home</a>
    <a href="manage_request.php">📂 Manage Requests</a>
    <a href="student_list.php">🎓 Students List</a>
    <a href="admin_profile.php">👤 Profile</a>
  </div>

  <!-- Transcript Requests -->
  <div class="section-card">
    <h2>📄 Transcript Requests</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Student Name</th>
        <th>Purpose</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
      <?php
      $sql = "SELECT r.request_id, s.full_name, r.purpose, r.request_date, r.status
              FROM request r
              JOIN student s ON r.student_id = s.student_id
              ORDER BY r.request_date DESC";
      $result = mysqli_query($conn, $sql);

      if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
              $disabled = ($row['status'] == 'Approved' || $row['status'] == 'Rejected');
              $tooltip = $row['status'] == 'Approved' ? 'Already approved' : ($row['status'] == 'Rejected' ? 'Already rejected' : '');
              echo "<tr>
                      <td>{$row['request_id']}</td>
                      <td>{$row['full_name']}</td>
                      <td>{$row['purpose']}</td>
                      <td>{$row['request_date']}</td>
                      <td>{$row['status']}</td>
                      <td>
                        <a href='view_request.php?id={$row['request_id']}' class='btn view'>View</a>
                        <a href='approve_request.php?id={$row['request_id']}' 
                           class='btn approve " . ($disabled ? 'disabled' : '') . "'
                           data-tooltip='$tooltip'>Approve</a>
                        <a href='reject_request.php?id={$row['request_id']}' 
                           class='btn reject " . ($disabled ? 'disabled' : '') . "'
                           data-tooltip='$tooltip'>Reject</a>
                      </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='6'>No requests found.</td></tr>";
      }
      ?>
    </table>
  </div>

  <!-- Registered Students -->
  <div class="section-card">
    <h2>🎓 Registered Students</h2>
    <table>
      <tr>
        <th>Student ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Registered Date</th>
      </tr>
      <?php
      $sql_students = "SELECT student_id, full_name, email, created_at FROM student ORDER BY created_at DESC";
      $res_students = mysqli_query($conn, $sql_students);
      if (mysqli_num_rows($res_students) > 0) {
          while ($student = mysqli_fetch_assoc($res_students)) {
              echo "<tr>
                      <td>{$student['student_id']}</td>
                      <td>{$student['full_name']}</td>
                      <td>{$student['email']}</td>
                      <td>{$student['created_at']}</td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='4'>No students found.</td></tr>";
      }
      ?>
    </table>
  </div>

  <!-- Transaction Logs -->
  <div class="section-card">
    <h2>🕒 Transaction Logs</h2>
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
</div>

<script>
function updateClock() {
  const now = new Date();
  const formatted = now.toLocaleString('en-US', {
    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit', second: '2-digit'
  });
  document.getElementById('clock').innerHTML = formatted;
}
setInterval(updateClock, 1000);
updateClock();
</script>
</body>
</html>
