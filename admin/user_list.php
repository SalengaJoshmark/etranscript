<?php
session_start();
include("../db_connect.php");

// Redirect if not logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch admin name
$email = $_SESSION['email'];
$admin_name = "Admin";
$stmt = $conn->prepare("SELECT full_name FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['full_name'];
}
$stmt->close();

// ----------------------------
// Handle Approve / Reject Actions
// ----------------------------
if (isset($_GET['action']) && isset($_GET['type']) && isset($_GET['id'])) {
    $action = $_GET['action']; // approve or reject
    $type = $_GET['type']; // student or faculty
    $id = intval($_GET['id']);

    $table = ($type === 'faculty') ? 'faculty' : 'student';
    $id_col = ($type === 'faculty') ? 'faculty_id' : 'student_id';
    $newStatus = ($action === 'approve') ? 'Approved' : 'Rejected';

    if ($action === 'reject') {
        // 🧹 Get profile picture path before deleting
        $pic_query = $conn->prepare("SELECT profile_picture FROM $table WHERE $id_col = ?");
        $pic_query->bind_param("i", $id);
        $pic_query->execute();
        $pic_result = $pic_query->get_result();
        $pic = ($pic_result->num_rows > 0) ? $pic_result->fetch_assoc()['profile_picture'] : '';
        $pic_query->close();

        // ❌ Delete record
        $delete_stmt = $conn->prepare("DELETE FROM $table WHERE $id_col = ?");
        $delete_stmt->bind_param("i", $id);
        if ($delete_stmt->execute()) {
            if (!empty($pic) && file_exists("../" . $pic) && strpos($pic, "default_avatar.png") === false) {
                unlink("../" . $pic);
            }
            mysqli_query($conn, "ALTER TABLE $table AUTO_INCREMENT = 1");
            $_SESSION['success_message'] = ucfirst($type) . " registration rejected and removed. Profile picture deleted. ID counter adjusted.";
        } else {
            $_SESSION['error_message'] = "Failed to reject and remove " . $type . ".";
        }
        $delete_stmt->close();
    } else {
        // ✅ Approve user
        $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE $id_col = ?");
        $stmt->bind_param("si", $newStatus, $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = ucfirst($type) . " has been approved.";
        } else {
            $_SESSION['error_message'] = "Failed to approve " . $type . ".";
        }
        $stmt->close();
    }

    header("Location: user_list.php");
    exit();
}


// ----------------------------
// Handle Delete Actions
// ----------------------------
if (isset($_GET['delete_type']) && isset($_GET['delete_id'])) {
    $type = $_GET['delete_type'];
    $delete_id = intval($_GET['delete_id']);
    $table = ($type === 'faculty') ? 'faculty' : 'student';
    $id_col = ($type === 'faculty') ? 'faculty_id' : 'student_id';

    // 🧹 Get profile picture path before deleting
    $pic_query = $conn->prepare("SELECT profile_picture FROM $table WHERE $id_col = ?");
    $pic_query->bind_param("i", $delete_id);
    $pic_query->execute();
    $pic_result = $pic_query->get_result();
    $pic = ($pic_result->num_rows > 0) ? $pic_result->fetch_assoc()['profile_picture'] : '';
    $pic_query->close();

    // 🗑 Delete record
    $stmt = $conn->prepare("DELETE FROM $table WHERE $id_col = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        if (!empty($pic) && file_exists("../" . $pic) && strpos($pic, "default_avatar.png") === false) {
            unlink("../" . $pic);
        }
        mysqli_query($conn, "ALTER TABLE $table AUTO_INCREMENT = 1");
        $_SESSION['success_message'] = ucfirst($type) . " deleted successfully and photo removed.";
    } else {
        $_SESSION['error_message'] = "Failed to delete " . $type . ".";
    }

    header("Location: user_list.php");
    exit();
}

// ----------------------------
// Fetch Students
// ----------------------------
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$likeSearch = "%$search%";

$query_students = "SELECT * FROM student WHERE full_name LIKE ? OR email LIKE ? ORDER BY created_at DESC";
$stmt_students = $conn->prepare($query_students);
$stmt_students->bind_param("ss", $likeSearch, $likeSearch);
$stmt_students->execute();
$students = $stmt_students->get_result();

// ----------------------------
// Fetch Faculty
// ----------------------------
$query_faculty = "SELECT * FROM faculty WHERE full_name LIKE ? OR email LIKE ? ORDER BY created_at DESC";
$stmt_faculty = $conn->prepare($query_faculty);
$stmt_faculty->bind_param("ss", $likeSearch, $likeSearch);
$stmt_faculty->execute();
$faculty = $stmt_faculty->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin | User Management</title>
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
    width: 85%;
    margin: 40px auto;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  h2 { color: #1e3a8a; margin-bottom: 20px; }

  .section-title {
    color: #1e3a8a;
    border-left: 6px solid #1e40af;
    padding-left: 10px;
    font-size: 20px;
    margin-top: 30px;
  }

  .alert {
    padding: 10px;
    border-radius: 6px;
    font-weight: 500;
    margin-bottom: 20px;
    text-align: center;
  }
  .success { background: #d1fae5; color: #065f46; }
  .error { background: #fee2e2; color: #991b1b; }

  .search-bar {
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
  }

  .search-bar input {
    padding: 8px 12px;
    width: 250px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
  }

  .search-bar button {
    background: #1e40af;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
  }
  .search-bar button:hover { background: #1d4ed8; }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
  }

  th, td {
    border: 1px solid #e2e8f0;
    padding: 10px;
    text-align: center;
  }

  th { background-color: #e0e7ff; color: #1e3a8a; }
  tr:hover { background: #f8fafc; }

  .profile-pic {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
  }

  /* ✨ Improved Button Design */
  .btn {
    display: inline-block;
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    color: white;
    margin: 3px;
    transition: all 0.25s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  }

  .btn-view {
    background: linear-gradient(135deg, #2563eb, #1e40af);
  }
  .btn-view:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
  }

  .btn-approve {
    background: linear-gradient(135deg, #16a34a, #15803d);
  }
  .btn-approve:hover {
    background: linear-gradient(135deg, #22c55e, #15803d);
  }

  .btn-delete {
    background: linear-gradient(135deg, #dc2626, #991b1b);
  }
  .btn-delete:hover {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
  }

  .modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    justify-content: center; align-items: center;
  }

  .modal-content {
    background: white;
    padding: 25px;
    border-radius: 10px;
    width: 380px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  }

  .modal-content img {
    width: 90px; height: 90px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
  }

  .close {
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    cursor: pointer;
    margin-top: 10px;
  }
</style>
</head>
<body>

<div class="navbar">
  <div class="logo">E-Scription</div>
  <div class="links">
    <a href="admin_dashboard.php">🏠 Home</a>
    <a href="manage_request.php">📂 Manage Requests</a>
    <a href="user_list.php">👥 User Management</a>
    <a href="transaction_log.php">🕒 Transaction Logs</a>
    <a href="admin_profile.php">👤 Profile</a>
    <a href="reset_system.php"> 📊 Database/File Management</a>
  </div>
  <a href="../logout.php" class="logout">Logout</a>
</div>

<div class="container">
  <h2>User Management</h2>

  <?php
  if (isset($_SESSION['success_message'])) {
      echo "<div class='alert success'>{$_SESSION['success_message']}</div>";
      unset($_SESSION['success_message']);
  }
  if (isset($_SESSION['error_message'])) {
      echo "<div class='alert error'>{$_SESSION['error_message']}</div>";
      unset($_SESSION['error_message']);
  }
  ?>

  <form class="search-bar" method="GET">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email...">
    <button type="submit">Search</button>
  </form>

  <!-- STUDENT SECTION -->
  <h3 class="section-title">🎓 Student Accounts</h3>
  <table>
    <tr>
      <th>Profile</th>
      <th>Student ID</th>
      <th>Student No.</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Course</th>
      <th>Status</th>
      <th>Registered Date</th>
      <th>Action</th>
    </tr>
    <?php
    if ($students->num_rows > 0) {
        while ($student = $students->fetch_assoc()) {
            $pic = !empty($student['profile_picture'])
                ? '../uploads/profile_pics/' . basename($student['profile_picture'])
                : '../uploads/default_avatar.png';
            echo "<tr>
                    <td><img src='$pic' class='profile-pic'></td>
                    <td>{$student['student_id']}</td>
                    <td>{$student['student_number']}</td>
                    <td>{$student['full_name']}</td>
                    <td>{$student['email']}</td>
                    <td>{$student['course']}</td>
                    <td><span style='color:" . 
                      ($student['status']=='Approved' ? "#16a34a" : 
                      ($student['status']=='Rejected' ? "#dc2626" : "#ca8a04")) . 
                      "; font-weight:600;'>" . htmlspecialchars($student['status']) . "</span></td>
                    <td>{$student['created_at']}</td>
                    <td>";
            if ($student['status'] === 'Pending') {
                echo "<a href='user_list.php?action=approve&type=student&id={$student['student_id']}' class='btn btn-approve'>✅ Approve</a>
                      <a href='user_list.php?action=reject&type=student&id={$student['student_id']}' class='btn btn-delete'>❌ Reject</a>";
            }
            echo "<a href='#' class='btn btn-view' onclick='viewUser(\"{$student['full_name']}\", \"{$student['email']}\", \"{$student['course']}\", \"{$student['created_at']}\", \"$pic\")'>🔍 View</a>
                  <a href='user_list.php?delete_type=student&delete_id={$student['student_id']}' class='btn btn-delete' onclick='return confirm(\"Delete this student?\")'>🗑 Delete</a>
                  </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='9'>No students found.</td></tr>";
    }
    ?>
  </table>

  <!-- FACULTY SECTION -->
  <h3 class="section-title">👨‍🏫 Faculty Accounts</h3>
  <table>
    <tr>
      <th>Profile</th>
      <th>Faculty ID</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Department</th>
      <th>Status</th>
      <th>Registered Date</th>
      <th>Action</th>
    </tr>
    <?php
    if ($faculty->num_rows > 0) {
        while ($f = $faculty->fetch_assoc()) {
            $pic = !empty($f['profile_picture'])
                ? '../uploads/profile_pics/' . basename($f['profile_picture'])
                : '../uploads/default_avatar.png';
            echo "<tr>
                    <td><img src='$pic' class='profile-pic'></td>
                    <td>{$f['faculty_id']}</td>
                    <td>{$f['full_name']}</td>
                    <td>{$f['email']}</td>
                    <td>{$f['department']}</td>
                    <td><span style='color:" .
                      ($f['status']=='Approved' ? "#16a34a" :
                      ($f['status']=='Rejected' ? "#dc2626" : "#ca8a04")) .
                      "; font-weight:600;'>" . htmlspecialchars($f['status']) . "</span></td>
                    <td>{$f['created_at']}</td>
                    <td>";
            if ($f['status'] === 'Pending') {
                echo "<a href='user_list.php?action=approve&type=faculty&id={$f['faculty_id']}' class='btn btn-approve'>✅ Approve</a>
                      <a href='user_list.php?action=reject&type=faculty&id={$f['faculty_id']}' class='btn btn-delete'>❌ Reject</a>";
            }
            echo "<a href='#' class='btn btn-view' onclick='viewUser(\"{$f['full_name']}\", \"{$f['email']}\", \"{$f['department']}\", \"{$f['created_at']}\", \"$pic\")'>🔍 View</a>
                  <a href='user_list.php?delete_type=faculty&delete_id={$f['faculty_id']}' class='btn btn-delete' onclick='return confirm(\"Delete this faculty member?\")'>🗑 Delete</a>
                  </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='8'>No faculty members found.</td></tr>";
    }
    ?>
  </table>
</div>

<!-- Modal -->
<div class="modal" id="userModal">
  <div class="modal-content">
    <img id="modalPic" src="" alt="Profile Picture">
    <h3 id="modalName"></h3>
    <p id="modalEmail"></p>
    <p id="modalInfo"></p>
    <p id="modalDate"></p>
    <button class="close" onclick="closeModal()">Close</button>
  </div>
</div>

<script>
function viewUser(name, email, info, date, pic) {
  document.getElementById('modalName').textContent = name;
  document.getElementById('modalEmail').textContent = "📧 " + email;
  document.getElementById('modalInfo').textContent = "📘 " + info;
  document.getElementById('modalDate').textContent = "🗓 Registered: " + date;
  document.getElementById('modalPic').src = pic;
  document.getElementById('userModal').style.display = 'flex';
}
function closeModal() {
  document.getElementById('userModal').style.display = 'none';
}
</script>

</body>
</html>
