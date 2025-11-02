<?php
session_start();
include("../db_connect.php");

// Ensure logged in as faculty
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'faculty') {
    header("Location: ../index.php");
    exit();
}

// Get faculty info
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT faculty_id, full_name FROM faculty WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$faculty = $result->fetch_assoc();
$faculty_id = $faculty['faculty_id'];
$faculty_name = $faculty['full_name'];
$stmt->close();

// Get request details
if (!isset($_GET['request_id'])) {
    die("Invalid request.");
}
$request_id = intval($_GET['request_id']);
$stmt = $conn->prepare("SELECT r.request_id, s.full_name AS student_name, r.purpose, r.status, r.date_needed
                        FROM request r
                        JOIN student s ON r.student_id = s.student_id
                        WHERE r.request_id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $subject = "Faculty Review Notice";
        $stmt = $conn->prepare("
        INSERT INTO faculty_messages (faculty_id, request_id, subject, message, sent_at, is_read)
        VALUES (?, ?, ?, ?, NOW(), 0)
        ");
        $stmt->bind_param("iiss", $faculty_id, $request_id, $subject, $message);
        $stmt->execute();
        $stmt->close();

        // Mark request as Checked
        $status = "Checked";
        $update = $conn->prepare("UPDATE request SET status = ? WHERE request_id = ?");
        $update->bind_param("si", $status, $request_id);
        $update->execute();
        $update->close();

        header("Location: department_requests.php?msg=sent");
        exit();
    } else {
        $error = "Message cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Notify Admin | Faculty Message</title>
<style>
  body {
    font-family:"Poppins",sans-serif;
    background:linear-gradient(to right,#d6f0e8,#b7d6f2);
    margin:0;
    color:#1e293b;
  }
  .container {
    width:90%;
    max-width:700px;
    margin:40px auto;
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
  }
  h2 { color:#1e3a8a; }
  textarea {
    width:100%;
    height:150px;
    padding:10px;
    border-radius:8px;
    border:1px solid #94a3b8;
    font-family:"Poppins",sans-serif;
    font-size:14px;
    resize:none;
  }
  .btn {
    display:inline-block;
    background:#2563eb;
    color:white;
    padding:10px 18px;
    border:none;
    border-radius:6px;
    font-weight:500;
    cursor:pointer;
    transition:0.3s;
  }
  .btn:hover { background:#1d4ed8; }
  .back {
    text-decoration:none;
    color:#1e3a8a;
    display:inline-block;
    margin-top:15px;
  }
  .error { color:red; margin-bottom:10px; }
  .info-box {
    background:#f8fafc;
    border:1px solid #e2e8f0;
    padding:10px 15px;
    border-radius:8px;
    margin-bottom:15px;
  }
  .highlight {
    color:#dc2626;
    font-weight:600;
  }
  .clock {
    font-size:14px;
    font-weight:500;
    color:#334155;
    text-align:right;
    margin-bottom:10px;
  }
</style>
</head>
<body>

<div class="container">
  <div class="clock" id="clock"></div>
  <h2>📩 Notify Admin</h2>

  <div class="info-box">
    <p><b>Request ID:</b> <?= htmlspecialchars($request['request_id']); ?></p>
    <p><b>Student:</b> <?= htmlspecialchars($request['student_name']); ?></p>
    <p><b>Purpose:</b> <?= htmlspecialchars($request['purpose']); ?></p>
    <p><b>Date Needed:</b>
      <span class="<?= (strtotime($request['date_needed']) - time()) < 259200 ? 'highlight' : ''; ?>">
        <?= htmlspecialchars($request['date_needed']); ?>
      </span>
    </p>
    <p><b>Current Status:</b> <?= htmlspecialchars($request['status']); ?></p>
  </div>

  <form method="POST">
    <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error); ?></div><?php endif; ?>
    <label for="message"><b>Message to Admin:</b></label>
    <textarea name="message" id="message" placeholder="e.g. Transcript has been reviewed and verified for accuracy. Ready for admin approval."></textarea><br><br>
    <button type="submit" class="btn">Send Message</button>
  </form>

  <a href="department_requests.php" class="back">← Back to Department Requests</a>
</div>

<script>
// 🕒 Real-time clock (updates every second)
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
  document.getElementById('clock').textContent = formatted;
}
setInterval(updateClock, 1000);
updateClock();
</script>

</body>
</html>
