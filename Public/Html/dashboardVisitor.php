<?php
session_start();
require 'connect.php'; // Include your database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: home.php'); // Adjust this to your actual welcome/dashboard page
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_role'];

// Fetch current user data
$sql = "SELECT email FROM visitor WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Style/dashboardVisitor.css">
    <link rel="icon" type="image/x-icon" href="../Assets/icon/title-logo.svg">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Visitor Dashboard</title>
    <style>
        .profile-photo {
            width: 120px;
            height: 120px;
        }

        .profile-photo img {
            width: 120px;
            height: 120px;
            background-size: cover;
            border-radius: 50%;
        }

        .sign button {
            background: none;
            padding: 8px;
            color: var(--text-primary);
            font-size: 18px;
            border: none;
            cursor: pointer;
            font-family: var(--font-regular);
            opacity: var(--opacity);
        }

        .open-menu button {
            background: none;
            padding: 8px;
            color: var(--text-primary);
            font-size: 18px;
            border: none;
            cursor: pointer;
            font-family: var(--font-regular);
            opacity: var(--opacity);
        }
    </style>
</head>

<body>
    <!-- NAVBAR -->
    <nav id="navbar">
        <a class="logo" href="home.php">
            <img src="../Assets/icon/arté.png" alt="">
        </a>
        <div class="menu-container">
            <div class="menu-inside">
                <div class="sign">
                <?php


if (isset($_SESSION['user_role'])) {
    // If user is logged in, show profile button and logout button
    echo '<button onclick="logout()">Logout</button>';

    // Output user role as a JavaScript variable
    echo '<script>var userRole = "' . htmlspecialchars($_SESSION['user_role']) . '";</script>';

    // Check if user_photo is set before displaying
    if (isset($_SESSION['user_photo'])) {
        echo '<button  onclick="openProfile()"><img  style="width: 40px; height: 40px; border-radius: 100%; filter: grayscale(0); transition: all 0.9s ease;" src="' . htmlspecialchars($_SESSION['user_photo']) . '" onmouseover="this.style.filter=\'grayscale(100%)\';" onmouseout="this.style.filter=\'grayscale(0)\';"></button>';
    } else {
        echo '<button onclick="openProfile()">Profile</button>'; // Show profile button if photo is not available
    }
} else {
    // If user is not logged in, show login button
    echo '<button onclick="openSign()">Log in</button>';
}
?>
                </div>
                <div class="open-menu">
                    <button onclick="openNav()">
                        <img src="../Assets/icon/icon-menu.png" alt="">
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- SIDENAV -->
    <div id="overlay" class="overlay"></div>
    <div id="mySidenav" class="sidenav">
        <button class="closebtn" onclick="closeNav()">&times;</button>
        <div class="menu-overlay">
            <div class="artType">
            <ul>
                    <h6>Category</h6>
                    <li><a href="artwork.php?art_type=Abstract">Abstract</a></li>
                    <li><a href="artwork.php?art_type=Animal">Animal</a></li>
                    <li><a href="artwork.php?art_type=Botanical">Botanical</a></li>
                    <li><a href="artwork.php?art_type=Drawings">Drawings</a></li>
                    <li><a href="artwork.php?art_type=Illustration">Illustration</a></li>
                    <li><a href="artwork.php?art_type=Figurative">Figurative</a></li>
                    <li><a href="artwork.php?art_type=Landscape">Landscape</a></li>
                    <li><a href="artwork.php?art_type=Mythology">Mythology</a></li>
                    <li><a href="artwork.php?art_type=Religion">Religion</a></li>
                    <li><a href="artwork.php?art_type=Still Life">Still Life</a></li>
                </ul>
            </div>
            <div class="nav-overlay">
                <ul>
                    <h6>Menu</h6>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="artists.php">Artists</a></li>
                    <li><a href="">News</a></li>
                    <li><a href="">Terms</a></li>
                    <li><a href="">About Us</a></li>
                </ul>
            </div>
        </div>
        <div class="info">
            <div class="text">Get in touch</div>
            <button>info@DevArt.com</button>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="tab">
        <div class="title-page">
            Profile & Directory
        </div>
        <div class="button-group">
            <button class="tablinks active" onclick="openTab(event, 'personal-info')">Personal Info</button>
            <button class="tablinks" onclick="openTab(event, 'collection')">Collection</button>
        </div>
    </div>

    <!-- Tab content -->
    <div id="personal-info" class="tabcontent" style="display: block;">
        <form action="update_visitor_personal_info.php" method="post">
            <div class="header">
                <div class="title-tab">Change Password</div>
            </div>
            <div class="row">
                <div class="item">
                    <label for="new_password">NEW PASSWORD</label>
                    <input type="password" id="new_password" name="new_password" placeholder="New password" required>
                </div>
                <div class="item">
                    <label for="confirm_password">CONFIRM PASSWORD</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                </div>
                <div class="item">
                    <input type="submit" value="Update Password" class="">
                </div>
            </div>
        </form>
        <div class="line"></div>
        <form action="update_visitor_personal_info.php" method="post">
            <div class="header">
                <div class="title-tab">Change Email</div>
            </div>
            <div class="row">
                <div class="item">
                    <label for="new_email">NEW EMAIL</label>
                    <input type="email" id="new_email" name="new_email" placeholder="New email" required>
                </div>
                <div class="item">
                    <label for="confirm_email">CONFIRM EMAIL</label>
                    <input type="email" id="confirm_email" name="confirm_email" placeholder="Confirm new email" required>
                </div>
                <div class="item">
                    <input type="submit" value="Update Email" class="">
                </div>
            </div>
        </form>
    </div>

    <div id="collection" class="tabcontent">
        <div class="art-wrap">
            
        <?php
            // Fetch liked artworks for the visitor
            $liked_arts_sql = "SELECT art.* FROM likes 
                               INNER JOIN art ON likes.art_id = art.id 
                               WHERE likes.visitor_id = ?";
            $stmt_likes = $conn->prepare($liked_arts_sql);
            $stmt_likes->bind_param('i', $user_id);
            $stmt_likes->execute();
            $result_likes = $stmt_likes->get_result();

            if ($result_likes->num_rows > 0) {
                while ($row = $result_likes->fetch_assoc()) {
                    echo '<div class="box">
                        <a href="openArt.php?id=' . $row["id"] . '">
                            <img src="' . htmlspecialchars($row["image_path"]) . '" alt="' . htmlspecialchars($row["painting_name"]) . '">
                            <div class="text">
                                <div class="text-inner">See Artwork</div>
                            </div>
                        </a>
                    </div>';
                }
            } else {
                echo "No liked artworks found";
            }
            ?>
        </div>
    </div>

    <!-- FOOTER SECTION -->
    <section class="footer">
        <div class="footer-container">
            <div class="menu-row">
                <a href="artists.php">Artists</a>
                <a href="">News & Insights</a>
                <a href="">About</a>
                <a href="">Terms</a>
            </div>
            <div class="contact">
                <a href="">Get in touch</a>
            </div>
            <div class="social">
                <a href="">Dribbble</a>
                <a href="">Artsy</a>
                <a href="">Awwards</a>
            </div>
        </div>
    </section>

    <section class="watermark">
        <div class="watermark-container">
            <div>
                <div class="line2"></div>
            </div>
            <div class="males">
                <p>Created by informatics students as a final project</p>
                <p>Specially created by the DevArt team (IP TEAM)</p>
                <p>© 2024 Arte Limited. All rights reserved.</p>
            </div>
        </div>
    </section>
    <script>

function openProfile() {
    if (typeof userRole !== 'undefined') {
        if (userRole === 'artist') {
            window.location.href = 'dashboard.php';
        } else if (userRole === 'visitor') {
            window.location.href = 'dashboardVisitor.php';
        }else if (userRole === 'admin') {
            window.location.href = 'admin.php';
        }else {
            console.error('Unknown user role:', userRole);
        }
    } else {
        console.error('User role is not defined.');
    }
}


    function logout() {
        window.location.href = 'logout.php';
    }
        function openNav() {
            document.getElementById("mySidenav").style.width = "50%";
            document.getElementById("overlay").style.display = "block"; // tampilkan overlay
        }

        function closeNav() {
            document.getElementById("mySidenav").style.width = "0";
            document.getElementById("overlay").style.display = "none"; // sembunyikan overlay
        }

        function openTab(evt, tabName) {
            // Declare all variables
            var i, tabcontent, tablinks;

            // Get all elements with class="tabcontent" and hide them
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            // Get all elements with class="tablinks" and remove the class "active"
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            // Show the current tab, and add an "active" class to the button that opened the tab
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";

            // Remove any previous tab-specific classes from body
            document.body.classList.remove("personal-info", "collection");

            // Add tab-specific class to body
            document.body.classList.add(tabName.replace(/\s+/g, '-').toLowerCase());

            // Initialize chart if Activity tab is opened
            if (tabName === 'Activity') {
                initChart();
            }
        }


    </script>
</body>

</html>
