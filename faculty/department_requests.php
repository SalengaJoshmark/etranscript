<?php
session_start();
include("../db_connect.php");

// Redirect if not logged in as faculty
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'faculty') {
    header("Location: ../index.php");
    exit();
}

// Check email in session
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

$email = $_SESSION['email'];
$faculty_name = "Faculty Member";
$faculty_pic = "../uploads/profile_pics/default_avatar.png";
$faculty_department = "";
$faculty_dept_id = 0;

// Fetch faculty info
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
<title>Department Requests | Faculty Panel</title>
<style>
  body { font-family: "Poppins", sans-serif; background: linear-gradient(to right, #d6f0e8, #b7d6f2); margin:0; color:#1e293b; }
  
  /* HEADER */
  .header { background: #1e40af; color:white; padding:15px 40px; display:flex; justify-content:space-between; align-items:center; }
  .header h1 { font-size:22px; margin:0; }
  .right-section { display:flex; align-items:center; gap:20px; }
  .clock { font-size:14px; color:#e0e7ff; }
  .logout { background:white; color:#1e40af; padding:8px 16px; border-radius:6px; text-decoration:none; font-weight:500; transition:0.3s; }
  .logout:hover { background:#ff0707ff; color:white; }
  .faculty-pic { width:50px; height:50px; border-radius:50%; object-fit:cover; border:2px solid #fff; }

  /* NAVIGATION */
  .nav { display:flex; gap:15px; background:#e0e7ff; padding:10px 20px; border-radius:8px; margin:25px auto; width:90%; max-width:1300px; }
  .nav a { text-decoration:none; color:#1e3a8a; font-weight:500; transition:0.3s; }
  .nav a:hover, .nav a.active { color:#1d4ed8; text-decoration:underline; }

  /* CONTAINER */
  .container { width:90%; margin:20px auto; max-width:1300px; }
  .section-card { background:white; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.08); padding:25px; }

  /* Tables */
  table { width:100%; border-collapse:collapse; border-radius:8px; overflow:hidden; background:white; }
  th, td { border-bottom:1px solid #335b90ff; padding:10px; text-align:center; }
  th { background-color:#bbdcfdff; color:#1e3a8a; }
  tr:hover { background-color:#eff6ff; }

  /* Buttons */
  .btn { padding:6px 12px; border-radius:6px; text-decoration:none; font-size:14px; margin:2px; color:white; font-weight:500; transition:0.3s; display:inline-block; border:none; cursor:pointer; }
  .btn.view { background:#2563eb; }
  .btn.view:hover { background:#1d4ed8; }
  .btn.message { background:#f59e0b; }
  .btn.message:hover { background:#d97706; }
  .btn.disabled {
    background:#94a3b8;
    cursor:not-allowed;
    pointer-events:none;
  }

  /* Status */
  .status { font-weight:600; padding:6px 10px; border-radius:6px; display:inline-block; }
  .status.pending { background-color:#fef3c7; color:#b45309; }
  .status.approved { background-color:#dcfce7; color:#166534; }
  .status.rejected { background-color:#fee2e2; color:#b91c1c; }
  .status.checked { background-color:#bfdbfe; color:#1e3a8a; }
</style>
</head>
<body>

<div class="header">
  <h1>📄 Department Transcript Requests</h1>
  <div class="right-section">
    <img src="<?= htmlspecialchars($faculty_pic); ?>" alt="Faculty" class="faculty-pic">
    <div class="clock" id="clock"></div>
    <a href="../logout.php" class="logout">Logout</a>
  </div>
</div>

<!-- ✅ NAVIGATION -->
<div class="nav">
  <a href="faculty_dashboard.php">🏠 Home</a>
  <a href="department_requests.php" class="active">📄 Department Requests</a>
  <a href="transaction_log.php">🕒 Transaction Logs</a>
  <a href="faculty_profile.php">👤 Profile</a>
</div>

<div class="container">
  <div class="section-card">
    <h2>🗂️ Transcript Requests (<?= htmlspecialchars($faculty_department); ?>)</h2>
    <p>These are transcript requests from students in your department. Review each one and message the admin when checked.</p>
    <table>
      <tr>
        <th>Request ID</th>
        <th>Student</th>
        <th>Purpose</th>
        <th>Date</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
      <?php
      $sql = "SELECT r.request_id, s.full_name, r.purpose, r.request_date, r.status 
              FROM request r
              JOIN student s ON r.student_id = s.student_id
              WHERE s.department_id = ?
              ORDER BY r.request_date DESC";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $faculty_dept_id);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($res && $res->num_rows > 0) {
          while ($r = $res->fetch_assoc()) {
              $statusClass = strtolower($r['status']);
              echo "<tr>
                      <td>{$r['request_id']}</td>
                      <td>" . htmlspecialchars($r['full_name']) . "</td>
                      <td>" . htmlspecialchars($r['purpose']) . "</td>
                      <td>{$r['request_date']}</td>
                      <td><span class='status {$statusClass}'>" . htmlspecialchars($r['status']) . "</span></td>
                      <td>";
              
              echo "<a href='../view_request.php?id={$r['request_id']}' class='btn view'>View Details</a>";

              // ✅ Disable "Notify Admin" if already checked
              if (strtolower($r['status']) === 'checked') {
                  echo "<button class='btn message disabled'>✔ Already Checked</button>";
              } else {
                  echo "<a href='message_admin.php?request_id={$r['request_id']}' class='btn message'>Notify Admin: Checked</a>";
              }

              echo "</td></tr>";
          }
      } else {
          echo "<tr><td colspan='6'>No transcript requests found in your department.</td></tr>";
      }
      $stmt->close();
      $conn->close();
      ?>
    </table>
  </div>
</div>

<script>
function updateClock() {
  const now = new Date();
  const formatted = now.toLocaleString('en-US', {
    weekday:'short', year:'numeric', month:'short', day:'numeric',
    hour:'2-digit', minute:'2-digit', second:'2-digit'
  });
  document.getElementById('clock').innerHTML = formatted;
}
setInterval(updateClock, 1000);
updateClock();
</script>

</body>
</html>
