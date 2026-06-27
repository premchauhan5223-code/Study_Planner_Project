<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'InvalidRequest';
    exit();
}

$fullname = isset($_POST['fname']) ? trim($_POST['fname']) : '';
$dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$cpassword = isset($_POST['cpassword']) ? $_POST['cpassword'] : '';

if ($fullname === '' || $dob === '' || $email === '' || $password === '' || $cpassword === '') {
    echo 'MissingFields';
    exit();
}

if ($password !== $cpassword) {
    echo 'PasswordMismatch';
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'InvalidEmail';
    exit();
}

// Check existing user
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo 'UserExists';
    exit();
}
$stmt->close();

$hashed = password_hash($password, PASSWORD_DEFAULT);

// handle file upload (optional)
$profile_img_name = 'default.png'; // default image

$uploadDir = realpath(__DIR__) . '/uploads/'; // absolute path

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // create if not exists
}

if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['profile_img']['tmp_name'];
    $origName = basename($_FILES['profile_img']['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];

    if (in_array($ext, $allowed)) {
        $profile_img_name = time() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $origName);
        $target = $uploadDir . $profile_img_name;

        if (!move_uploaded_file($tmp, $target)) {
            $profile_img_name = 'default.png'; // fallback
        }
    } else {
        echo 'InvalidImageType';
        exit();
    }
}


$insert = $conn->prepare("INSERT INTO users (fullname, dob, email, password, profile_img) VALUES (?, ?, ?, ?, ?)");
if (!$insert) {
    echo 'PrepareError: ' . $conn->error;
    exit();
}
$insert->bind_param('sssss', $fullname, $dob, $email, $hashed, $profile_img_name);
if ($insert->execute()) {
    echo 'Success';
} else {
    echo 'InsertError: ' . $insert->error;
}
$insert->close();
?>
