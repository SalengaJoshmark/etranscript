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
<title>Student Dashboard - E-Transcript System</title>
<style>
  body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(to right, #d6f0e8, #b7d6f2);
    margin: 0;
  }

  /* ===== NAVIGATION BAR ===== */
  .navbar {
    background: #1e40af;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 40px;
    color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
  }

  .navbar .logo {
    font-weight: 600;
    font-size: 20px;
  }

  .navbar .links {
    display: flex;
    gap: 25px;
  }

  .navbar a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s;
    padding: 6px 10px;
    border-radius: 6px;
  }

  .navbar a:hover {
    background: #3b82f6;
  }

  .navbar .right {
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .logout {
    background: white;
    color: #1e40af;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
  }

  .logout:hover {
    background: #e2e8f0;
  }

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

  .alert {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 6px;
    font-weight: 500;
  }

  .success { background: #d1fae5; color: #065f46; }
  .error { background: #fee2e2; color: #991b1b; }
</style>
</head>
<body>

<div class="navbar">
  <div class="logo">E-Transcript System</div>
  <div class="links">
    <a href="student_dashboard.php">🏠 Home</a>
    <a href="new_request.php">📝 New Request</a>
    <a href="my_requests.php">📄 My Requests</a>
    <a href="student_profile.php">👤 Profile</a>
  </div>
  <div class="right">
    <span id="clock"></span>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <div style="display:flex;align-items:center;gap:20px;margin-bottom:15px;">
  <?php if (!empty($student['profile_picture']) && file_exists($student['profile_picture'])): ?>
    <img src="<?= htmlspecialchars($student['profile_picture']) ?>" 
         alt="Profile Picture" 
         style="width:80px;height:80px;border-radius:50%;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,0.2);">
  <?php else: ?>
    <img src="assets/default-avatar.png" 
         alt="Default Avatar" 
         style="width:80px;height:80px;border-radius:50%;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,0.2);">
  <?php endif; ?>

  <div>
    <h2 style="margin:0;">Welcome, <?= htmlspecialchars($student_name) ?> 👋</h2>
    <p style="margin:5px 0 0;color:#374151;">You are logged into the E-Transcript Request System.</p>
  </div>
</div>


 


  <?php
  if (isset($_SESSION['success_message'])) {
      echo "<div class='alert success'>{$_SESSION['success_message']}</div>";
      unset($_SESSION['success_message']);
  }

  if (isset($_SESSION['error_message'])) {
      echo "<div class='alert error'>{$_SESSION['error_message']}</div>";
      unset($_SESSION['error_message']);
  }
  ?>

  <h3>My Transcript Requests</h3>
  <table>
    <tr>
      <th>Request ID</th>
      <th>Purpose</th>
      <th>Date Requested</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    <?php
    $sql = "SELECT * FROM request WHERE student_id='$student_id' ORDER BY request_date DESC";
    $res = mysqli_query($conn, $sql);
    if (mysqli_num_rows($res) > 0) {
      while ($r = mysqli_fetch_assoc($res)) {
        $color = ($r['status'] == 'Approved') ? 'green' : (($r['status'] == 'Rejected') ? 'red' : 'orange');
        echo "<tr>
                <td>{$r['request_id']}</td>
                <td>{$r['purpose']}</td>
                <td>{$r['request_date']}</td>
                <td style='color:$color;font-weight:bold;'>{$r['status']}</td>
                <td><a href='view_request.php?id={$r['request_id']}' style='color:#1e40af;text-decoration:none;'>View</a></td>
              </tr>";
      }
    } else {
      echo "<tr><td colspan='5'>No requests found.</td></tr>";
    }
    ?>
  </table>
</div>

<script>
function updateClock(){
  const now = new Date();
  document.getElementById("clock").innerHTML = now.toLocaleString();
}
setInterval(updateClock, 1000);
updateClock();
</script>

</body>
</html>
