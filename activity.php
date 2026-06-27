<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.html');
    exit();
}
include 'backend/db_connect.php';
$user_id = $_SESSION['user']['id'];

$week_goal = 20; // target hours
$studied = $conn->query("SELECT IFNULL(SUM(duration)/60,0) AS total FROM study_sessions WHERE user_id=$user_id AND WEEK(date)=WEEK(NOW())")->fetch_assoc()['total'];
$assign_total = $conn->query("SELECT COUNT(*) AS total FROM assignments WHERE user_id=$user_id")->fetch_assoc()['total'];
$assign_done = $conn->query("SELECT COUNT(*) AS done FROM assignments WHERE user_id=$user_id AND status='Completed'")->fetch_assoc()['done'];
$recent_sessions = $conn->query("SELECT * FROM study_sessions WHERE user_id=$user_id ORDER BY date DESC LIMIT 5");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Activity | StudyPlanner</title>
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
      <li><a href="dashboard.php">🏠 Dashboard</a></li>
      <h3><li class="active"><a href="activity.php">📊 Activity</a></li></h3>
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
          <h1> <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 👋</h1>
          <p>Track your progress and achievements</p>
        </div>
      </div>
    </header>

    <section class="dashboard-section">
    <h2>Track Your Activity</h2>

     <main class="main-content activity-page">

      <div class="progress-card">
      <h3>Weekly Study Goal</h3>
      <p><?php echo round($studied); ?> of <?php echo $week_goal; ?> hours</p>
      <div class="progress-bar"><span style="width: <?php echo min(100, ($studied/$week_goal)*100); ?>%"></span></div>
    </div>

    <div class="progress-card">
      <h3>Assignment Completion</h3>
      <p><?php echo $assign_done; ?> of <?php echo $assign_total; ?> completed</p>
      <div class="progress-bar"><span style="width: <?php echo $assign_total ? ($assign_done/$assign_total)*100 : 0; ?>%"></span></div>
    </div>

    <div class="activity-box">
      <h3>Recent Study Sessions</h3>
      <?php if ($recent_sessions->num_rows > 0): ?>
        <ul>
          <?php while($row = $recent_sessions->fetch_assoc()): ?>
            <li>
              <div class="session-info">
                <span class="session-subject"><?php echo htmlspecialchars($row['subject']); ?></span>
                <span class="session-date"><?php echo date('F j, Y', strtotime($row['date'])); ?></span>
              </div>
              <span class="session-duration"><?php echo $row['duration']; ?> min</span>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p>No study sessions recorded yet</p>
      <?php endif; ?>
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

</body>
</html>
