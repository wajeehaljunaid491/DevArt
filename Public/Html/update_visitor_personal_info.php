<?php
session_start();
require 'connect.php'; // Include your database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);  // Adjust this to your actual welcome/dashboard page
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        // Change password logic
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql_update_password = "UPDATE visitor SET password=? WHERE id=?";
            $stmt = $conn->prepare($sql_update_password);
            $stmt->bind_param('si', $hashed_password, $user_id);
            if ($stmt->execute()) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                echo "Error updating password: " . $stmt->error;
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
            $sql_update_email = "UPDATE visitor SET email=? WHERE id=?";
            $stmt = $conn->prepare($sql_update_email);
            $stmt->bind_param('si', $new_email, $user_id);
            if ($stmt->execute()) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                echo "Error updating email: " . $stmt->error;
            }
        } else {
            echo "Emails do not match.";
        }
    }
}

$conn->close();
?>
