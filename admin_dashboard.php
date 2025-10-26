<?php
session_start();
include("db_connect.php");

// redirect if not logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// check if email exists in session
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Fetch admin name
$email = $_SESSION['email'];
$admin_name = "Admin";

$stmt = $conn->prepare("SELECT full_name FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['full_name'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - E-Transcript Request System</title>
<style>
  body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(to right, #d6f0e8, #b7d6f2);
    margin: 0;
  }
  .header {
    background-color: #1e40af;
    color: white;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .header h1 { font-size: 22px; margin: 0; }
  .logout {
    background: white;
    color: #1e40af;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
  }
  .logout:hover { background: #e0e7ff; }

  /* CLOCK */
  .clock {
    font-size: 14px;
    color: white;
    margin-right: 20px;
  }

  .right-section {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .container {
    background: #fff;
    width: 85%;
    margin: 40px auto;
    border-radius: 8px;
    padding: 25px 35px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }
  h2 { color: #1e3a8a; margin-bottom: 10px; }
  .nav {
    display: flex;
    gap: 20px;
    background: #e0e7ff;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
  }
  .nav a {
    text-decoration: none;
    color: #1e3a8a;
    font-weight: 500;
  }
  .nav a:hover { text-decoration: underline; }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }
  th, td {
    border: 1px solid #e2e8f0;
    padding: 10px;
    text-align: center;
  }
  th { background-color: #f1f5f9; }
  .btn {
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    margin: 0 3px;
  }
  .approve { background-color: #16a34a; color: white; }
  .reject { background-color: #dc2626; color: white; }
  .view { background-color: #2563eb; color: white;}
  .view:hover {background-color: #1d4ed8;}

</style>
</head>
<body>

<div class="header">
  <h1>Admin Dashboard</h1>
  <div class="right-section">
    <div class="clock" id="clock"></div>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?> 👋</h2>
  <p>Manage and monitor transcript requests and student accounts.</p>

  <div class="nav">
    <a href="#">Home</a>
    <a href="#">Manage Requests</a>
    <a href="#">Students List</a>
    <a href="#">Profile</a>
  </div>

  <!-- Transcript Requests Table -->
  <h2>Transcript Requests</h2>
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
            echo "<tr>
                    <td>{$row['request_id']}</td>
                    <td>{$row['full_name']}</td>
                    <td>{$row['purpose']}</td>
                    <td>{$row['request_date']}</td>
                    <td>{$row['status']}</td>
                    <td>
                      <a href='view_request.php?id={$row['request_id']}' class='btn view'>View</a>
                      <a href='approve_request.php?id={$row['request_id']}' class='btn approve'>Approve</a>
                      <a href='reject_request.php?id={$row['request_id']}' class='btn reject'>Reject</a>
                    </td>

                  </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No requests found.</td></tr>";
    }
    ?>
  </table>

  <!-- Students List Table -->
  <h2>Registered Students</h2>
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

<!-- REAL-TIME CLOCK SCRIPT -->
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
