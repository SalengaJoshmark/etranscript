<?php
session_start();
include("../db_connect.php");

// ✅ Ensure only students can access
if (!isset($_SESSION['user']) || $_SESSION['user'] != 'student') {
    header("Location: index.php");
    exit();
}

// ✅ Fetch student ID
$email = $_SESSION['email'];
$result = mysqli_query($conn, "SELECT student_id FROM student WHERE email='$email'");
$row = mysqli_fetch_assoc($result);
$student_id = $row['student_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>New Transcript Request</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(to right, #d6f0e8, #b7d6f2);
      margin: 0;
      padding: 0;
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
    .navbar a.active { background: #3b82f6; }
    .navbar .right { display: flex; align-items: center; gap: 15px; }
    .logout {
      background: #adc0ff;
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
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 { color: #1e3a8a; margin-bottom: 10px; text-align: center; }

    label { display: block; margin: 12px 0 6px; color: #334155; font-weight: 500; }
    select, textarea, button, input[type="text"], input[type="date"] {
      width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;
    }
    button {
      background: #1e40af; color: white; border: none; margin-top: 20px; padding: 10px;
      font-weight: 500; cursor: pointer;
    }
    button:hover { background: #1d4ed8; }

    .notice {
      text-align:center;
      color:#475569;
      background:#f1f5f9;
      padding:10px;
      border-radius:6px;
      font-size:14px;
      margin-bottom:20px;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
  <div class="logo">E-Scription</div>
  <div class="links">
    <a href="student_dashboard.php">🏠 Home</a>
    <a href="new_request.php" class="active">📝 New Request</a>
    <a href="my_requests.php">📄 My Requests</a>
    <a href="student_profile.php">👤 Profile</a>
  </div>
  <div class="right">
    <span id="clock"></span>
    <a href="../logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <h2>Submit a New Request</h2>

  <p class="notice">
    ⚠️ All requests are reviewed by your department faculty for verification before registrar approval. 
    Processing time may vary depending on document type and faculty availability.
  </p>

  <form method="POST" action="../submit_request.php">
    <label for="purpose">Purpose of Request:</label>
    <select name="purpose" id="purpose" required onchange="toggleCustomPurpose(this.value)">
      <option value="">-- Select Purpose --</option>
      <option value="Transcript of Records">Transcript of Records</option>
      <option value="Certificate of Grades">Certificate of Grades</option>
      <option value="Good Moral Certificate">Good Moral Certificate</option>
      <option value="Other">Other (Please specify)</option>
    </select>

    <div id="customPurposeContainer" style="display:none;">
      <label for="custom_purpose">Custom Purpose:</label>
      <input type="text" name="custom_purpose" id="custom_purpose" placeholder="Enter your custom request">
    </div>

    <label for="delivery_option">Delivery Option:</label>
    <select name="delivery_option" id="delivery_option" required>
      <option value="">-- Select Option --</option>
      <option value="Pickup">Pickup</option>
      <option value="Email">Email</option>
    </select>

    <p id="estimated_time" style="color:#475569;font-size:13px;margin-top:5px;">
      ⏳ Estimated processing time: 2–3 business days.
    </p>

    <label for="date_needed">Date Needed (optional):</label>
    <input type="date" name="date_needed" id="date_needed">

    <label for="remarks">Remarks (optional):</label>
    <textarea name="remarks" id="remarks" rows="3" placeholder="Any additional notes..."></textarea>

    <p id="summary" style="
      margin-top:10px;
      background:#f8fafc;
      padding:10px;
      border-radius:6px;
      font-size:13px;
      color:#334155;">
    </p>

    <input type="hidden" name="student_id" value="<?= $student_id ?>">

    <div style="display:flex; gap:10px; margin-top:20px;">
      <button type="submit">Submit Request</button>
      <a href="student_dashboard.php" style="
        background:#64748b;
        color:white;
        padding:10px 20px;
        text-decoration:none;
        border-radius:5px;
        display:inline-block;
        text-align:center;">Cancel</a>
    </div>
  </form>
</div>

<!-- JS: Clock, Dynamic Estimate & Summary -->
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

function toggleCustomPurpose(value) {
  const customContainer = document.getElementById('customPurposeContainer');
  if (value === "Other") {
    customContainer.style.display = "block";
    document.getElementById('custom_purpose').required = true;
  } else {
    customContainer.style.display = "none";
    document.getElementById('custom_purpose').required = false;
  }
}

// ✅ Dynamic estimated time & summary based on purpose
document.getElementById('purpose').addEventListener('change', function() {
  const estimate = document.getElementById('estimated_time');
  const summary = document.getElementById('summary');
  switch (this.value) {
    case 'Transcript of Records':
      estimate.textContent = "⏳ Estimated processing time: 5–7 business days (faculty verification required).";
      summary.innerHTML = "Your transcript will first be verified by your department faculty to ensure accuracy before registrar approval.";
      break;
    case 'Certificate of Grades':
      estimate.textContent = "⏳ Estimated processing time: 3–5 business days.";
      summary.innerHTML = "Your grades will be reviewed by your department faculty for correctness prior to approval.";
      break;
    case 'Good Moral Certificate':
      estimate.textContent = "⏳ Estimated processing time: 2–3 business days.";
      summary.innerHTML = "Your request will be reviewed by faculty before registrar issuance.";
      break;
    case 'Other':
      estimate.textContent = "⏳ Processing time may vary based on request details.";
      summary.innerHTML = "Faculty will review your custom request before registrar approval.";
      break;
    default:
      estimate.textContent = "⏳ Estimated processing time: 2–3 business days.";
      summary.innerHTML = "";
  }
});
</script>

</body>
</html>
