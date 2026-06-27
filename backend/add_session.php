<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['user'])) exit();

$user_id = $_SESSION['user']['id'];
$duration = $_POST['duration'];
$date = date('Y-m-d');

$conn->query("INSERT INTO study_sessions (user_id, duration, session_date)
VALUES ($user_id, $duration, '$date')");
echo "success";
?>
