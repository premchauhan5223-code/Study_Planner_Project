<?php
require __DIR__ . '/db_connect.php';
require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set("Asia/Kolkata");

// Current time + 5-minutes window
$now = date("Y-m-d H:i:s");
$window_start = date("Y-m-d H:i:s", strtotime("-5 minutes"));

echo "Checking reminders between $window_start and $now\n";

// --- FETCH PENDING REMINDERS ---
$sql = "
    SELECT 
        r.id AS reminder_id,
        r.remind_at,
        r.sent,
        r.class_id,
        r.assignment_id,
        c.class_name,
        c.instructor,
        CONCAT(c.class_date,' ',c.class_time) AS class_datetime,
        a.title AS assign_title,
        CONCAT(a.due_date,' ',a.due_time) AS assign_datetime,
        u.fullname,
        u.email
    FROM reminders r
    LEFT JOIN classes c ON r.class_id = c.id
    LEFT JOIN assignments a ON r.assignment_id = a.id
    JOIN users u ON r.user_id = u.id
    WHERE r.sent = 0
      AND r.remind_at BETWEEN ? AND ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $window_start, $now);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No pending reminders found.\n";
}

while ($row = $result->fetch_assoc()) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "premchauhan5223@gmail.com";
        $mail->Password = "pwti htcx ltnw qwjg"; // ← App password 
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->setFrom("premchauhan5223@gmail.com", "StudyPlanner");
        $mail->addAddress($row["email"]);
        $mail->isHTML(true);

        // --- Determine type: Class or Assignment ---
        if (!empty($row['class_name'])) {
            // Class reminder
            $mail->Subject = "Reminder Class : " . $row["class_name"];
            $mail->Body = "
                Hi " . $row["fullname"] . ",<br><br>
                This is a reminder for your upcoming class:<br><br>
                <b>Class:</b> " . $row["class_name"] . "<br>
                <b>Instructor:</b> " . $row["instructor"] . "<br>
                <b>Class Time:</b> " . $row["class_datetime"] . "<br>
                <b>Reminder Time:</b> " . $row["remind_at"] . "<br><br>
                Best of luck for your study session! 😊
            ";
        } else if (!empty($row['assign_title'])) {
            // Assignment reminder
            $mail->Subject = "Assignment Due Date - " . $row["assign_title"];
            $mail->Body = "
                Hi " . $row["fullname"] . ",<br><br>
                This is a reminder for your pending assignment:<br><br>
                <b>Assignment:</b> " . $row["assign_title"] . "<br>
                <b>Due Date & Time:</b> " . $row["assign_datetime"] . "<br>
                <b>Reminder Time:</b> " . $row["remind_at"] . "<br><br>
                Complete it on time! ✅
            ";
        }

        $mail->send();

        // Mark reminder as sent
        $update = $conn->prepare("UPDATE reminders SET sent = 1 WHERE id = ?");
        $update->bind_param("i", $row["reminder_id"]);
        $update->execute();

        echo "✔ Email sent → Reminder ID: " . $row["reminder_id"] . "\n";

    } catch (Exception $e) {
        echo "❌ Mailer Error for Reminder ID " . $row["reminder_id"] . ": " . $mail->ErrorInfo . "\n";
    }
}
