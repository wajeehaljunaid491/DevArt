
<?php
include 'connect.php'; // Include the database connection script

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $keepLoggedIn = isset($_POST['keep_logged_in']) ? true : false; // Check if "Keep me logged in" checkbox is checked

    // Query to check if the user exists in the artist table
    $sql_artist = "SELECT * FROM artist WHERE email = '$email'";
    $result_artist = $conn->query($sql_artist);

    if ($result_artist->num_rows > 0) {
        // Artist found, verify password
        $row_artist = $result_artist->fetch_assoc();
        if (password_verify($password, $row_artist['password'])) {
            // Login successful, set session variables
            $_SESSION['user_id'] = $row_artist['id'];
            $_SESSION['user_role'] = 'artist';
            $_SESSION['user_name'] = $row_artist['name'];
            $_SESSION['user_photo'] = $row_artist['photo_path'];

            // Set cookie if "Keep me logged in" is checked
            if ($keepLoggedIn) {
                setcookie('user_id', $row_artist['id'], time() + (7 * 24 * 60 * 60), '/');
                setcookie('user_role', 'artist', time() + (7 * 24 * 60 * 60), '/');
                setcookie('user_name', $row_artist['name'], time() + (7 * 24 * 60 * 60), '/');
                setcookie('user_photo', $row_artist['photo_path'], time() + (7 * 24 * 60 * 60), '/');
            }

            // Close connection and redirect back to previous page
            $conn->close();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            echo "Invalid password for artist.";
        }
    } else {
        // Query to check if the user exists in the visitor table
        $sql_visitor = "SELECT * FROM visitor WHERE email = '$email'";
        $result_visitor = $conn->query($sql_visitor);

        if ($result_visitor->num_rows > 0) {
            // Visitor found, verify password
            $row_visitor = $result_visitor->fetch_assoc();
            if (password_verify($password, $row_visitor['password'])) {
                // Login successful, set session variables
                $_SESSION['user_id'] = $row_visitor['id'];
                $_SESSION['user_role'] = 'visitor';
                $_SESSION['user_name'] = $row_visitor['name'];
                $_SESSION['user_photo'] = $row_visitor['photo_path'];

                // Set cookie if "Keep me logged in" is checked
                if ($keepLoggedIn) {
                    setcookie('user_id', $row_visitor['id'], time() + (7 * 24 * 60 * 60), '/');
                    setcookie('user_role', 'visitor', time() + (7 * 24 * 60 * 60), '/');
                    setcookie('user_name', $row_visitor['name'], time() + (7 * 24 * 60 * 60), '/');
                    setcookie('user_photo', $row_visitor['photo_path'], time() + (7 * 24 * 60 * 60), '/');
                }

                // Close connection and redirect back to previous page
                $conn->close();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                echo "Invalid password for visitor.";
            }
        } else {
            // Query to check if the user exists in the admin table
            $sql_admin = "SELECT * FROM admin WHERE email = '$email'";
            $result_admin = $conn->query($sql_admin);

            if ($result_admin->num_rows > 0) {
                // Admin found, verify password
                $row_admin = $result_admin->fetch_assoc();
                if (password_verify($password, $row_admin['password'])) {
                    // Login successful, set session variables
                    $_SESSION['user_id'] = $row_admin['id'];
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['user_name'] = $row_admin['name'];
                    $_SESSION['user_photo'] = $row_admin['photo'];

                    // Set cookie if "Keep me logged in" is checked
                    if ($keepLoggedIn) {
                        setcookie('user_id', $row_admin['id'], time() + (7 * 24 * 60 * 60), '/');
                        setcookie('user_role', 'admin', time() + (7 * 24 * 60 * 60), '/');
                        setcookie('user_name', $row_admin['name'], time() + (7 * 24 * 60 * 60), '/');
                        setcookie('user_photo', $row_admin['photo'], time() + (7 * 24 * 60 * 60), '/');
                    }

                    // Close connection and redirect back to previous page
                    $conn->close();
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                } else {
                    echo "Invalid password for admin.";
                }
            } else {
                echo "User not found.";
            }
        }
    }
}

// Close connection if not already closed
$conn->close();
?>
