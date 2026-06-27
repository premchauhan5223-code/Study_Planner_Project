<?php
session_start();
require 'db_connect.php'; // Your DB connection file

if (!isset($_SESSION['user']['id'])) {
    echo "Unauthorized";
    exit();
}

$userId = $_SESSION['user']['id'];

if (isset($_FILES['profile_img'])) {
    $file = $_FILES['profile_img'];
    $fileName = time() . "_" . basename($file['name']);
    $targetDir = "uploads/";
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        // Update DB
        $stmt = $conn->prepare("UPDATE users SET profile_img = ? WHERE id = ?");
        $stmt->bind_param("si", $fileName, $userId);

        if ($stmt->execute()) {
            $_SESSION['user']['profile_img'] = $fileName;
            echo "success";
        } else {
            echo "DBError";
        }
    } else {
        echo "UploadError";
    }
} else {
    echo "NoFile";
}
