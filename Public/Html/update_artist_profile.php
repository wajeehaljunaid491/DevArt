<?php
session_start(); // Start the session

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $artist_id = $_SESSION['user_id']; // Corrected from $_SESSION['user_id']

    // Include your database connection file
    include 'connect.php';

    // Initialize variables from POST data
    $display_name = $conn->real_escape_string($_POST['display_name']);
    $about_you = $conn->real_escape_string($_POST['about_you']);
    $biography = $conn->real_escape_string($_POST['biography']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $facebook_url = $conn->real_escape_string($_POST['facebook_url']);
    $linkedin_url = $conn->real_escape_string($_POST['linkedin_url']);
    $instagram_url = $conn->real_escape_string($_POST['instagram_url']);
    $x_url = $conn->real_escape_string($_POST['x_url']);

    // File upload handling for profile photo
    $target_dir = __DIR__ . "/uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["profile_photo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image
    $check = getimagesize($_FILES["profile_photo"]["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["profile_photo"]["size"] > 5242880) { // 5MB limit
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    $allowed_extensions = array("jpg", "jpeg", "png", "gif", "bmp", "webp");
    if (!in_array($imageFileType, $allowed_extensions)) {
        echo "Sorry, only JPG, JPEG, PNG, GIF, BMP, WEBP files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // Attempt to move uploaded file
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            $profile_photo_path = "uploads/" . basename($_FILES["profile_photo"]["name"]);

            // Update data in the database
            $sql = "UPDATE artist SET 
                    name = '$display_name', 
                    about = '$about_you', 
                    bio = '$biography', 
                    gender = '$gender', 
                    photo_path = '$profile_photo_path', 
                    facebook_url = '$facebook_url', 
                    linkedin_url = '$linkedin_url', 
                    instagram_url = '$instagram_url', 
                    x_url = '$x_url' 
                    WHERE id = '$artist_id'";

            if ($conn->query($sql) === TRUE) {
                // Redirect back to the dashboard or artist profile page
                header('Location: dashboard.php');
                exit;
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    $conn->close(); // Close the database connection
} else {
    echo "Invalid request.";
}
?>
