<?php
session_start();
include("../db_connect.php");

// Ensure only students can access
if (!isset($_SESSION['user']) || $_SESSION['user'] != 'student') {
    header("Location: index.php");
    exit();
}

// Fetch student ID
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
    h2 { color: #1e3a8a; margin-bottom: 20px; text-align: center; }

    label { display: block; margin: 12px 0 6px; color: #334155; font-weight: 500; }
    select, textarea, button, input[type="text"] {
      width: 100%; padding: 8px; border-radius: 5px; border: 1px solid #ccc;
    }
    button {
      background: #1e40af; color: white; border: none; margin-top: 20px; padding: 10px;
      font-weight: 500; cursor: pointer;
    }
    button:hover { background: #1d4ed8; }
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

    <label for="remarks">Remarks (optional):</label>
    <textarea name="remarks" id="remarks" rows="3" placeholder="Any additional notes..."></textarea>

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

<!-- JS: Clock & Custom Purpose -->
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
</script>

</body>
</html>
