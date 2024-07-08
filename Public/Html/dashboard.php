
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: home.php');  // Adjust this to your actual welcome/dashboard page
    exit();
}

include 'connect.php'; // Assuming connect.php includes your database connection

$user_id = $_SESSION["user_id"];
$role = $_SESSION["user_role"];

if ($role == "artist") {
    // Fetch followers count for each month
    $followers_data = array();
    $sql_followers = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count FROM follow WHERE followee_id='$user_id' GROUP BY DATE_FORMAT(created_at, '%Y-%m')";
    $result_followers = $conn->query($sql_followers);
    if ($result_followers) {
        while ($row = $result_followers->fetch_assoc()) {
            $followers_data[$row['month']] = $row['count'];
        }
    } else {
        echo "Error fetching followers data: " . $conn->error;
        exit();
    }

    // Fetch likes count for each month 
    $likes_data = array();
    $sql_likes = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count FROM likes WHERE art_id IN (SELECT id FROM art WHERE artist_id='$user_id') GROUP BY DATE_FORMAT(created_at, '%Y-%m')";
    $result_likes = $conn->query($sql_likes);
    if ($result_likes) {
        while ($row = $result_likes->fetch_assoc()) {
            $likes_data[$row['month']] = $row['count'];
        }
    } else {
        echo "Error fetching likes data: " . $conn->error;
        exit();
    }

    // Fetch artist profile data
    $sql_artist = "SELECT * FROM artist WHERE id = '$user_id'";
    $result_artist = $conn->query($sql_artist);
    if ($result_artist->num_rows > 0) {
        $row = $result_artist->fetch_assoc();

        // Initialize variables with current data
        $current_display_name = $row['name'];
        $current_about_you = $row['about'];
        $current_biography = $row['bio'];
        $current_gender = $row['gender'];
        $current_facebook_url = $row['facebook_url'];
        $current_linkedin_url = $row['linkedin_url'];
        $current_instagram_url = $row['instagram_url'];
        $current_x_url = $row['x_url'];
        $current_profile_photo = $row['photo_path'];
    } else {
        echo "Artist profile not found.";
        exit();
    }

    // Close the database connection
    $conn->close();
} else {
    echo "Unauthorized access.";
    exit();
}

// Encode the data to JSON format to use it in JavaScript
$followers_json = json_encode($followers_data);
$likes_json = json_encode($likes_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Style/dashboard.css">
    <link rel="icon" type="image/x-icon" href="../Assets/icon/title-logo.svg">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>ARTIST | Dashboard</title>
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
        #activityChart {
    max-width: 270;
    max-height: 100;
    width: 100%;
    height: auto;
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
            <button class="tablinks active" onclick="openTab(event, 'Public Profile')">Public Profile</button>
            <button class="tablinks" onclick="openTab(event, 'Personal Info')">Personal Info</button>
            <button class="tablinks" onclick="openTab(event, 'Submit Art')">Submit Art</button>
            <button class="tablinks" onclick="openTab(event, 'Activity')">Activity</button>
        </div>
    </div>

    <!-- Tab content -->
    <div id="Public Profile" class="tabcontent default">
    <form action="update_artist_profile.php" method="post" enctype="multipart/form-data">
        <div class="header">
            <div class="title-tab">Your profile</div>
            <div class="sub-title">Add more information about you here.</div>
        </div>
        <label for="display_name">DISPLAY NAME</label>
        <input type="text" id="display_name" name="display_name" placeholder="Full name" value="<?php echo htmlspecialchars($current_display_name); ?>" required>
        
        <label for="about_you">ABOUT YOU</label>
        <textarea id="about_you" name="about_you" cols="20" rows="10" required><?php echo htmlspecialchars($current_about_you); ?></textarea>
        
       <label for="biography">YOUR BIOGRAPHY</label>
<textarea id="biography" name="biography" cols="20" rows="30" required><?php echo $current_biography; ?></textarea>
        
        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
            <option value="" disabled>Select</option>
            <option value="Male" <?php if ($current_gender == 'Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if ($current_gender == 'Female') echo 'selected'; ?>>Female</option>
        </select>
        
        <label for="profile_photo" class="file-container">
            <span>PROFILE PHOTO</span>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
           
        </label>
        
        <div class="header2">
            <div class="title-tab">Social Media Links</div>
            <div class="sub-title">Improve visibility and help people contact you by adding your social media links here.</div>
        </div>
        
        <div class="row">
            <div class="item">
                <label for="Facebook">Facebook</label>
                <input type="url" id="Facebook" name="Facebook" placeholder="Paste url here" value="<?php echo htmlspecialchars($current_facebook_url); ?>">
            </div>
            <div class="item">
                <label for="linkedin">LINKEDIN</label>
                <input type="url" id="linkedin" name="linkedin" placeholder="Paste url here" value="<?php echo htmlspecialchars($current_linkedin_url); ?>">
            </div>
        </div>
        
        <div class="row">
            <div class="item">
                <label for="instagram">Instagram</label>
                <input type="url" id="instagram" name="instagram" placeholder="Paste url here" value="<?php echo htmlspecialchars($current_instagram_url); ?>">
            </div>
            <div class="item">
                <label for="X">X</label>
                <input type="url" id="X" name="X" placeholder="Paste url here" value="<?php echo htmlspecialchars($current_x_url); ?>">
            </div>
        </div>
        
        <input type="submit" name="submit" value="Save Changes">
    </form>
</div>
<!-- HTML part -->

<div id="Personal Info" class="tabcontent">
    <form action="update_artist_personal_info.php" method="post">
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
    <form action="update_artist_personal_info.php" method="post">
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


    <div id="Submit Art" class="tabcontent">
        <form action="submit_art.php" method="POST" enctype="multipart/form-data">
            <div class="header">
                <div class="title-tab">Submit Artwork</div>
                <div class="sub-title">Submit your artwork here.</div>
            </div>
            <label for="art_title">TITLE OF ART</label>
            <input type="text" name="art_title" id="art_title" placeholder="Title of artwork" required>
            
            <label for="art_type">TYPE OF ART</label>
            <select id="art_type" name="art_type" required>
                <option value="" disabled selected>Select</option>
                <option value="Abstract">Abstract</option>
                <option value="Animal">Animal</option>
                <option value="Botanical">Botanical</option>
                <option value="Drawings">Drawings</option>
                <option value="Illustration">Illustration</option>
                <option value="Figurative">Figurative</option>
                <option value="Landscape">Landscape</option>
                <option value="Mythology">Mythology</option>
                <option value="Religion">Religion</option>
                <option value="Still Life">Still Life</option>
            </select>
            
            <label for="art_medium">ART MEDIUM</label>
            <input type="text" name="art_medium" id="art_medium" placeholder="ART Medium" >
            
            <label for="art_description">DESCRIPTION</label>
            <textarea name="art_description" id="art_description" cols="30" rows="10" ></textarea>
            
            <label for="created_year">CREATED YEAR</label>
            <input type="number" name="created_year" id="created_year" placeholder="Year of creation" >
            
            <label for="size">SIZE (in cm)</label>
            <input type="text" name="size" id="size" placeholder="Size (e.g., 30x40)" >
            
            <label for="images" class="file-container">
                <span>ARTWORK IMAGE</span>
                <div>
                    <input type="file" name="art_image" id="images" accept="image/*" required>
                </div>
            </label>
            
            <input type="submit" value="Submit Artwork">
        </form>
    </div>

    <div id="Activity" class="tabcontent">
        <form action="">
            <div class="header">
                <div class="title-tab">Recent Activity</div>
            </div>
            <canvas id="activityChart" width="270" height="100"></canvas>
            <!-- Content for Recent Activity -->
        </form>
    </div>

    <!-- FOOTER SECTION -->
    <section class="footer">
        <div class="footer-container">
            <div class="menu-row">
                <a href="">Artists</a>
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
            document.body.classList.remove("public-profile", "personal-info", "submit-art", "activity");

            // Add tab-specific class to body
            document.body.classList.add(tabName.replace(/\s+/g, '-').toLowerCase());

            // Initialize chart if Activity tab is opened
            if (tabName === 'Activity') {
                initChart();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('activityChart').getContext('2d');
        const activityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    <?php
                    $labels = array_keys($followers_data + $likes_data); // Combine keys from both arrays
                    foreach ($labels as $label) {
                        echo "'$label', ";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Followers',
                    data: [
                        <?php
                        foreach ($labels as $label) {
                            echo isset($followers_data[$label]) ? $followers_data[$label] : 0;
                            echo ', ';
                        }
                        ?>
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Likes',
                    data: [
                        <?php
                        foreach ($labels as $label) {
                            echo isset($likes_data[$label]) ? $likes_data[$label] : 0;
                            echo ', ';
                        }
                        ?>
                    ],
                    backgroundColor: 'rgba(255, 32, 78, 0.3)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                   
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });

        // Inisialisasi tab default saat halaman pertama kali dimuat
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelector(".tablinks.active").click();
        });
    </script>
</body>

</html>
