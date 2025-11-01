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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Requests - E-Transcript System</title>
<style>
  body { font-family: "Poppins", sans-serif; background: linear-gradient(to right,#d6f0e8,#b7d6f2); margin: 0; }
  .navbar { background: #1e40af; display: flex; justify-content: space-between; align-items: center; padding: 12px 40px; color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.15); }
  .navbar .logo { font-weight: 600; font-size: 20px; }
  .navbar .links { display: flex; gap: 25px; }
  .navbar a { color: white; text-decoration: none; font-weight: 500; transition: 0.3s; padding: 6px 10px; border-radius: 6px; }
  .navbar a:hover { background: #3b82f6; }
  .logout { background:white; color:#1e40af; padding:6px 14px; border-radius:6px; text-decoration:none; font-weight:600; }
  .container { background: white; width: 85%; margin: 50px auto; border-radius: 10px; padding: 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
  h2 { color: #1e3a8a; margin-bottom: 20px; }

  /* Summary Cards */
  .summary-cards { display:flex; flex-wrap:wrap; gap:20px; margin-bottom:30px; }
  .summary-cards .card { flex:1; min-width:150px; background:#e0e7ff; padding:15px; border-radius:8px; text-align:center; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
  .summary-cards .card p { font-weight:600; font-size:18px; margin:5px 0 0; }

  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: center; }
  th { background-color: #e0e7ff; color: #1e3a8a; }
  .status { font-weight: bold; }
  .status.Pending { color: orange; }
  .status.Approved { color: green; }
  .status.Rejected { color: red; }
  .btn-doc { color: #1e40af; text-decoration: none; font-weight: 500; border: 1px solid #1e40af; padding: 5px 8px; border-radius: 5px; transition: 0.2s; display: inline-block; margin: 2px; }
  .btn-doc:hover { background: #1e40af; color: white; }
  .tips { background:#fff4e5; padding:15px; border-radius:8px; margin-top:30px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
  .tips ul { margin:5px 0 0 20px; padding:0; }
</style>
</head>
<body>

<div class="navbar">
  <div class="logo">E-Scription</div>
  <div class="links">
    <a href="student_dashboard.php">🏠 Home</a>
    <a href="new_request.php">📝 New Request</a>
    <a href="my_requests.php" style="background:#3b82f6;">📄 My Requests</a>
    <a href="student_profile.php">👤 Profile</a>
  </div>
  <div class="right">
    <a href="../logout.php" class="logout">Logout</a>
  </div>
</div>

<style>
  .logout {
    background: #adc0ff; /* match dashboard style */
    color: #1e40af;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s;
  }
  .logout:hover {
    background: #dbeafe;
  }
</style>
<div class="container">
  <h2>📄 My Transcript Requests</h2>

  <!-- Summary Cards -->
  <?php
  // Quick stats for request status
  $sql_counts = "SELECT 
                    SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status='Approved' THEN 1 ELSE 0 END) AS approved,
                    SUM(CASE WHEN status='Rejected' THEN 1 ELSE 0 END) AS rejected
                 FROM request WHERE student_id='$student_id'";
  $counts = mysqli_fetch_assoc(mysqli_query($conn, $sql_counts));
  ?>
  <div class="summary-cards">
    <div class="card">
      <strong>Pending Requests</strong>
      <p><?= $counts['pending'] ?: 0 ?></p>
    </div>
    <div class="card">
      <strong>Approved Requests</strong>
      <p><?= $counts['approved'] ?: 0 ?></p>
    </div>
    <div class="card">
      <strong>Rejected Requests</strong>
      <p><?= $counts['rejected'] ?: 0 ?></p>
    </div>
    <div class="card">
      <strong>Total Requests</strong>
      <p><?= array_sum($counts) ?></p>
    </div>
  </div>

  <table>
    <tr>
      <th>Request ID</th>
      <th>Purpose</th>
      <th>Date Requested</th>
      <th>Status</th>
      <th>Remarks</th>
      <th>Documents</th>
    </tr>
    <?php
    $sql = "SELECT * FROM request WHERE student_id='$student_id' ORDER BY request_date DESC";
    $res = mysqli_query($conn, $sql);

    if (mysqli_num_rows($res) > 0) {
      while ($r = mysqli_fetch_assoc($res)) {
        $status = htmlspecialchars($r['status']);
        $pdfPath = "../uploads/generated_pdfs/approval_" . $r['request_id'] . ".pdf";

        echo "<tr>
                <td>" . htmlspecialchars($r['request_id']) . "</td>
                <td>" . htmlspecialchars($r['purpose']) . "</td>
                <td>" . htmlspecialchars($r['request_date']) . "</td>
                <td class='status " . htmlspecialchars($status) . "'>" . htmlspecialchars($status) . "</td>
                <td>" . (!empty($r['remarks']) ? htmlspecialchars($r['remarks']) : '—') . "</td>";

        if ($status === 'Approved') {
            echo "<td>";
            if (file_exists($pdfPath)) {
                $pdfUrl = htmlspecialchars($pdfPath);
                echo "<a class='btn-doc' href='{$pdfUrl}' target='_blank'>View Approval Notice</a>";
                echo "<a class='btn-doc' href='{$pdfUrl}' download>Download</a>";
            } else {
                echo "—";
            }
            echo "</td>";
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

  <!-- Tips Box -->
  <div class="tips">
    <strong>Tips:</strong>
    <ul>
      <li>Click "New Request" to submit a transcript request.</li>
      <li>Check the status of your requests regularly.</li>
      <li>Approved requests will have downloadable approval notices.</li>
      <li>Update your profile for accurate records.</li>
    </ul>
  </div>
</div>

</body>
</html>
