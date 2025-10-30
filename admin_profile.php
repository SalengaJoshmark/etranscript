<?php
session_start();
include("db_connect.php");

// redirect if not logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];
$admin_name = "";
$admin_pic = "default_avatar.png";

// Fetch admin info
$stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['full_name'];
    $admin_pic = !empty($row['profile_picture']) ? $row['profile_picture'] : "default_avatar.png";
}
$stmt->close();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_name = trim($_POST['full_name']);
    $new_password = trim($_POST['password']);
    $profile_picture = $admin_pic;

   // Handle profile picture upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $upload_dir = "uploads/profile_pics/";

    // Ensure folder exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Create a safe unique filename
    $file_ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
    $filename = time() . "_" . uniqid() . "." . strtolower($file_ext);
    $target_path = $upload_dir . $filename;

    // Move file to correct folder
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
        $profile_picture = $target_path;
    }
}


    // Update query
    if (!empty($new_password)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin SET full_name=?, password=?, profile_picture=? WHERE email=?");
        $stmt->bind_param("ssss", $new_name, $hashed, $profile_picture, $email);
    } else {
        $stmt = $conn->prepare("UPDATE admin SET full_name=?, profile_picture=? WHERE email=?");
        $stmt->bind_param("sss", $new_name, $profile_picture, $email);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='admin_profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Profile</title>
<style>
  body {
    font-family: "Poppins", sans-serif;
    background: linear-gradient(to right, #d6f0e8, #b7d6f2);
    margin: 0;
  }

  .navbar {
    background: #1e40af;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 40px;
  }

  .navbar .logo { font-size: 20px; font-weight: 600; }
  .navbar .links a {
    color: white;
    text-decoration: none;
    margin-right: 20px;
    transition: 0.3s;
  }
  .navbar .links a:hover { text-decoration: underline; }
  .logout {
    background: white; color: #1e40af;
    padding: 6px 14px; border-radius: 6px;
    text-decoration: none; font-weight: 600;
  }

  .container {
    background: white;
    width: 450px;
    margin: 60px auto;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    text-align: center;
  }

  h2 { color: #1e3a8a; margin-bottom: 20px; }

  .profile-pic {
    width: 120px; height: 120px;
    border-radius: 50%; object-fit: cover;
    border: 3px solid #1e40af;
    margin-bottom: 15px;
  }

  input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    margin-bottom: 12px;
  }

  button {
    background: #1e40af;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: 0.3s;
  }

  button:hover { background: #1d4ed8; }

  .note { font-size: 12px; color: #64748b; }
</style>
</head>
<body>

<div class="navbar">
  <div class="logo">E-Transcript System</div>
  <div class="links">
   <a href="admin_dashboard.php">🏠 Home</a>
    <a href="manage_request.php">📂 Manage Requests</a>
    <a href="student_list.php">🎓 Students List</a>
    <a href="transaction_log.php">🕒 Transaction Logs</a>
    <a href="admin_profile.php">👤 Profile</a>
  </div>
  <a href="logout.php" class="logout">Logout</a>
</div>

<div class="container">
  <h2>Admin Profile</h2>
  <form method="POST" enctype="multipart/form-data">
    <img src="<?php echo htmlspecialchars($admin_pic); ?>" class="profile-pic" id="preview">
    <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)">
    <input type="text" name="full_name" value="<?php echo htmlspecialchars($admin_name); ?>" required>
    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
    <input type="password" name="password" placeholder="Enter new password (optional)">
    <button type="submit">Save Changes</button>
    <p class="note">Leave password blank if you don't want to change it.</p>
  </form>
</div>

<script>
function previewImage(event) {
  const reader = new FileReader();
  reader.onload = function() {
    document.getElementById('preview').src = reader.result;
  };
  reader.readAsDataURL(event.target.files[0]);
}
</script>

</body>
</html>
