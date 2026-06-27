<?php
require __DIR__ . '/db_connect.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';
require __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set("Asia/Kolkata");

// Current time and 5-minute window for reminders
$now = date("Y-m-d H:i:s");
$window_start = date("Y-m-d H:i:s", strtotime("-5 minutes"));

// Fetch pending study session reminders
$sql = "SELECT r.id AS reminder_id, r.remind_at, s.subject, s.date, s.session_time, u.fullname, u.email
        FROM study_session_reminders r
        JOIN study_sessions s ON r.session_id = s.id
        JOIN users u ON r.user_id = u.id
        WHERE r.sent = 0 AND r.remind_at BETWEEN ? AND ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $window_start, $now);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit("No pending study session reminders.\n");
}

// Send emails
while ($row = $result->fetch_assoc()) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "premchauhan5223@gmail.com"; // your Gmail
        $mail->Password = "pwti htcx ltnw qwjg";         // ← replace with Gmail App Password
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->setFrom("premchauhan5223@gmail.com", "StudyPlanner");
        $mail->addAddress($row['email']);
        $mail->isHTML(true);

        $session_datetime = $row['date'] . ' ' . $row['session_time'];
        $mail->Subject = "📚 Study Session Reminder - " . $row['subject'];
        $mail->Body = "
            Hi {$row['fullname']},<br><br>
            This is a reminder for your study session:<br><br>
            <b>Subject:</b> {$row['subject']}<br>
            <b>Session Date & Time:</b> {$session_datetime}<br>
            <b>Reminder Time:</b> {$row['remind_at']}<br><br>
            Stay focused and keep learning! 🎯
        ";

        $mail->send();

        // Mark reminder as sent
        $update = $conn->prepare("UPDATE study_session_reminders SET sent=1 WHERE id=?");
        $update->bind_param("i", $row['reminder_id']);
        $update->execute();

        echo "Reminder sent for '{$row['subject']}' to {$row['email']} at {$row['remind_at']}\n";

    } catch (Exception $e) {
        echo "Mailer Error for '{$row['subject']}' ({$row['email']}): " . $mail->ErrorInfo . "\n";
    }
}
?>
