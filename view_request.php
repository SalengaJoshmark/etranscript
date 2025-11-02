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

// Determine back links
$source = $_GET['source'] ?? '';
if ($user_role === 'admin') {
    $back_link = ($source === 'manage') ? 'admin/manage_request.php' : 'admin/admin_dashboard.php';
} elseif ($user_role === 'faculty') {
    $back_link = 'faculty/department_requests.php';
} else {
    $back_link = 'student/student_dashboard.php';
}

// Ensure request ID exists
if (!isset($_GET['id'])) {
    header("Location: $back_link");
    exit();
}

$request_id = intval($_GET['id']);

// Fetch request + student info
$sql = "SELECT r.*, s.full_name AS student_name, s.email, s.course
        FROM request r
        JOIN student s ON r.student_id = s.student_id
        WHERE r.request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Request not found!'); window.location='$back_link';</script>";
    exit();
}
$row = $result->fetch_assoc();
$stmt->close();

// Check if faculty already marked as "Checked"
$is_checked_by_faculty = (isset($row['status']) && strtolower($row['status']) === 'checked');

// Highlight date_needed based on urgency
$date_needed_highlight = '';
if (!empty($row['date_needed'])) {
    $today = new DateTime();
    $date_needed = new DateTime($row['date_needed']);
    $interval = (int)$today->diff($date_needed)->format("%r%a");

    if ($interval >= 0 && $interval <= 3) {
        $date_needed_highlight = 'style="color:#dc2626; font-weight:600;"'; // red
    } elseif ($interval >= 4 && $interval <= 7) {
        $date_needed_highlight = 'style="color:#f97316; font-weight:600;"'; // orange
    }
}
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
  .details p { margin: 8px 0; color: #334155; }

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

  .disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
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
  <h2>Request #<?= htmlspecialchars($row['request_id']) ?></h2>

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

    <?php if (!empty($row['date_needed'])): ?>
      <p><strong>Date Needed:</strong> <span <?= $date_needed_highlight ?>><?= htmlspecialchars($row['date_needed']) ?></span></p>
    <?php endif; ?>

    <p><strong>Date Requested:</strong> <?= htmlspecialchars($row['request_date']) ?></p>
    <p><strong>Status:</strong>
      <?php
        $status = htmlspecialchars($row['status']);
        $color = match ($status) {
          'Approved' => '#16a34a',
          'Rejected' => '#dc2626',
          'Checked'  => '#0284c7',
          default => '#f59e0b'
        };
        echo "<span style='color: $color; font-weight: 600;'>$status</span>";
      ?>
    </p>
  </div>

  <!-- Faculty Messages (visible to admin and faculty only) -->
  <?php if ($user_role === 'admin' || $user_role === 'faculty'): ?>
    <?php
    $msg_stmt = $conn->prepare("
      SELECT fm.subject, fm.message, fm.sent_at, fm.is_read, f.full_name AS faculty_name
      FROM faculty_messages fm
      JOIN faculty f ON fm.faculty_id = f.faculty_id
      WHERE fm.request_id = ?
      ORDER BY fm.sent_at DESC
    ");
    $msg_stmt->bind_param("i", $request_id);
    $msg_stmt->execute();
    $messages = $msg_stmt->get_result();

    if ($user_role === 'admin' && $messages->num_rows > 0) {
        $update_read = $conn->prepare("UPDATE faculty_messages SET is_read = 1 WHERE request_id = ?");
        $update_read->bind_param("i", $request_id);
        $update_read->execute();
        $update_read->close();
    }
    ?>

    <?php if ($messages->num_rows > 0): ?>
      <div style="margin-top: 20px;">
        <h3 style="color:#1e3a8a;">📩 Faculty Messages</h3>
        <?php while ($msg = $messages->fetch_assoc()): ?>
          <div style="background:#f8fafc; border:1px solid #cbd5e1; padding:12px; border-radius:8px; margin-bottom:10px;">
            <p style="margin:0;">
              <strong>Subject:</strong> <?= htmlspecialchars($msg['subject']); ?><br>
              <strong>From:</strong> <?= htmlspecialchars($msg['faculty_name']); ?> 
              <span style="font-size:12px; color:#475569;">(<?= htmlspecialchars($msg['sent_at']); ?>)</span>
            </p>
            <p style="margin-top:8px;"><?= nl2br(htmlspecialchars($msg['message'])); ?></p>
            <?php if ($msg['is_read'] == 0): ?>
              <p style="font-size:12px;color:#0284c7;"><em>Unread</em></p>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>

    <?php $msg_stmt->close(); ?>
  <?php endif; ?>

  <!-- Admin: Approve/Reject only if faculty checked -->
  <?php if ($user_role === 'admin'): ?>
    <?php if ($is_checked_by_faculty): ?>
      <a href="approve_request.php?id=<?= $row['request_id'] ?>" 
         class="btn approve" 
         onclick="return confirm('Approve this request? This will generate a PDF.');">
         Approve
      </a>
      <a href="reject_request.php?id=<?= $row['request_id'] ?>" 
         class="btn reject" 
         onclick="return confirm('Reject this request?');">
         Reject
      </a>
    <?php else: ?>
      <button class="btn approve disabled">Approve (Waiting for Faculty)</button>
      <button class="btn reject disabled">Reject (Waiting for Faculty)</button>
    <?php endif; ?>
  <?php endif; ?>

    <div style="margin-top: 30px;">
    <?php if ($user_role === 'admin'): ?>
        <a href="admin/manage_request.php" class="btn back" style="margin-right: 10px;">📂 Back to Manage Requests</a>
        <a href="admin/admin_dashboard.php" class="btn back">🏠 Back to Dashboard</a>

    <?php elseif ($user_role === 'faculty'): ?>
        <a href="faculty/department_requests.php" class="btn back" style="margin-right: 10px;">📄 Back to Department Requests</a>
        <a href="faculty/faculty_dashboard.php" class="btn back">🏠 Back to Dashboard</a>

    <?php else: ?>
        <a href="student/my_requests.php" class="btn back" style="margin-right: 10px;">📄 Back to My Requests</a>
        <a href="student/student_dashboard.php" class="btn back">🏠 Back to Dashboard</a>
    <?php endif; ?>
  </div>

<!-- JavaScript Clock -->
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
