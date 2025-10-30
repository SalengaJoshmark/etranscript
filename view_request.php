<?php
session_start();
include("db_connect.php");

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user_role = $_SESSION['user'];
$email = $_SESSION['email'] ?? '';
$logout_link = "logout.php";

// ✅ Detect source for back button
$source = $_GET['source'] ?? '';
if ($user_role === 'admin') {
    $back_link = ($source === 'manage') ? 'manage_request.php' : 'admin_dashboard.php';
} else {
    $back_link = 'student_dashboard.php';
}

// Ensure request ID exists
if (!isset($_GET['id'])) {
    header("Location: $back_link");
    exit();
}

$request_id = $_GET['id'];

// Fetch full request details + student info
$sql = "SELECT r.*, s.full_name AS student_name, s.email, s.course
        FROM request r
        JOIN student s ON r.student_id = s.student_id
        WHERE r.request_id = '$request_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Request not found!'); window.location='$back_link';</script>";
    exit();
}

$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Request Details - E-Transcript</title>
<style>
  body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(to right, #d6f0e8, #b7d6f2);
    margin: 0;
    padding: 0;
  }
  .header {
    background: #1e40af;
    color: white;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .right-section {
    display: flex;
    align-items: center;
    gap: 15px;
  }
  .clock { font-size: 14px; color: white; }
  .logout {
    background: white;
    color: #1e40af;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
  }
  .logout:hover { background: #dbeafe; }

  .container {
    background: white;
    width: 60%;
    margin: 40px auto;
    border-radius: 8px;
    padding: 30px 40px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }

  h2 { color: #1e3a8a; margin-bottom: 20px; }
  .details p {
    margin: 8px 0;
    color: #334155;
  }

  .btn {
    display: inline-block;
    padding: 10px 16px;
    margin-top: 20px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 500;
  }
  .approve { background: #16a34a; color: white; margin-right: 10px; }
  .approve:hover { background: #15803d; }
  .reject { background: #dc2626; color: white; margin-right: 10px; }
  .reject:hover { background: #b91c1c; }
  .back { background: #1e40af; color: white; }
  .back:hover { background: #1d4ed8; }
</style>
</head>
<body>

<div class="header">
  <h1>Request Details</h1>
  <div class="right-section">
    <div class="clock" id="clock"></div>
    <a href="<?= $logout_link ?>" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <h2>Request #<?= $row['request_id'] ?></h2>
  <div class="details">
    <p><strong>Student Name:</strong> <?= htmlspecialchars($row['student_name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
    <p><strong>Course:</strong> <?= htmlspecialchars($row['course']) ?></p>
    <p><strong>Purpose:</strong> <?= htmlspecialchars($row['purpose']) ?></p>

    <?php if (!empty($row['delivery_option'])): ?>
      <p><strong>Delivery Option:</strong> <?= htmlspecialchars($row['delivery_option']) ?></p>
    <?php endif; ?>

    <?php if (!empty($row['remarks'])): ?>
      <p><strong>Remarks:</strong> <?= htmlspecialchars($row['remarks']) ?></p>
    <?php endif; ?>

    <p><strong>Date Requested:</strong> <?= htmlspecialchars($row['request_date']) ?></p>
    <p><strong>Status:</strong>
      <?php
        $status = htmlspecialchars($row['status']);
        $color = ($status == 'Approved') ? '#16a34a' : (($status == 'Rejected') ? '#dc2626' : '#f59e0b');
        echo "<span style='color: $color; font-weight: 600;'>$status</span>";
      ?>
    </p>
  </div>

  <?php if ($user_role === 'admin' && $row['status'] == 'Pending'): ?>
    <a href="approve_request.php?id=<?= $row['request_id'] ?>" class="btn approve"
       onclick="return confirm('Are you sure you want to approve this request? This will automatically generate a PDF.');">
       Approve
    </a>
    <a href="reject_request.php?id=<?= $row['request_id'] ?>" class="btn reject"
       onclick="return confirm('Are you sure you want to reject this request?');">
       Reject
    </a>
  <?php endif; ?>

  <div style="margin-top: 30px;">
  <?php if ($user_role === 'admin'): ?>
    <a href="manage_request.php" class="btn back" style="margin-right: 10px;">Back to Manage Requests</a>
    <a href="admin_dashboard.php" class="btn back">Back to Dashboard</a>
  <?php else: ?>
    <a href="student_dashboard.php" class="btn back">Back to Dashboard</a>
  <?php endif; ?>
  </div>
</div>

<!-- JavaScript -->
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
  document.getElementById('clock').innerHTML = formatted;
}
setInterval(updateClock, 1000);
updateClock();
</script>

</body>
</html>
