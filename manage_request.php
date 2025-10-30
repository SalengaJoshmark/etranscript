<?php
session_start();
include("db_connect.php");

// Redirect if not logged in as admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle rejection only (approval now handled in view_request.php)
if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    if ($action === 'reject') {
        $status = 'Rejected';
        $stmt = $conn->prepare("UPDATE request SET status=? WHERE request_id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success_message'] = "Request #$id marked as Rejected.";
        header("Location: manage_requests.php");
        exit();
    }
}

// Filter by status
$filter = isset($_GET['status']) ? $_GET['status'] : 'All';
$sql = "SELECT r.request_id, s.full_name, r.purpose, r.request_date, r.status
        FROM request r
        JOIN student s ON r.student_id = s.student_id";

if ($filter !== 'All') {
    $sql .= " WHERE r.status = '$filter'";
}
$sql .= " ORDER BY r.request_date DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Requests - Admin Dashboard</title>
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
    color: white; text-decoration: none; margin-right: 20px;
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
    width: 90%;
    margin: 40px auto;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  h2 { color: #1e3a8a; }

  .filter-buttons {
    margin-bottom: 15px;
  }

  .filter-buttons a {
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 6px;
    background: #e0e7ff;
    color: #1e3a8a;
    font-weight: 500;
    margin-right: 8px;
    transition: 0.3s;
  }

  .filter-buttons a.active {
    background: #1e40af;
    color: white;
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th, td {
    border: 1px solid #cbd5e1;
    padding: 10px;
    text-align: center;
  }

  th {
    background-color: #e0e7ff;
    color: #1e3a8a;
  }

  .btn {
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    margin: 0 3px;
  }

  .view { background-color: #2563eb; color: white; }
  .approve { background-color: #16a34a; color: white; }
  .reject { background-color: #dc2626; color: white; }
  .view:hover { background-color: #1d4ed8; }
  .approve:hover { background-color: #15803d; }
  .reject:hover { background-color: #b91c1c; }

  .success {
    background: #d1fae5;
    color: #065f46;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    text-align: center;
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
  <h2>Manage Transcript Requests</h2>

  <?php
  if (isset($_SESSION['success_message'])) {
      echo "<div class='success'>{$_SESSION['success_message']}</div>";
      unset($_SESSION['success_message']);
  }
  ?>

  <div class="filter-buttons">
    <a href="?status=All" class="<?= $filter=='All' ? 'active' : '' ?>">All</a>
    <a href="?status=Pending" class="<?= $filter=='Pending' ? 'active' : '' ?>">Pending</a>
    <a href="?status=Approved" class="<?= $filter=='Approved' ? 'active' : '' ?>">Approved</a>
    <a href="?status=Rejected" class="<?= $filter=='Rejected' ? 'active' : '' ?>">Rejected</a>
  </div>

  <table>
    <tr>
      <th>ID</th>
      <th>Student Name</th>
      <th>Purpose</th>
      <th>Date</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $status_color = match ($row['status']) {
                'Approved' => 'green',
                'Rejected' => 'red',
                default => 'orange'
            };
            echo "<tr>
                    <td>{$row['request_id']}</td>
                    <td>{$row['full_name']}</td>
                    <td>{$row['purpose']}</td>
                    <td>{$row['request_date']}</td>
                    <td style='color:$status_color;font-weight:bold;'>{$row['status']}</td>
                    <td>
                    <a href='view_request.php?id={$row['request_id']}' class='btn view'>View</a>
                    ";

                    if ($row['status'] == 'Pending') {
                        echo "
                        <a href='view_request.php?id={$row['request_id']}' class='btn approve'>Approve</a>
                        <a href='manage_requests.php?action=reject&id={$row['request_id']}' class='btn reject' onclick='return confirm(\"Reject this request?\")'>Reject</a>
                        ";
                    } else {
                        echo "
                        <button class='btn approve' style='opacity:0.5;cursor:not-allowed;' disabled>Approve</button>
                        <button class='btn reject' style='opacity:0.5;cursor:not-allowed;' disabled>Reject</button>
                        ";
                    }

                    echo "
                  </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No requests found.</td></tr>";
    }
    ?>
  </table>
</div>

</body>
</html>
