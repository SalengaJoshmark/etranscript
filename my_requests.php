<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user']) || $_SESSION['user'] != 'student') {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];
$sql = "SELECT * FROM student WHERE email='$email'";
$result = mysqli_query($conn, $sql);
$student = mysqli_fetch_assoc($result);
$student_name = $student['full_name'];
$student_id = $student['student_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Requests - E-Transcript System</title>
<style>
  body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(to right,#d6f0e8,#b7d6f2);
    margin: 0;
  }

  .navbar {
    background: #1e40af;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 40px;
    color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
  }

  .navbar .logo { font-weight: 600; font-size: 20px; }
  .navbar .links { display: flex; gap: 25px; }
  .navbar a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s;
    padding: 6px 10px;
    border-radius: 6px;
  }
  .navbar a:hover { background: #3b82f6; }
  .logout { background:white; color:#1e40af; padding:6px 14px; border-radius:6px; text-decoration:none; font-weight:600; }

  .container {
    background: white;
    width: 85%;
    margin: 50px auto;
    border-radius: 10px;
    padding: 40px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  h2 { color: #1e3a8a; }
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

  .status {
    font-weight: bold;
  }

  .status.Pending { color: orange; }
  .status.Approved { color: green; }
  .status.Rejected { color: red; }
</style>
</head>
<body>

<div class="navbar">
  <div class="logo">E-Transcript System</div>
  <div class="links">
    <a href="student_dashboard.php">🏠 Home</a>
    <a href="new_request.php">📝 New Request</a>
    <a href="my_requests.php" style="background:#3b82f6;">📄 My Requests</a>
    <a href="profile.php">👤 Profile</a>
  </div>
  <div class="right">
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <h2>📄 My Transcript Requests</h2>

  <table>
    <tr>
      <th>Request ID</th>
      <th>Purpose</th>
      <th>Date Requested</th>
      <th>Status</th>
      <th>Remarks</th>
      <th>Download</th>
    </tr>
    <?php
    $sql = "SELECT * FROM request WHERE student_id='$student_id' ORDER BY request_date DESC";
    $res = mysqli_query($conn, $sql);

    if (mysqli_num_rows($res) > 0) {
      while ($r = mysqli_fetch_assoc($res)) {
        $status = htmlspecialchars($r['status']);
        echo "<tr>
                <td>{$r['request_id']}</td>
                <td>{$r['purpose']}</td>
                <td>{$r['request_date']}</td>
                <td class='status {$status}'>{$status}</td>
                <td>" . ($r['remarks'] ?? '—') . "</td>";

        if ($r['status'] == 'Approved') {
          echo "<td><a href='uploads/{$r['file_name']}' style='color:#1e40af;text-decoration:none;'>Download</a></td>";
        } else {
          echo "<td>—</td>";
        }

        echo "</tr>";
      }
    } else {
      echo "<tr><td colspan='6'>No transcript requests found.</td></tr>";
    }
    ?>
  </table>
</div>

</body>
</html>
