<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
     exit;
}

include 'connect.php'; // Assuming connect.php includes your database connection

$user_id = $_SESSION["user_id"];
$role = $_SESSION["user_role"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        // Change password logic
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql_update_password = "UPDATE artist SET password='$hashed_password' WHERE id='$user_id'";
            if ($conn->query($sql_update_password) === TRUE) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                echo "Error updating password: " . $conn->error;
            }
        } else {
            echo "Passwords do not match.";
        }
    }
    
    if (isset($_POST['new_email']) && isset($_POST['confirm_email'])) {
        // Change email logic
        $new_email = $_POST['new_email'];
        $confirm_email = $_POST['confirm_email'];
        
        if ($new_email === $confirm_email) {
            $sql_update_email = "UPDATE artist SET email='$new_email' WHERE id='$user_id'";
            if ($conn->query($sql_update_email) === TRUE) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                echo "Error updating email: " . $conn->error;
            }
        } else {
            echo "Emails do not match.";
        }
    }
}

$conn->close();
?>
