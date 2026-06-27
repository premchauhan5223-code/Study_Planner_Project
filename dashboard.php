<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.html');
    exit();
}
include 'backend/db_connect.php';
$user_id = $_SESSION['user']['id'];

$classes = $conn->query("SELECT COUNT(*) AS total FROM classes WHERE user_id=$user_id")->fetch_assoc()['total'];
$assignments = $conn->query("SELECT COUNT(*) AS total FROM assignments WHERE user_id=$user_id")->fetch_assoc()['total'];
$completed = $conn->query("SELECT COUNT(*) AS completed FROM assignments WHERE user_id=$user_id AND status='Completed'")->fetch_assoc()['completed'];
$sessions = $conn->query("SELECT IFNULL(SUM(duration),0) AS total FROM study_sessions WHERE user_id=$user_id")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | StudyPlanner</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style/style.css" />    
  <script defer src="script.js"></script>
</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar">
    <h2 class="sidebar-logo"><img src="image/logo22.png" alt="StudyPlanner logo" class="logo-img"> StudyPlanner</h2>
    <ul class="menu">
<h3><li class="active"><a href="dashboard.php"><span class="home-icon">🏠</span> Dashboard</a></li></h3>
    <li><a href="activity.php">📊 Activity</a></li>
    <li><a href="class_schedule.php">📅 Class Schedule</a></li>
    <li><a href="assignments.php">📝 Assignments</a></li>
    <li><a href="study_sessions.php">⏰ Study Sessions</a></li>
    <h3><li><a href="#" id="logoutBtn"> Logout </a></li></h3>
</ul>
  </aside>

  <!-- Main Content -->
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
          <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 👋</h1>
          <p>Here’s your academic overview</p>
          <button id="editProfileBtn" class="edit-btn1">Edit Profile Image</button>
        </div>
      </div>
    </header>

<section class="dashboard-section">
<h2>Dashboard Overview</h2>
<!-- Dashboard Stats -->
<div class="stats-container">
    <div class="stat-box">
        <h4>Total Classes</h4>
        <h2><?php echo $classes; ?></h2>
    </div>
    <div class="stat-box">
        <h4>Assignments</h4>
        <h2><?php echo $assignments; ?></h2>
    </div>
    <div class="stat-box">
        <h4>Study Hours</h4>
        <h2><?php echo round($sessions / 60, 1); ?> hrs</h2>
    </div>
    <div class="stat-box">
        <h4>Completion Rate</h4>
        <h2><?php echo $completed; ?> %</h2>
    </div>
</div>

<!-- Upcoming & Recent Sections -->
<div class="extra-sections">
    <div class="extra-card">
        <h2>Upcoming Assignments</h2><br>
        <p>Your next deadlines</p>
        <?php
          // Fetch upcoming assignments due today or later
          $today = date('Y-m-d');
          $stmt = $conn->prepare("SELECT id, title, due_date, status FROM assignments WHERE user_id = ? AND due_date >= ? AND status = 'Pending' ORDER BY due_date ASC LIMIT 5");
          $stmt->bind_param('is', $user_id, $today);
          $stmt->execute();
          $res = $stmt->get_result();
        ?>
        <?php if ($res && $res->num_rows > 0): ?>
          <ul class="upcoming-list">
            <?php while ($row = $res->fetch_assoc()): ?>
              <li class="upcoming-item">
                <div class="upcoming-left">
                  <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                  <div class="upcoming-meta">Due: <?php echo htmlspecialchars(date('M j, Y', strtotime($row['due_date']))); ?></div>
                </div>
                <div class="upcoming-status"><?php echo htmlspecialchars($row['status']); ?></div>
              </li>
            <?php endwhile; ?>
          </ul>
        <?php else: ?>
          <p class="empty-text">No pending upcoming assignments. You're all caught up!</p>
        <?php endif; ?>
    </div>
</div>

    </section>
  </main>

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

  <!-- Edit Profile Image Modal -->
  <div id="editProfileModal" class="modal">
    <div class="modal-content small">
      <h2>Change Profile Picture</h2>
      <form id="profileForm" enctype="multipart/form-data">
        <input type="file" name="profile_img" accept="image/*" required>
        <div class="modal-buttons">
          <button type="submit" class="yes">Upload</button>
          <button type="button" id="closeEditModal" class="cancel">Cancel</button>
        </div>
      </form>
    </div>
  </div>

</body>
</html>
