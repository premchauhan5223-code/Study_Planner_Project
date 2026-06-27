<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'InvalidRequest';
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($email === '' || $password === '') {
    echo 'MissingFields';
    exit();
}

$stmt = $conn->prepare("SELECT id, fullname, password, profile_img FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo 'NoUser';
    exit();
}

$user = $result->fetch_assoc();
if (password_verify($password, $user['password'])) {
    // set session as array (better)
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['fullname'],
        'email' => $email,
        'profile_img' => $user['profile_img'] ?? 'default.png'
    ];
    echo 'LoginSuccess';
} else {
    echo 'InvalidPassword';
}
?>
