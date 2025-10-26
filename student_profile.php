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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $new_password = $_POST['password'];

    // Handle profile picture upload
    $profile_picture = $student['profile_picture']; // existing picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $target_dir = "uploads/profile_pics/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES['profile_picture']['name']);
        $target_file = $target_dir . $file_name;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
        $profile_picture = $target_file;
    }

    if (!empty($new_password)) {
        $hashed = md5($new_password);
        $update = "UPDATE student SET full_name='$full_name', course='$course', password='$hashed', profile_picture='$profile_picture' WHERE email='$email'";
    } else {
        $update = "UPDATE student SET full_name='$full_name', course='$course', profile_picture='$profile_picture' WHERE email='$email'";
    }

    if (mysqli_query($conn, $update)) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: student_profile.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile - E-Transcript System</title>
<style>
  body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(to right,#d6f0e8,#b7d6f2);
    margin: 0;
  }

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
  .logout { background:white; color:#1e40af; padding:6px 14px; border-radius:6px; text-decoration:none; font-weight:600; }

  .container {
    background: white;
    width: 450px;
    margin: 50px auto;
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    text-align: center;
  }

  h2 { color: #1e3a8a; }

  .profile-pic {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #1e40af;
    margin-bottom: 15px;
  }

  input[type="file"] {
    margin-top: 10px;
    font-size: 13px;
  }

  input[type="text"], input[type="email"], input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
  }

  button {
    width: 100%;
    background: #1e40af;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    margin-top: 20px;
    cursor: pointer;
    transition: 0.3s;
  }

  button:hover { background: #3b82f6; }

  .success {
    background: #d1fae5;
    color: #065f46;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    text-align: center;
  }

  label {
    display: block;
    margin-top: 10px;
    text-align: left;
    color: #1e3a8a;
    font-weight: 500;
  }
</style>
</head>
<body>

<div class="navbar">
  <div class="logo">E-Transcript System</div>
  <div class="links">
    <a href="student_dashboard.php">🏠 Home</a>
    <a href="new_request.php">📝 New Request</a>
    <a href="my_requests.php">📄 My Requests</a>
    <a href="student_profile.php" style="background:#3b82f6;">👤 Profile</a>
  </div>
  <div class="right">
    <a href="logout.php" class="logout">Logout</a>
  </div>
</div>

<div class="container">
  <h2>👤 My Profile</h2>

  <?php if (isset($_SESSION['success_message'])): ?>
    <div class="success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <img src="<?= htmlspecialchars($student['profile_picture'] ?: 'uploads/default_avatar.png') ?>" alt="Profile Picture" class="profile-pic">

    <label>Change Profile Picture</label>
    <input type="file" name="profile_picture" accept="image/*">

    <label>Full Name</label>
    <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" required>

    <label>Course</label>
    <input type="text" name="course" value="<?= htmlspecialchars($student['course']) ?>" required>

    <label>Email Address (Read Only)</label>
    <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" readonly>

    <label>New Password (Optional)</label>
    <input type="password" name="password" placeholder="Enter new password">

    <button type="submit">Save Changes</button>
  </form>
</div>

</body>
</html>
