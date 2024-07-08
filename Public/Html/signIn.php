


<?php
include 'connect.php'; // Include the database connection script
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $conn->real_escape_string($_POST['role']);
    $keepLoggedIn = isset($_POST['keep_logged_in']) ? true : false; // Check if "Keep me logged in" checkbox is checked

    if ($role == "artist") {
        $sql = "INSERT INTO artist (name, email, password) VALUES ('$name', '$email', '$password')";
    } elseif ($role == "visitor") {
        $sql = "INSERT INTO visitor (name, email, password) VALUES ('$name', '$email', '$password')";
    }

    if ($conn->query($sql) === TRUE) {
        // Get the last inserted ID
        $user_id = $conn->insert_id;

        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_name'] = $name;

        // Set cookies if "Keep me logged in" is checked
        if ($keepLoggedIn) {
            setcookie('user_id', $user_id, time() + (7 * 24 * 60 * 60), '/');
            setcookie('user_role', $role, time() + (7 * 24 * 60 * 60), '/');
            setcookie('user_name', $name, time() + (7 * 24 * 60 * 60), '/');
        }

        // Redirect to a welcome or dashboard page
        $conn->close();
        header('Location: ' . $_SERVER['HTTP_REFERER']);  // Adjust this to your actual welcome/dashboard page
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection if not already closed
$conn->close();
?>

