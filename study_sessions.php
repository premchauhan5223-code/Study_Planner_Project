<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.html');
    exit();
}
include 'backend/db_connect.php';

$user_id = $_SESSION['user']['id'];

// --- DELETE SESSION ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM study_sessions WHERE id=$id AND user_id=$user_id");
    $conn->query("DELETE FROM study_session_reminders WHERE session_id=$id AND user_id=$user_id");
    header("Location: study_sessions.php");
    exit();
}

// --- ADD / EDIT SESSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $duration = intval($_POST['duration']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time = mysqli_real_escape_string($conn, $_POST['session_time']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $reminders = $_POST['reminders'] ?? [];
    $session_id = $_POST['session_id'] ?? '';

    if ($session_id) {
        $conn->query("UPDATE study_sessions 
                      SET subject='$subject', duration=$duration, date='$date', session_time='$time', notes='$notes'
                      WHERE id=$session_id AND user_id=$user_id");
        $conn->query("DELETE FROM study_session_reminders WHERE session_id=$session_id AND user_id=$user_id");
        $id_to_use = $session_id;
    } else {
        $conn->query("INSERT INTO study_sessions (user_id, subject, duration, date, session_time, notes)
                      VALUES ($user_id,'$subject',$duration,'$date','$time','$notes')");
        $id_to_use = $conn->insert_id;
    }

    foreach ($reminders as $hrs) {
        $hrs = intval($hrs);
        $remind_at = date('Y-m-d H:i:s', strtotime("$date $time -$hrs hours"));
        $conn->query("INSERT INTO study_session_reminders (user_id, session_id, remind_at, sent)
                      VALUES ($user_id, $id_to_use, '$remind_at', 0)");
    }

    header("Location: study_sessions.php");
    exit();
}

$result = $conn->query("SELECT * FROM study_sessions WHERE user_id=$user_id ORDER BY date DESC");

$todayRes = $conn->query("SELECT SUM(duration) as total FROM study_sessions WHERE user_id=$user_id AND DATE(date)=CURDATE()");
$todayTotal = $todayRes->fetch_assoc()['total'] ?? 0;

$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));
$weekRes = $conn->query("SELECT SUM(duration) as total FROM study_sessions WHERE user_id=$user_id AND date BETWEEN '$week_start' AND '$week_end'");
$weekTotal = $weekRes->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Study Sessions | StudyPlanner</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/style.css" />
<script defer src="script.js"></script>
</head>
<body>
<aside class="sidebar">
    <h2 class="sidebar-logo"><img src="image/logo22.png" alt="StudyPlanner logo" class="logo-img"> StudyPlanner</h2>
    <ul class="menu">
        <li><a href="dashboard.php">🏠 Dashboard</a></li>
        <li><a href="activity.php">📊 Activity</a></li>
        <li><a href="class_schedule.php">📅 Class Schedule</a></li>
        <li><a href="assignments.php">📝 Assignments</a></li>
        <li class="active"><a href="study_sessions.php">⏰ Study Sessions</a></li>
        <h3><li><a href="#" id="logoutBtn"> Logout </a></li></h3>
    </ul>
</aside>

<main class="main-content">
<header class="dashboard-header">
    <div class="profile-wrap">
        <?php
            $img = 'backend/uploads/default.png';
            if (!empty($_SESSION['user']['profile_img']) && file_exists('backend/uploads/' . $_SESSION['user']['profile_img'])) {
                $img = 'backend/uploads/' . htmlspecialchars($_SESSION['user']['profile_img']);
            }
        ?>
        <img src="<?php echo $img; ?>" alt="Profile" class="profile-img" id="profileImg" />
        <div>
            <h1><?php echo htmlspecialchars($_SESSION['user']['name']); ?> 👋</h1>
            <p>Track your study sessions and time logs</p>
        </div>
    </div>
</header>

<section class="dashboard-section">
<h2>Study Sessions</h2><br>
<div class="session-header">
    <p>Monitor your daily and weekly study progress</p>
    <button class="btn-primary" onclick="openSessionModal()">+ Add Session</button>
</div>

<div class="session-cards">
    <div class="session-card">
        <h3>⏰ Today’s Study Time</h3>
        <h1><?php echo floor($todayTotal/60).'h '.($todayTotal%60).'m'; ?></h1>
        <p>Keep focusing!</p>
    </div>
    <div class="session-card">
        <h3>📅 This Week</h3>
        <h1><?php echo floor($weekTotal/60).'h '.($weekTotal%60).'m'; ?></h1>
        <p>Nice consistency!</p>
    </div>
</div>

<div class="recent-sessions">
<h3>Recent Sessions</h3>
<?php if ($result->num_rows > 0): ?>
<table class="assign-table">
<tr>
    <th>Subject</th>
    <th>Duration (min)</th>
    <th>Date & Time</th>
    <th>Notes</th>
    <th>Action</th>
</tr>
<?php while($row=$result->fetch_assoc()): ?>
<tr>
    <td><?php echo htmlspecialchars($row['subject']); ?></td>
    <td><?php echo $row['duration']; ?></td>
    <td><?php echo htmlspecialchars($row['date'] . ' ' . $row['session_time']); ?></td>
    <td><?php echo htmlspecialchars($row['notes']); ?></td>
    <td>
        <button class="edit-btn"
        onclick="editSession(<?php echo $row['id']; ?>,'<?php echo addslashes($row['subject']); ?>',<?php echo $row['duration']; ?>,'<?php echo $row['date']; ?>','<?php echo $row['session_time']; ?>','<?php echo addslashes($row['notes']); ?>')">Edit</button>
        <a href="?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Delete this session?')">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No study sessions recorded yet. Start tracking your study time!</p>
<?php endif; ?>
</div>
</section>
</main>

<div id="sessionModal" class="modal">
<div class="modal-content">
    <h2 id="sessionTitle">Add Study Session</h2>
    <form method="POST">
        <input type="hidden" name="session_id" id="session_id">
        <label>Subject</label>
        <input type="text" name="subject" id="subject" required>
        <label>Duration (in minutes)</label>
        <input type="number" name="duration" id="duration" required>
        <label>Date</label>
        <input type="date" name="date" id="date" required>
        <label>Time</label>
        <input type="time" name="session_time" id="time" required>
        <label>Notes</label>
        <textarea name="notes" id="notes" rows="3"></textarea>
        <label>Reminders</label>
        <div>
            <label><input type="checkbox" name="reminders[]" value="1"> 1 hour before</label>
            <label><input type="checkbox" name="reminders[]" value="2"> 2 hours before</label>
            <label><input type="checkbox" name="reminders[]" value="3"> 3 hours before</label>
        </div>
        <div class="modal-buttons">
            <button type="submit" class="yes">Save</button>
            <button type="button" class="cancel" onclick="closeSessionModal()">Cancel</button>
        </div>
    </form>
</div>
</div>

<div id="logoutModal" class="modal">
<div class="modal-content">
<h2>Confirm Logout</h2>
<p>Are you sure you want to logout?</p>
<div class="modal-buttons">
<button id="confirmLogout" class="yes">Yes, Logout</button>
<button id="cancelLogout" class="cancel">Cancel</button>
</div>
</div>
</div>

</body>
</html>
