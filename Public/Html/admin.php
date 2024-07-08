<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: home.php');    exit();
}

include 'connect.php'; // Assuming connect.php includes your database connection

// Function to generate the last three months
function getLastThreeMonths() {
    $months = [];
    for ($i = 0; $i < 3; $i++) {
        $months[] = date('Y-m', strtotime("-$i month"));
    }
    return array_reverse($months);
}

// Fetch data from the database
$visitors_query = "SELECT COUNT(*) as count, DATE_FORMAT(created_at, '%Y-%m') as month FROM visitor GROUP BY month";
$visitors_result = mysqli_query($conn, $visitors_query);
$visitors_data = [];
while ($row = mysqli_fetch_assoc($visitors_result)) {
    $visitors_data[$row['month']] = $row['count'];
}

$artworks_query = "SELECT COUNT(*) as count, DATE_FORMAT(created_at, '%Y-%m') as month FROM art GROUP BY month";
$artworks_result = mysqli_query($conn, $artworks_query);
$artworks_data = [];
while ($row = mysqli_fetch_assoc($artworks_result)) {
    $artworks_data[$row['month']] = $row['count'];
}

$artists_query = "SELECT COUNT(*) as count, DATE_FORMAT(created_at, '%Y-%m') as month FROM artist GROUP BY month";
$artists_result = mysqli_query($conn, $artists_query);
$artists_data = [];
while ($row = mysqli_fetch_assoc($artists_result)) {
    $artists_data[$row['month']] = $row['count'];
}

$months = getLastThreeMonths();
$combined_labels = array_unique(array_merge(array_keys($visitors_data), array_keys($artworks_data), array_keys($artists_data), $months));
sort($combined_labels);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Style/dashboard.css">
    <link rel="icon" type="image/x-icon" href="../Assets/icon/title-logo.svg">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>ADMIN | Dashboard</title>
    
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
            <button class="tablinks active" onclick="openTab(event, 'submit-event')">Submit Event</button>

            <button class="tablinks" onclick="openTab(event, 'submit-news')">Submit news</button>
            <button class="tablinks" onclick="openTab(event, 'Activity')">Activity</button>
        </div>
    </div>
    <!-- Tab content -->
    
    <div id="submit-event" class="tabcontent">
        <form action="submit_event.php" method="post" enctype="multipart/form-data">
            <div class="header">
                <div class="title-tab">Add Events</div>
                <label for="event_title">TITLE OF EVENT</label><br>
                <input type="text" name="event_title" id="event_title" placeholder="Title of event" required>
            </div>
            <label for="event_description">DESCRIPTION</label>
            <textarea name="event_description" id="event_description" cols="30" rows="10"></textarea>
            <label for="event_image" class="file-container">
                <span>EVENT IMAGE</span>
                <div>
                    <input type="file" name="event_image" id="event_image" accept="image/*" required>
                </div>
            </label>
            <input type="submit" value="Submit Event">
        </form>
    </div>

    

    <div id="submit-news" class="tabcontent">
    <form action="submit_news.php" method="post" enctype="multipart/form-data">
        <div class="header">
            <div class="title-tab">News</div>
            <div class="sub-title">Add news here.</div>
        </div>
        <label for="news_title">TITLE OF NEWS</label><br>
        <input type="text" name="news_title" id="news_title" placeholder="Title of news" required>
        <label for="news_type">TYPE OF NEWS</label>
        <select id="news_type" name="news_type" required>
            <option value="" disabled selected class="selected">Select</option>
            <option value="Company Announcement">Company Announcement</option>
            <option value="Best Practice">Best Practice</option>
            <option value="Product News">Product News</option>
            <option value="Artists News">Artists News</option>
        </select>
        <label for="news_description">DESCRIPTION</label>
        <textarea name="news_description" id="news_description" cols="30" rows="10" required></textarea>
        <label for="news_image" class="file-container">
            <span>NEWS IMAGE</span>
            <div>
                <input type="file" name="news_image" id="news_image" accept="image/*" required>
            </div>
        </label>
        <input type="submit" value="Submit News">
    </form>
</div>

    

    <div id="Activity" class="tabcontent">
        <div class="header">
            <div class="title-tab">Recent Activity</div>
        </div>
        <canvas id="activityChart" width="270" height="100"></canvas>
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
            document.getElementById("overlay").style.display = "block";
        }

        function closeNav() {
            document.getElementById("mySidenav").style.width = "0";
            document.getElementById("overlay").style.display = "none";
        }

        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
            document.body.classList.remove("submit-event",  "submit-news","activity");
            document.body.classList.add(tabName.replace(/\s+/g, '-').toLowerCase());
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
                        foreach ($combined_labels as $label) {
                            echo "'$label', ";
                        }
                        ?>
                    ],
                    datasets: [{
                        label: 'Visitors',
                        data: [
                            <?php
                            foreach ($combined_labels as $label) {
                                echo isset($visitors_data[$label]) ? $visitors_data[$label] : 0;
                                echo ', ';
                            }
                            ?>
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Artworks',
                        data: [
                            <?php
                            foreach ($combined_labels as $label) {
                                echo isset($artworks_data[$label]) ? $artworks_data[$label] : 0;
                                echo ', ';
                            }
                            ?>
                        ],
                        backgroundColor: 'rgba(255, 32, 78, 0.3)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }, {
                        label: 'Artists',
                        data: [
                            <?php
                            foreach ($combined_labels as $label) {
                                echo isset($artists_data[$label]) ? $artists_data[$label] : 0;
                                echo ', ';
                            }
                            ?>
                        ],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
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


        document.addEventListener("DOMContentLoaded", function () {
            document.querySelector(".tablinks.active").click();
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
