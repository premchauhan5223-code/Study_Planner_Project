<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['user'])) exit();

$user_id = $_SESSION['user']['id'];

$name = $_POST['class_name'];
$instructor = $_POST['instructor'];
$date = $_POST['date'];
$time = $_POST['time'];
$day = $_POST['day'];

$stmt = $conn->prepare("INSERT INTO classes (user_id, class_name, instructor, class_date, class_time, class_day)
                        VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $user_id, $name, $instructor, $date, $time, $day);
$stmt->execute();

echo "success";
?>
