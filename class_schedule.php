<?php
session_start();
if (!isset($_SESSION['user'])) {
  header('Location: ../index.html');
  exit();
}
include 'backend/db_connect.php';
$user_id = $_SESSION['user']['id'];

/* ---------------------- ADD CLASS ---------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
  $class_name = $conn->real_escape_string($_POST['class_name']);
  $instructor = $conn->real_escape_string($_POST['instructor']);
  $class_date = $_POST['class_date']; // YYYY-MM-DD
  $class_time = $_POST['class_time']; // HH:MM
  $user_id = $_SESSION['user']['id'];

  // Insert class
  $stmt = $conn->prepare("INSERT INTO classes (user_id, class_name, instructor, class_date, class_time, class_day) VALUES (?, ?, ?, ?, ?, ?)");
  $dayName = date('l', strtotime($class_date)); // Monday, Tuesday...
  $stmt->bind_param("isssss", $user_id, $class_name, $instructor, $class_date, $class_time, $dayName);
  $stmt->execute();
  $class_id = $stmt->insert_id;
  $stmt->close();

  // handle reminders array (hours before)
  $reminders = $_POST['reminders'] ?? []; // array of '1','2','3' etc.
  $class_datetime = date('Y-m-d H:i:s', strtotime($class_date . ' ' . $class_time));

  foreach ($reminders as $hrs) {
      $hrs = intval($hrs);
      if ($hrs <= 0) continue;
      $remind_at = date('Y-m-d H:i:s', strtotime($class_datetime . " -{$hrs} hours"));

      // Insert reminder
      $ins = $conn->prepare("INSERT INTO reminders (user_id, class_id, remind_at, sent) VALUES (?, ?, ?, 0)");
      $ins->bind_param("iis", $user_id, $class_id, $remind_at);
      $ins->execute();
      $ins->close();
  }

  header("Location: class_schedule.php");
  exit();
}

/* ---------------------- DELETE CLASS ---------------------- */
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM classes WHERE id=$id AND user_id=$user_id");
  header("Location: class_schedule.php");
  exit();
}

/* ---------------------- EDIT CLASS ---------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_class'])) {

  $id = $_POST['class_id'];
  $class_name = $_POST['class_name'];
  $instructor = $_POST['instructor'];
  $date = $_POST['date'];
  $time = $_POST['time'];
  $day = $_POST['day'];

  $stmt = $conn->prepare("UPDATE classes SET class_name=?, instructor=?, class_date=?, class_time=?, class_day=? WHERE id=? AND user_id=?");
  $stmt->bind_param("ssssssi", $class_name, $instructor, $date, $time, $day, $id, $user_id);
  $stmt->execute();
  header("Location: class_schedule.php");
  exit();
}

/* ---------------------- FETCH ALL CLASSES ---------------------- */
$result = $conn->query("SELECT * FROM classes WHERE user_id=$user_id ORDER BY class_date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Class Schedule | StudyPlanner</title>
<link rel="stylesheet" href="style/style.css" />
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<script defer src="script.js"></script>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <h2 class="sidebar-logo"><img src="image/logo22.png" class="logo-img">StudyPlanner</h2>

  <ul class="menu">
    <li><a href="dashboard.php">🏠 Dashboard</a></li>
    <li><a href="activity.php">📊 Activity</a></li>
    <li class="active"><a href="class_schedule.php">📅 Class Schedule</a></li>
    <li><a href="assignments.php">📝 Assignments</a></li>
    <li><a href="study_sessions.php">⏰ Study Sessions</a></li>
    <h3><li><a href="#" id="logoutBtn">Logout</a></li></h3>
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
    <img src="<?php echo $img; ?>" class="profile-img" />
    <div>
      <h1><?php echo htmlspecialchars($_SESSION['user']['name']); ?> 👋</h1>
      <p>Manage your class schedule</p>
    </div>
  </div>
</header>

<section class="dashboard-section class-schedule">

  <div class="schedule-header">
    <div class="header-text">
      <h2>Your Class Schedule</h2>
      <p>Manage your weekly schedule easily</p>
    </div>
    <button id="addClassBtn" class="add-btn">+ Add Class</button>
  </div>

  <div class="schedule-list">
    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="schedule-card">

          <div class="class-info">
            <h3><?php echo htmlspecialchars($row['class_name']); ?></h3>
            <p><b>Instructor:</b> <?php echo htmlspecialchars($row['instructor']); ?></p>
            <p><b>Date:</b> <?php echo htmlspecialchars($row['class_date']); ?></p>
            <p><b>Time:</b> <?php echo htmlspecialchars($row['class_time']); ?></p>
            <p><b>Day:</b> <?php echo htmlspecialchars($row['class_day']); ?></p>
          </div>

          <div class="action-btns">
            <button class="edit-btn"
              data-id="<?php echo $row['id']; ?>"
              data-name="<?php echo htmlspecialchars($row['class_name']); ?>"
              data-instructor="<?php echo htmlspecialchars($row['instructor']); ?>"
              data-date="<?php echo htmlspecialchars($row['class_date']); ?>"
              data-time="<?php echo htmlspecialchars($row['class_time']); ?>"
              data-day="<?php echo htmlspecialchars($row['class_day']); ?>"
            >Edit</button>

            <a href="class_schedule.php?delete=<?php echo $row['id']; ?>"
               class="delete-btn"
               onclick="return confirm('Delete this class?')">Delete</a>

          </div>

        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="empty-text">No classes yet. Click <b>"Add Class"</b>.</p>
    <?php endif; ?>
  </div>

</section>

</main>

<!-- ADD CLASS MODAL -->
<!-- Add Class Modal (use date + time + reminder checkboxes) -->
<div id="addClassModal" class="modal">
  <div class="modal-content small">
    <h2>Add New Class</h2>
    <form method="POST">
      <input type="hidden" name="add_class" value="1">
      <input type="text" name="class_name" placeholder="Class Name" required>
      <input type="text" name="instructor" placeholder="Instructor" required>

      <!-- date + time inputs -->
      <label>Class Date</label>
      <input type="date" name="class_date" required>

      <label>Class Time</label>
      <input type="time" name="class_time" required>

      <!-- reminder choices (hours before) -->
      <label>Reminders</label>
      <div>
        <label><input type="checkbox" name="reminders[]" value="1"> 1 hour before</label>
        <label><input type="checkbox" name="reminders[]" value="2"> 2 hours before</label>
        <label><input type="checkbox" name="reminders[]" value="3"> 3 hours before</label>
      </div>

      <div class="modal-buttons">
        <button type="submit" class="yes">Save</button>
        <button type="button" id="cancelAddClass" class="cancel">Cancel</button>
      </div>
    </form>
  </div>
</div>


<!-- EDIT CLASS MODAL -->
<div id="editClassModal" class="modal">
  <div class="modal-content small">
    <h2>Edit Class</h2>

    <form method="POST">
      <input type="hidden" name="edit_class" value="1">
      <input type="hidden" name="class_id" id="editClassId">

      <input type="text" name="class_name" id="editClassName" required>
      <input type="text" name="instructor" id="editInstructor" required>

      <label>Date</label>
      <input type="date" name="date" id="editDate" required>

      <label>Time</label>
      <input type="time" name="time" id="editTime" required>

      <input type="text" name="day" id="editDay" required>

      <div class="modal-buttons">
        <button type="submit" class="yes">Update</button>
        <button type="button" id="cancelEditClass" class="cancel">Cancel</button>
      </div>
    </form>

  </div>
</div>

 <!-- Logout Modal -->
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
