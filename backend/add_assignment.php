<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['user'])) exit();

$user_id = $_SESSION['user']['id'];
$title = $_POST['title'];
$desc = $_POST['description'];
$date = $_POST['due_date'];

$conn->query("INSERT INTO assignments (user_id, title, description, due_date)
VALUES ($user_id, '$title', '$desc', '$date')");
echo "success";
?>
