<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>E-Transcript Request System | Create Account</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- ✅ Font Awesome for Eye Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #c7e9fb, #e0f7ef);
      margin: 0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .header {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      background: #1e40af;
      color: white;
      padding: 15px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
      box-sizing: border-box;
    }

    .header h1 {
      font-size: 22px;
      margin: 0;
    }

    .header span {
      font-size: 14px;
      opacity: 0.9;
    }

    .register-box {
      background: #ffffff;
      padding: 45px 50px;
      border-radius: 14px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      width: 400px;
      text-align: center;
      animation: fadeIn 0.5s ease-in-out;
      margin-top: 60px;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(20px);}
      to {opacity: 1; transform: translateY(0);}
    }

    h2 {
      color: #1e3a8a;
      margin-bottom: 20px;
      font-size: 22px;
    }

    input[type="text"], input[type="email"], input[type="password"], select {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      box-sizing: border-box;
    }

    input[type="file"] {
      margin-top: 10px;
      font-size: 14px;
      width: 100%;
    }

    /* --- Password Field Styling --- */
    .password-container {
      position: relative;
      width: 100%;
      margin-bottom: 10px;
    }

    .password-container input {
      width: 100%;
      padding-right: 40px; /* space for eye icon */
    }

    .toggle-password {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6b7280;
      font-size: 18px;
      transition: color 0.2s ease;
      user-select: none;
    }

    .toggle-password:hover {
      color: #1e40af;
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

    .login-link {
      margin-top: 18px;
      color: #1e3a8a;
      font-size: 14px;
    }

    .login-link a {
      text-decoration: none;
      color: #1e3a8a;
      font-weight: 500;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    footer {
      position: absolute;
      bottom: 10px;
      font-size: 12px;
      color: #475569;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="header">
    <h1>E-Transcript Request System</h1>
    <span>Automated & Secure Academic Document Requests</span>
  </div>

  <div class="register-box">
    <h2>Create Account</h2>

    <form method="POST" action="register_process.php" enctype="multipart/form-data">
      <!-- Role -->
      <select name="role" required onchange="toggleRoleFields(this.value)">
        <option value="" disabled selected>Select Account Type</option>
        <option value="student">Student</option>
        <option value="admin">Admin</option>
      </select>

      <!-- Shared Fields -->
      <input type="text" name="full_name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email Address" required>

      <!-- ✅ Admin Only Fields -->
      <div id="adminFields" style="display:none;">
        <input type="text" name="username" placeholder="Admin Username">
      </div>

      <!-- ✅ Student Only Fields -->
      <div id="studentFields">
        <input type="text" name="course" placeholder="Course (e.g., BSIT)">
        <input type="text" name="student_number" placeholder="Student Number">
      </div>

      <!-- Profile Picture -->
      <label style="display:block; text-align:left; font-size:13px; margin-top:10px;">Upload Profile Picture:</label>
      <input type="file" name="profile_picture" accept="image/*">

      <!-- Password Fields -->
      <div class="password-container">
        <input type="password" name="password" id="password" placeholder="Password" required>
        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('password', this)"></i>
      </div>

      <div class="password-container">
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('confirm_password', this)"></i>
      </div>

      <button type="submit">Create Account</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="index.php">Login here</a>
    </div>
  </div>

  <footer>
    © 2025 E-Transcript Request System | Developed for Academic Purposes
  </footer>

  <script>
    function togglePassword(id, icon) {
      const field = document.getElementById(id);
      const isVisible = field.type === "text";
      field.type = isVisible ? "password" : "text";
      icon.classList.toggle("fa-eye");
      icon.classList.toggle("fa-eye-slash");
    }

    // ✅ Toggle visibility of fields depending on selected role
    function toggleRoleFields(role) {
      const studentFields = document.getElementById("studentFields");
      const adminFields = document.getElementById("adminFields");

      if (role === "student") {
        studentFields.style.display = "block";
        adminFields.style.display = "none";
      } else if (role === "admin") {
        studentFields.style.display = "none";
        adminFields.style.display = "block";
      } else {
        studentFields.style.display = "none";
        adminFields.style.display = "none";
      }
    }
  </script>

</body>
</html>
