<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.html');
    exit();
}
include 'backend/db_connect.php';

$user_id = $_SESSION['user']['id'];

// --- DELETE ASSIGNMENT ---
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM assignments WHERE id=$id AND user_id=$user_id");
  $conn->query("DELETE FROM reminders WHERE assignment_id=$id AND user_id=$user_id"); // delete associated assignment reminders
  header("Location: assignments.php");
  exit();
}

// --- ADD / EDIT ASSIGNMENT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = mysqli_real_escape_string($conn, $_POST['title']);
  $desc = mysqli_real_escape_string($conn, $_POST['description']);
  $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
  $due_time = mysqli_real_escape_string($conn, $_POST['due_time']);
  $status = mysqli_real_escape_string($conn, $_POST['status']);
  $assign_id = $_POST['assign_id'] ?? '';
  $reminders = $_POST['reminders'] ?? []; // array of hours before: 24,12

  if ($assign_id) {
    // Update assignment
    $conn->query("UPDATE assignments SET title='$title', description='$desc', due_date='$due_date', due_time='$due_time', status='$status' WHERE id=$assign_id AND user_id=$user_id");

    // Delete old reminders
    $conn->query("DELETE FROM reminders WHERE assignment_id=$assign_id AND user_id=$user_id");

    $assignment_id = $assign_id;
  } else {
    // Insert assignment
    $conn->query("INSERT INTO assignments (user_id, title, description, due_date, due_time, status) VALUES ($user_id,'$title','$desc','$due_date','$due_time','$status')");
    $assignment_id = $conn->insert_id;
  }

  // Insert reminders
  $assignment_datetime = date('Y-m-d H:i:s', strtotime("$due_date $due_time"));
  foreach ($reminders as $hrs) {
      $hrs = intval($hrs);
      if ($hrs <= 0) continue;
      $remind_at = date('Y-m-d H:i:s', strtotime("$assignment_datetime -$hrs hours"));
      $conn->query("INSERT INTO reminders (user_id, assignment_id, remind_at, sent) VALUES ($user_id, $assignment_id, '$remind_at', 0)");
  }

  header("Location: assignments.php");
  exit();
}

// --- FETCH DATA ---
$status_filter = $_GET['status'] ?? 'All';
$query = "SELECT * FROM assignments WHERE user_id=$user_id";
if ($status_filter != 'All') $query .= " AND status='$status_filter'";
$query .= " ORDER BY due_date ASC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Assignments | StudyPlanner</title>
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
      <h3><li class="active"><a href="assignments.php">📝 Assignments</a></li></h3>
      <li><a href="study_sessions.php">⏰ Study Sessions</a></li>
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
          <p>Track your progress and assignments</p>
        </div>
      </div>
    </header>

    <section class="dashboard-section">
      <div class="main-content">
        <div class="assignments-header">
          <div>
            <h2>Your Assignments</h2><br>
            <p>Track and manage all your assignments</p>
          </div>
          <button class="btn-primary" onclick="openAssignModal()">+ New Assignment</button>
        </div>

        <div class="assignment-tabs">
          <button class="<?php echo ($status_filter=='All')?'active':''; ?>" onclick="filterAssign('All')">All</button>
          <button class="<?php echo ($status_filter=='Pending')?'active':''; ?>" onclick="filterAssign('Pending')">Pending</button>
          <button class="<?php echo ($status_filter=='Completed')?'active':''; ?>" onclick="filterAssign('Completed')">Completed</button>
        </div>

        <div class="assignment-list">
          <?php if ($result->num_rows > 0): ?>
          <table class="assign-table">
            <tr>
              <th>Title</th>
              <th>Description</th>
              <th>Due Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
            <?php while($row=$result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                  <div class="action-btns">
                    <button class="edit-btn"
                      onclick="editAssign(<?php echo $row['id']; ?>,'<?php echo addslashes($row['title']); ?>','<?php echo addslashes($row['description']); ?>','<?php echo $row['due_date']; ?>','<?php echo $row['status']; ?>')">Edit</button>
                    <a href="?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Delete this assignment?')">Delete</a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </table>
          <?php else: ?>
            <p>No assignments available</p>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>
    
  <div id="assignModal" class="modal">
    <div class="modal-content">
      <h2 id="modalTitle">Add Assignment</h2>
      <form method="POST">
        <input type="hidden" name="assign_id" id="assign_id">
        <label>Title</label>
        <input type="text" name="title" id="title" required>
        <label>Description</label>
        <textarea name="description" id="description" rows="3"></textarea>
        <label>Due Date</label>
        <input type="date" name="due_date" id="due_date" required>
        <label>Due Time</label>
        <input type="time" name="due_time" id="due_time" required>

        <label>Reminders</label>
        <div>
          <label><input type="checkbox" name="reminders[]" value="24"> 24 hours before</label>
          <label><input type="checkbox" name="reminders[]" value="12"> 12 hours before</label>
        </div>

        <label>Status</label>
        <select name="status" id="status">
          <option value="Pending">Pending</option>
          <option value="Completed">Completed</option>
        </select>
        <div class="modal-buttons">
          <button type="submit" class="yes">Save</button>
          <button type="button" class="cancel" onclick="closeAssignModal()">Cancel</button>
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
