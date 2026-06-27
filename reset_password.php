<?php
session_start();
include('backend/db_connect.php');

// Redirect if session missing
if (!isset($_SESSION['otp']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

// ✅ AJAX Request Handle
if (isset($_POST['action']) && $_POST['action'] == "reset_password") {
    $otp = $_POST['otp'];
    $new_pass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];

    if ($otp != $_SESSION['otp']) {
        echo json_encode(["status" => "error", "message" => "❌ Invalid OTP!"]);
    } elseif ($new_pass != $confirm) {
        echo json_encode(["status" => "error", "message" => "⚠️ Passwords do not match!"]);
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $update = $conn->query("UPDATE users SET password='$hash' WHERE email='$email'");
        if ($update) {
            unset($_SESSION['otp']);
            unset($_SESSION['reset_email']);
            echo json_encode(["status" => "success", "message" => "✅ Password reset successful! Redirecting..."]);
        } else {
            echo json_encode(["status" => "error", "message" => "❌ Database update failed!"]);
        }
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password - FocusMate</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/prem.css">

</head>

<body>
<header class="navbar">
  <div class="container">
    <h1 class="logo"><img src="image/logo22.png" alt="StudyPlanner logo" class="logo-img"> StudyPlanner</h1>
  </div>
</header>

<section class="form-section">
  <div class="form-container">
    <div class="toast" id="toast"></div> <!-- ✅ Toast Message -->
    <h2>Reset Password</h2>
    
    <form id="resetForm" method="POST">
      <input type="text" name="otp" placeholder="Enter OTP again" required>
      <input type="password" name="new_password" placeholder="Enter New Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
      <button type="submit" name="reset_password" class="btn primary">Reset Password</button>
    </form>

    <a href="forgot_password.php" class="back-btn">← Back to Forgot Password</a>
  </div>
</section>

<footer><p>© 2025 StudyPlanner. All rights reserved.</p></footer>

<script>
// ✅ AJAX - No Refresh Password Reset
document.getElementById("resetForm").addEventListener("submit", function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  formData.append("action", "reset_password");

  fetch("reset_password.php", {
      method: "POST",
      body: formData
  })
  .then(res => res.json())
  .then(data => {
      showToast(data.message, data.status);
      if (data.status === "success") {
          setTimeout(() => window.location.href = "login.html", 2000);
      }
  })
  .catch(() => {
      showToast("⚠️ Something went wrong!", "error");
  });
});

// ✅ Toast Function
function showToast(message, type) {
  const toast = document.getElementById("toast");
  toast.textContent = message;
  toast.className = "toast " + type;
  toast.style.display = "block";
  setTimeout(() => toast.style.display = "none", 2800);
}
</script>

</body>
</html>
