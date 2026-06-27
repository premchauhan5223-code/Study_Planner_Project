<?php
session_start();
include('backend/db_connect.php');

// Include PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/prem.css">
</head>
<body>
<header class="navbar">
  <div class="container">
    <h1 class="logo"><img src="image/logo22.png" alt="logo" class="logo-img">StudyPlanner</h1>
  </div>
</header>

<section class="form-section">
  <div class="form-container">
    <h2>Forgot Password</h2>
    <p>Enter your email to receive OTP</p>

    <form method="POST">
      <input type="email" name="email" placeholder="Enter your registered email" required>
      <button type="submit" name="send_otp" class="btn primary">Send OTP</button>
    </form>

<?php
if (isset($_POST['send_otp'])) {
    $email = $_POST['email'];
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['reset_email'] = $email;

    // Check user exists
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {

        // ---------- PHPMailer START ----------
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'premchauhan5223@gmail.com'; 
            $mail->Password = 'pwti htcx ltnw qwjg';   
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('premchauhan5223@gmail.com', 'StudyPlanner');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Your StudyPlanner Password Reset OTP";
            $mail->Body    = "<h3>Your OTP for password reset is: <b>$otp</b></h3>";

             $mail->send();

            // ✅ Show popup on success
            echo "<script>alert('✅ OTP sent successfully to your email!');</script>";

        } catch (Exception $e) {
            echo "<script>alert('❌ Failed to send OTP. Please try again!');</script>";
        }

    } else {
        // ⚠️ Invalid email popup
        echo "<script>alert('⚠️ No user found with this email.');</script>";
    }
}
?>

    <form method="POST" action="reset_password.php">
      <input type="text" name="otp" placeholder="Enter OTP" required>
      <button type="submit" name="verify_otp" class="btn primary">Verify OTP</button>
    </form>

    <a href="login.html" class="back-btn">← Back to Login</a>
  </div>
</section>

<footer><p>© 2025 StudyPlanner. All rights reserved.</p></footer>
</body>
</html>
