<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['news_title'];
    $type = $_POST['news_type'];
    $description = $_POST['news_description'];

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["news_image"]["name"]);
    if (move_uploaded_file($_FILES["news_image"]["tmp_name"], $target_file)) {
        $query = "INSERT INTO news (title, type, description, image_path) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $title, $type, $description, $target_file);

        if ($stmt->execute()) {
            header('Location: admin.php');
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error uploading file.";
    }
    
    $conn->close();
}
?>
