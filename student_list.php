<?php
session_start();
include("db_connect.php");

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

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM student WHERE student_id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Student deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete student.";
    }
    header("Location: student_list.php");
    exit();
}

// Search query
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$query = "SELECT * FROM student WHERE full_name LIKE ? OR email LIKE ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$likeSearch = "%$search%";
$stmt->bind_param("ss", $likeSearch, $likeSearch);
$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin | Student List</title>
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

  .btn {
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    color: white;
    margin: 0 3px;
  }

  .btn-view { background: #2563eb; }
  .btn-view:hover { background: #1d4ed8; }
  .btn-delete { background: #dc2626; }
  .btn-delete:hover { background: #b91c1c; }

  /* Modal */
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
  <h2>Registered Students</h2>

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

  <table>
    <tr>
      <th>Profile</th>
      <th>Student ID</th>
      <th>Student Number</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Course</th>
      <th>Date Registered</th>
      <th>Action</th>
    </tr>
    <?php
    if ($students->num_rows > 0) {
        while ($student = $students->fetch_assoc()) {
            $pic = !empty($student['profile_picture']) ? $student['profile_picture'] : 'uploads/default_avatar.png';

            echo "<tr>
                    <td><img src='$pic' class='profile-pic'></td>
                    <td>{$student['student_id']}</td>
                    <td>{$student['student_number']}</td>
                    <td>{$student['full_name']}</td>
                    <td>{$student['email']}</td>
                    <td>{$student['course']}</td>
                    <td>{$student['created_at']}</td>
                    <td>
                      <a href='#' class='btn btn-view' onclick='viewStudent(\"{$student['student_number']}\", \"{$student['full_name']}\", \"{$student['email']}\", \"{$student['course']}\", \"{$student['created_at']}\", \"$pic\")'>View</a>
                      <a href='student_list.php?delete_id={$student['student_id']}' class='btn btn-delete' onclick='return confirm(\"Delete this student?\")'>Delete</a>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='8'>No students found.</td></tr>";
    }
    ?>
  </table>
</div>

<!-- View Modal -->
<div class="modal" id="studentModal">
  <div class="modal-content">
    <img id="modalPic" src="" alt="Profile Picture">
    <h3 id="modalName"></h3>
    <p id="modalNumber"></p>
    <p id="modalEmail"></p>
    <p id="modalCourse"></p>
    <p id="modalDate"></p>
    <button class="close" onclick="closeModal()">Close</button>
  </div>
</div>

<script>
function viewStudent(number, name, email, course, date, pic) {
  document.getElementById('modalNumber').textContent = "🆔 Student No: " + number;
  document.getElementById('modalName').textContent = name;
  document.getElementById('modalEmail').textContent = "📧 " + email;
  document.getElementById('modalCourse').textContent = "🎓 " + course;
  document.getElementById('modalDate').textContent = "🗓 Registered: " + date;
  document.getElementById('modalPic').src = pic;
  document.getElementById('studentModal').style.display = 'flex';
}
function closeModal() {
  document.getElementById('studentModal').style.display = 'none';
}
</script>

</body>
</html>
