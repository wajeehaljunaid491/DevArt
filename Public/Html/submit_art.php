<?php
session_start(); // Start the session

// Include your database connection file
include_once 'connect.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assuming user_id is set in session after user login
    $user_id = $_SESSION["user_id"];

    // Initialize variables from POST data
    $art_title = $_POST['art_title'];
    $art_type = $_POST['art_type'];
    $art_medium = $_POST['art_medium'];
    $art_description = $_POST['art_description'];
    $created_year = $_POST['created_year'];
    $size = $_POST['size'];

    // File upload handling
    $target_dir = __DIR__ . "/uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["art_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image
    $check = getimagesize($_FILES["art_image"]["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["art_image"]["size"] > 524288000) { // 5MB limit
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    $allowed_extensions = array("jpg", "jpeg", "png", "gif", "bmp", "webp", "tiff", "svg");
    if (!in_array($imageFileType, $allowed_extensions)) {
        echo "Sorry, only image files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // Attempt to move uploaded file
        if (move_uploaded_file($_FILES["art_image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . basename($_FILES["art_image"]["name"]);

            // Insert data into the database
            $sql = "INSERT INTO art (painting_name, artist_id, art_type, art_description, image_path, art_medium, created_year, size) 
                    VALUES ('$art_title', '$user_id', '$art_type', '$art_description', '$image_path', '$art_medium', '$created_year', '$size')";

            if ($conn->query($sql) === TRUE) {
                // Redirect back to the previous page
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

$conn->close(); // Close the database connection
?>
