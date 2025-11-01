<?php
include("db_connect.php");
$message = "";
$account_found = false; // Default state

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    // Step 1: Recover account
    if (!empty($email) && !empty($role) && !isset($_POST['new_password'])) {
        if ($role == "student") {
            $sql = "SELECT full_name FROM student WHERE email='$email'";
        } elseif ($role == "faculty") {
            $sql = "SELECT full_name FROM faculty WHERE email='$email'";
        } else {
            $sql = "SELECT full_name FROM admin WHERE email='$email'";
        }

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $message = "✅ Account found for <b>" . htmlspecialchars($row['full_name']) . "</b>. Please enter your new password below.";
            $account_found = true;
        } else {
            $message = "❌ No account found with that email.";
        }
    }

    // Step 2: Update password
    if (isset($_POST['new_password']) && isset($_POST['confirm_password']) && !empty($_POST['role']) && !empty($_POST['email'])) {
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];
        $role = $_POST['role'];
        $email = $_POST['email'];

        if ($new_pass === $confirm_pass) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);

            if ($role == "student") {
                $update = "UPDATE student SET password='$hashed_pass' WHERE email='$email'";
            } elseif ($role == "faculty") {
                $update = "UPDATE faculty SET password='$hashed_pass' WHERE email='$email'";
            } else {
                $update = "UPDATE admin SET password='$hashed_pass' WHERE email='$email'";
            }

            if (mysqli_query($conn, $update)) {
                $message = "✅ Password successfully updated! Redirecting to login...";
                $account_found = false;

                // Redirect to login after 3 seconds
                echo "<meta http-equiv='refresh' content='3;url=index.php'>";
            } else {
                $message = "❌ Error updating password. Please try again.";
                $account_found = true;
            }
        } else {
            $message = "❌ Passwords do not match.";
            $account_found = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password | E-Transcript Request System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #c7e9fb, #e0f7ef);
      margin: 0;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .box {
      background: #fff;
      padding: 40px 50px;
      border-radius: 14px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      width: 380px;
      text-align: center;
    }

    h2 {
      color: #1e3a8a;
      margin-bottom: 10px;
    }

    p {
      color: #475569;
      font-size: 14px;
      margin-bottom: 20px;
    }

    select, input[type="email"], input[type="password"], input[type="text"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0 20px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      box-sizing: border-box;
    }

    button {
      width: 100%;
      padding: 12px;
      background: #1e40af;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 15px;
      transition: 0.3s;
    }

    button:hover {
      background: #1d4ed8;
      transform: translateY(-2px);
    }

    .back-link {
      display: block;
      margin-top: 20px;
      font-size: 14px;
      color: #1e3a8a;
      text-decoration: none;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    .message {
      margin-top: 20px;
      padding: 15px;
      border-radius: 6px;
      font-size: 14px;
      text-align: left;
    }

    .success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
    .error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }

    .password-container {
      position: relative;
      width: 100%;
      display: flex;
      align-items: center;
    }

    .password-container input {
      width: 100%;
      padding: 10px 40px 10px 12px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      box-sizing: border-box;
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      background: none;
      border: none;
      cursor: pointer;
      color: #475569;
      padding: 0;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .toggle-password i {
      font-size: 16px;
    }
  </style>
</head>
<body>

<div class="box">
  <h2>Forgot Password</h2>
  <p>Enter your email and role to reset your password.</p>

  <form method="POST">
    <?php if (!$account_found): ?>
      <select name="role" required>
        <option value="">-- Select Role --</option>
        <option value="student" <?php if(!empty($_POST['role']) && $_POST['role']=='student') echo 'selected'; ?>>Student</option>
        <option value="faculty" <?php if(!empty($_POST['role']) && $_POST['role']=='faculty') echo 'selected'; ?>>Faculty</option>
        <option value="admin" <?php if(!empty($_POST['role']) && $_POST['role']=='admin') echo 'selected'; ?>>Admin</option>
      </select>

      <input type="email" name="email" placeholder="Enter your email" required>
      <button type="submit">Recover Account</button>
    <?php else: ?>
      <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
      <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">

      <div class="password-container">
        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
        <button type="button" class="toggle-password" onclick="togglePassword('new_password', this)">
          <i class="fas fa-eye"></i>
        </button>
      </div>

      <div class="password-container">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)">
          <i class="fas fa-eye"></i>
        </button>
      </div>

      <button type="submit">Update Password</button>
    <?php endif; ?>
  </form>

  <?php if (!empty($message)): ?>
    <div class="message <?php echo (strpos($message, '✅') !== false) ? 'success' : 'error'; ?>">
      <?php echo $message; ?>
    </div>
  <?php endif; ?>

  <a href="index.php" class="back-link">← Back to Login</a>
</div>

<script>
function togglePassword(fieldId, button) {
  const input = document.getElementById(fieldId);
  const icon = button.querySelector('i');
  
  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}
</script>

</body>
</html>
