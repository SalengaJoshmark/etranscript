<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("db_connect.php");

// ✅ Fetch departments
$departments = [];
$result = $conn->query("SELECT department_id, department_name FROM department ORDER BY department_name ASC");
while ($row = $result->fetch_assoc()) {
  $departments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>E-Scription | Create Account</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.12);
      width: 420px;
      text-align: center;
      margin-top: 70px;
      animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(20px);}
      to {opacity: 1; transform: translateY(0);}
    }

    h2 {
      color: #1e3a8a;
      margin-bottom: 25px;
      font-size: 22px;
    }

    label {
      text-align: left;
      display: block;
      margin-top: 10px;
      font-size: 13px;
      font-weight: 500;
      color: #374151;
    }

    input, select {
      width: 100%;
      padding: 10px 12px;
      margin-top: 5px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      font-size: 14px;
      box-sizing: border-box;
      outline: none;
      transition: 0.2s;
    }

    input:focus, select:focus {
      border-color: #1e40af;
      box-shadow: 0 0 0 2px rgba(30,64,175,0.2);
    }

    .password-container {
      position: relative;
      margin-top: 10px;
    }

    .password-container input {
      padding-right: 40px;
    }

    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6b7280;
      font-size: 18px;
      transition: color 0.2s;
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
      margin-top: 18px;
      transition: all 0.3s;
    }

    button:hover {
      background: #1d4ed8;
      transform: translateY(-2px);
    }

    .login-link {
      margin-top: 15px;
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
      <select name="role" required onchange="toggleRoleFields(this.value)">
        <option value="" disabled selected>Select Account Type</option>
        <option value="student">Student</option>
        <option value="faculty">Faculty</option>
        <option value="admin">Admin</option>
      </select>

      <input type="text" name="full_name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email Address" required>

      <!-- Admin Only -->
      <div id="adminFields" style="display:none;">
        <input type="text" name="username" placeholder="Admin Username">
      </div>

      <!-- Student Only -->
      <div id="studentFields" style="display:none;">
        <label>Department:</label>
        <select name="department" id="student_department" onchange="loadCourses(this.value)">
          <option value="" disabled selected>Select Department</option>
          <?php foreach ($departments as $dept): ?>
            <option value="<?= $dept['department_id']; ?>"><?= htmlspecialchars($dept['department_name']); ?></option>
          <?php endforeach; ?>
        </select>

        <label>Course:</label>
        <select name="course" id="courseDropdown">
          <option value="" disabled selected>Select Course</option>
        </select>
      </div>

      <!-- Faculty Only -->
      <div id="facultyFields" style="display:none;">
        <label>Department:</label>
        <select name="department" id="faculty_department">
          <option value="" disabled selected>Select Department</option>
          <?php foreach ($departments as $dept): ?>
            <option value="<?= $dept['department_id']; ?>"><?= htmlspecialchars($dept['department_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <label>Upload Profile Picture:</label>
      <input type="file" name="profile_picture" accept="image/*">

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
    // ✅ Show/Hide password
    function togglePassword(id, icon) {
      const field = document.getElementById(id);
      field.type = field.type === "password" ? "text" : "password";
      icon.classList.toggle("fa-eye");
      icon.classList.toggle("fa-eye-slash");
    }

    // ✅ Role visibility
    function toggleRoleFields(role) {
      const student = document.getElementById("studentFields");
      const faculty = document.getElementById("facultyFields");
      const admin = document.getElementById("adminFields");
      student.style.display = faculty.style.display = admin.style.display = "none";

      document.querySelectorAll("#studentFields select, #facultyFields select, #adminFields input").forEach(el => el.required = false);

      if (role === "student") {
        student.style.display = "block";
        document.getElementById("student_department").required = true;
        document.getElementById("courseDropdown").required = true;
      } else if (role === "faculty") {
        faculty.style.display = "block";
        document.getElementById("faculty_department").required = true;
      } else if (role === "admin") {
        admin.style.display = "block";
        admin.querySelector("input").required = true;
      }
    }

    // ✅ Dynamic course loading
    function loadCourses(deptId) {
      const courseDropdown = document.getElementById("courseDropdown");
      courseDropdown.innerHTML = "<option>Loading...</option>";
      fetch(`load_courses.php?department_id=${deptId}`)
        .then(res => res.json())
        .then(data => {
          courseDropdown.innerHTML = '<option value="" disabled selected>Select Course</option>';
          data.forEach(course => {
            const opt = document.createElement("option");
            opt.value = course.course_id;
            opt.textContent = course.course_name;
            courseDropdown.appendChild(opt);
          });
        })
        .catch(() => {
          courseDropdown.innerHTML = "<option>Error loading courses</option>";
        });
    }
  </script>

</body>
</html>
