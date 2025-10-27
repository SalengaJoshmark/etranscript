<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>E-Transcript Request System | Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #c7e9fb, #e0f7ef);
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow-x: hidden;
    }

    /* Header */
    .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: #1e40af;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
      z-index: 10;
    }

    .header h1 {
      font-size: 20px;
      margin: 0;
      white-space: nowrap;
    }

    .header span {
      font-size: 13px;
      opacity: 0.9;
      text-align: right;
    }

    /* Login Box */
    .login-box {
      background: #ffffff;
      padding: 40px 45px;
      border-radius: 14px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 360px;
      text-align: center;
      margin-top: 110px;
      animation: fadeIn 0.4s ease-in-out;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(15px);}
      to {opacity: 1; transform: translateY(0);}
    }

    h2 {
      color: #1e3a8a;
      margin-bottom: 20px;
      font-size: 22px;
    }

    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 11px 12px;
      margin: 10px 0;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      transition: border-color 0.2s;
    }

    input:focus {
      outline: none;
      border-color: #1e40af;
      box-shadow: 0 0 3px rgba(30,64,175,0.3);
    }

    .password-container {
      position: relative;
      width: 100%;
    }

    .password-container input {
      padding-right: 40px;
    }

    .toggle-password {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #64748b;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 22px;
      height: 22px;
    }

    .toggle-password svg {
      width: 22px;
      height: 22px;
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
      margin-top: 5px;
    }

    button:hover {
      background: #1d4ed8;
      transform: translateY(-1px);
    }

    .toggle-link {
      margin-top: 16px;
      color: #1e3a8a;
      cursor: pointer;
      font-size: 14px;
      display: inline-block;
      transition: 0.2s;
    }

    .toggle-link:hover {
      text-decoration: underline;
    }

    .forgot-password {
      margin-top: 12px;
    }

    .forgot-password a {
      color: #1e3a8a;
      font-size: 13.5px;
      text-decoration: none;
    }

    .forgot-password a:hover {
      text-decoration: underline;
    }

    .create-account {
      margin-top: 22px;
      font-size: 14px;
      color: #374151;
    }

    .create-account a {
      color: #1e3a8a;
      text-decoration: none;
      font-weight: 500;
    }

    .create-account a:hover {
      text-decoration: underline;
    }

    footer {
      position: fixed;
      bottom: 10px;
      font-size: 12px;
      color: #475569;
      text-align: center;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="header">
    <h1>E-Transcript Request System</h1>
    <span>Automated & Secure Academic Document Requests</span>
  </div>

  <!-- Login Box -->
  <div class="login-box">
    <h2 id="login-title">Student Login</h2>

    <form method="POST" action="login.php" id="loginForm">
      <input type="hidden" name="role" id="role" value="student">

      <input type="text" name="email" placeholder="Email or Student Number" required>

      <div class="password-container">
        <input type="password" name="password" id="password" placeholder="Password" required>
        <span class="toggle-password" onclick="togglePassword()">
          <!-- Eye Icon (Visible) -->
          <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <!-- Eye Off Icon (Hidden) -->
          <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.348-3.63M9.88 9.88A3 3 0 0012 15a3 3 0 002.12-.88M6.1 6.1l11.8 11.8" />
          </svg>
        </span>
      </div>

      <button type="submit">Login</button>
    </form>

    <div class="toggle-link" id="switchLink" onclick="toggleLogin()">Login as Admin</div>

    <div class="forgot-password">
      <a href="forgot_password.php">Forgot Password?</a>
    </div>

    <div class="create-account">
      Don’t have an account? <a href="register.php">Create Account</a>
    </div>
  </div>

  <footer>
    © 2025 E-Transcript Request System | Developed for Academic Purposes
  </footer>

  <!-- JS -->
  <script>
    function toggleLogin() {
      const roleInput = document.getElementById("role");
      const title = document.getElementById("login-title");
      const switchLink = document.getElementById("switchLink");

      if (roleInput.value === "student") {
        roleInput.value = "admin";
        title.textContent = "Admin Login";
        switchLink.textContent = "Login as Student";
      } else {
        roleInput.value = "student";
        title.textContent = "Student Login";
        switchLink.textContent = "Login as Admin";
      }
    }

    function togglePassword() {
      const passwordField = document.getElementById("password");
      const eyeOpen = document.getElementById("eye-open");
      const eyeClosed = document.getElementById("eye-closed");

      if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeOpen.style.display = "none";
        eyeClosed.style.display = "block";
      } else {
        passwordField.type = "password";
        eyeOpen.style.display = "block";
        eyeClosed.style.display = "none";
      }
    }
  </script>

</body>
</html>
