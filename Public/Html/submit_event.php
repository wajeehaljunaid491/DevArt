<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['event_title'];
    $description = $_POST['event_description'];

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["event_image"]["name"]);
    move_uploaded_file($_FILES["event_image"]["tmp_name"], $target_file);

    $query = "INSERT INTO events (title, description, image_path) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $title, $description, $target_file);

    if ($stmt->execute()) {
        header('Location:admin.php');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
