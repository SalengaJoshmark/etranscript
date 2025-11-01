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

// Fetch faculty info and department_id
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
<title>Faculty Dashboard | E-STranscript Request System</title>
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

  /* CONTAINER */
  .container { width:90%; margin:30px auto; max-width:1300px; }
  .welcome { background:white; border-radius:10px; padding:25px; box-shadow:0 3px 10px rgba(0,0,0,0.1); margin-bottom:20px; }
  .welcome h2 { color:#1e3a8a; margin-bottom:5px; }

  /* NAVIGATION */
  .nav { display:flex; gap:15px; background:#e0e7ff; padding:10px 20px; border-radius:8px; margin-bottom:25px; }
  .nav a { text-decoration:none; color:#1e3a8a; font-weight:500; transition:0.3s; }
  .nav a:hover, .nav a.active { color:#1d4ed8; text-decoration:underline; }

  /* Section Card */
  .section-card { background:white; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.08); padding:25px; margin-bottom:25px; }
  .section-card h2 { color:#1e3a8a; margin-bottom:15px; font-size:20px; }

  /* Tables */
  table { width:100%; border-collapse:collapse; border-radius:8px; overflow:hidden; background:white; }
  th, td { border-bottom:1px solid #335b90ff; padding:10px; text-align:center; }
  th { background-color:#bbdcfdff; color:#1e3a8a; }
  tr:hover { background-color:#eff6ff; }

  /* Status */
  .status { font-weight:600; padding:6px 10px; border-radius:6px; display:inline-block; }
  .status.pending { background-color:#fef3c7; color:#b45309; }
  .status.approved { background-color:#dcfce7; color:#166534; }
  .status.rejected { background-color:#fee2e2; color:#b91c1c; }

  /* Buttons */
  .btn { padding:6px 12px; border-radius:6px; text-decoration:none; font-size:14px; margin:0 3px; color:white; font-weight:500; transition:0.3s; display:inline-block; }
  .btn.view { background:#2563eb; }
  .btn.view:hover { background:#1d4ed8; }
</style>
</head>
<body>

<div class="header">
  <h1>🎓 E-Scription Faculty Dashboard</h1>
  <div class="right-section">
    <img src="<?= htmlspecialchars($faculty_pic); ?>" alt="Faculty" class="faculty-pic">
    <div class="clock" id="clock"></div>
    <a href="../logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <div class="welcome">
    <h2>Welcome, <?= htmlspecialchars($faculty_name); ?> 👋</h2>
    <p>Department: <b><?= htmlspecialchars($faculty_department); ?></b></p>
    <p>Review student requests and see your department students below.</p>
  </div>

  <div class="nav">
    <a href="faculty_dashboard.php" class="active">🏠 Home</a>
    <a href="transaction_log.php">🕒 Transaction Logs</a>
    <a href="faculty_profile.php">👤 Profile</a>
  </div>

  <!-- Department Transcript Requests -->
  <div class="section-card">
    <h2>📄 Department Transcript Requests</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Student Name</th>
        <th>Purpose</th>
        <th>Date</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
      <?php
      $sql_requests = "SELECT r.request_id, s.full_name, r.purpose, r.request_date, r.status
                       FROM request r
                       JOIN student s ON r.student_id = s.student_id
                       WHERE s.department_id = ?
                       ORDER BY r.request_date DESC";
      $stmt_req = $conn->prepare($sql_requests);
      $stmt_req->bind_param("i", $faculty_dept_id);
      $stmt_req->execute();
      $res_req = $stmt_req->get_result();
      if ($res_req && mysqli_num_rows($res_req) > 0) {
          while ($row = mysqli_fetch_assoc($res_req)) {
              $statusClass = strtolower($row['status']);
              echo "<tr>
                      <td>{$row['request_id']}</td>
                      <td>{$row['full_name']}</td>
                      <td>{$row['purpose']}</td>
                      <td>{$row['request_date']}</td>
                      <td><span class='status {$statusClass}'>" . htmlspecialchars($row['status']) . "</span></td>
                      <td><a href='../view_request.php?id={$row['request_id']}' class='btn view'>View</a></td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='6'>No requests found for your department.</td></tr>";
      }
      $stmt_req->close();
      ?>
    </table>
  </div>

  <!-- Department Students -->
  <div class="section-card">
    <h2>👥 Department Students</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Course</th>
        <th>Registered Date</th>
      </tr>
      <?php
      $sql_students = "SELECT student_id, full_name, email, course, created_at
                       FROM student
                       WHERE department_id = ?
                       ORDER BY created_at DESC";
      $stmt_stud = $conn->prepare($sql_students);
      $stmt_stud->bind_param("i", $faculty_dept_id);
      $stmt_stud->execute();
      $res_stud = $stmt_stud->get_result();
      if ($res_stud && mysqli_num_rows($res_stud) > 0) {
          while ($student = mysqli_fetch_assoc($res_stud)) {
              $course = !empty($student['course']) ? htmlspecialchars($student['course']) : '-';
              echo "<tr>
                      <td>{$student['student_id']}</td>
                      <td>{$student['full_name']}</td>
                      <td>{$student['email']}</td>
                      <td>{$course}</td>
                      <td>{$student['created_at']}</td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='5'>No students found in your department.</td></tr>";
      }
      $stmt_stud->close();
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
