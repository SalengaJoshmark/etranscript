<?php
session_start();
include("../db_connect.php");

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

// Fetch department name from department_id
$department_name = '-';
if (!empty($student['department_id'])) {
    $dept_sql = "SELECT department_name FROM department WHERE department_id='" . intval($student['department_id']) . "' LIMIT 1";
    $dept_res = mysqli_query($conn, $dept_sql);
    if ($dept_res && mysqli_num_rows($dept_res) > 0) {
        $dept_row = mysqli_fetch_assoc($dept_res);
        $department_name = $dept_row['department_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard - E-Scription</title>
<style>
  body { font-family: "Poppins", sans-serif; background: linear-gradient(to right, #d6f0e8, #b7d6f2); margin: 0; }
  .navbar { background: #1e40af; display: flex; justify-content: space-between; align-items: center; padding: 12px 40px; color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.15); }
  .navbar .logo { font-weight: 600; font-size: 20px; }
  .navbar .links { display: flex; gap: 25px; }
  .navbar a { color: white; text-decoration: none; font-weight: 500; transition: 0.3s; padding: 6px 10px; border-radius: 6px; }
  .navbar a:hover { background: #3b82f6; }
  .navbar .right { display: flex; align-items: center; gap: 15px; }
  .logout { background: #adc0ffff; color: #af1e1eff; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-weight: 500; }
  .logout:hover { background: #e5dedfff; }
  .container { background: white; width: 85%; margin: 50px auto; border-radius: 10px; padding: 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
  h2 { color: #1e3a8a; }
  h3 { color: #1e3a8a; margin-top:40px; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: center; }
  th { background-color: #e0e7ff; color: #1e3a8a; }
  .alert { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: 500; }
  .success { background: #d1fae5; color: #065f46; }
  .error { background: #fee2e2; color: #991b1b; }
  .summary-cards { display:flex; flex-wrap:wrap; gap:20px; margin:20px 0; }
  .summary-cards .card { flex:1; min-width:150px; background:#e0e7ff; padding:15px; border-radius:8px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
  .summary-cards .card p { font-weight:600; font-size:18px; margin:5px 0 0; }
  .quick-stats { display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px; }
  .quick-stats .stat { flex:1; min-width:120px; padding:10px; border-radius:6px; text-align:center; font-weight:600; }
  .stat.pending { background:#fef3c7; color:#b45309; }
  .stat.approved { background:#d1fae5; color:#166534; }
  .stat.rejected { background:#fee2e2; color:#b91c1c; }
  .tips { background:#fff4e5; padding:15px; border-radius:8px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
  .tips ul { margin:5px 0 0 20px; padding:0; }
</style>
</head>
<body>

<div class="navbar">
  <div class="logo">E-Scription</div>
  <div class="links">
    <a href="student_dashboard.php">🏠 Home</a>
    <a href="new_request.php">📝 New Request</a>
    <a href="my_requests.php">📄 My Requests</a>
    <a href="student_profile.php">👤 Profile</a>
  </div>
  <div class="right">
    <span id="clock"></span>
    <a href="../logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <!-- Welcome & Profile -->
  <div style="display:flex;align-items:center;gap:20px;margin-bottom:15px;">
 <?php
$profile_img = $student['profile_picture'];
$display_img = (!empty($profile_img) && file_exists("../" . $profile_img)) 
               ? "../" . $profile_img 
               : "../uploads/profile_pics/default_avatar.png";
?>
<img src="<?= htmlspecialchars($display_img) ?>" 
     alt="Profile Picture" 
     style="width:80px;height:80px;border-radius:50%;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,0.2);">
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

<!-- Profile Summary Cards -->
<div class="summary-cards">
  <div class="card">
    <strong>Student ID</strong>
    <p><?= htmlspecialchars($student_id) ?></p>
  </div>
  <div class="card">
    <strong>Email</strong>
    <p><?= htmlspecialchars($student['email']) ?></p>
  </div>
  <div class="card">
    <strong>Course</strong>
    <p><?= htmlspecialchars($student['course'] ?: '-') ?></p>
  </div>
  <div class="card">
    <strong>Department</strong>
    <p><?= htmlspecialchars($department_name) ?></p>
  </div>
</div>

<!-- Quick Stats -->
<?php
$sql_counts = "SELECT 
                SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status='Approved' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN status='Rejected' THEN 1 ELSE 0 END) AS rejected
               FROM request WHERE student_id='$student_id'";
$counts = mysqli_fetch_assoc(mysqli_query($conn, $sql_counts));
?>
<div class="quick-stats">
  <div class="stat pending">Pending<br><?= $counts['pending'] ?: 0 ?></div>
  <div class="stat approved">Approved<br><?= $counts['approved'] ?: 0 ?></div>
  <div class="stat rejected">Rejected<br><?= $counts['rejected'] ?: 0 ?></div>
</div>

<!-- Tips Box -->
<div class="tips">
  <strong>Tips:</strong>
  <ul>
    <li>Click "New Request" to submit a transcript request.</li>
    <li>Track your requests in the table below.</li>
    <li>Click My Request To Download PDF needed after approve.</li>
    <li>Update your profile for accurate records.</li>
  </ul>
</div>

<!-- My Transcript Requests -->
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
              <td><a href='../view_request.php?id={$r['request_id']}' style='color:#1e40af;text-decoration:none;'>View</a></td>
            </tr>";
    }
  } else {
    echo "<tr><td colspan='5'>No requests found.</td></tr>";
  }
  ?>
</table>

</div>

<script>
function updateClock() {
  const now = new Date();
  const formatted = now.toLocaleString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
  document.getElementById("clock").innerHTML = formatted;
}
setInterval(updateClock, 1000);
updateClock();
</script>


</body>
</html>
