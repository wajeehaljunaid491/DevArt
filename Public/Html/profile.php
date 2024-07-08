<?php
include 'connect.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

if (isset($_GET['artist_id'])) {
    $artist_id = intval($_GET['artist_id']);

    // Prepare and execute the artist query
    $artist_sql = "SELECT * FROM artist WHERE id = ?";
    $stmt = $conn->prepare($artist_sql);
    $stmt->bind_param("i", $artist_id);
    $stmt->execute();
    $artist_result = $stmt->get_result();

    if ($artist_result->num_rows > 0) {
        $artist = $artist_result->fetch_assoc();
    } else {
        echo "Artist not found";
        exit();
    }

    // Prepare and execute the art query
    $art_sql = "SELECT * FROM art WHERE artist_id = ?";
    $stmt = $conn->prepare($art_sql);
    $stmt->bind_param("i", $artist_id);
    $stmt->execute();
    $art_result = $stmt->get_result();

    // Check if the user is following the artist
    $followed = false;
    if ($user_id > 0) {
        if ($user_role == 'visitor') {
            $follow_sql = "SELECT id FROM follow WHERE visitor_id = ? AND followee_id = ?";
        } else if ($user_role == 'artist') {
            $follow_sql = "SELECT id FROM follow WHERE artist_id = ? AND followee_id = ?";
        } else {
            // Handle unexpected user roles
            echo "<p>Invalid user role.</p>";
            exit();
        }

        $follow_stmt = $conn->prepare($follow_sql);
        $follow_stmt->bind_param("ii", $user_id, $artist_id);
        $follow_stmt->execute();
        $follow_stmt->store_result();
        $followed = $follow_stmt->num_rows > 0;
        $follow_stmt->close();
    }

} else {
    echo "<p>Artist ID not provided.</p>";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Style/profile.css">
    <link rel="icon" type="image/x-icon" href="../Assets/icon/title-logo.svg">
    <title><?php echo htmlspecialchars($artist['name']); ?> | Profile</title>
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
    <div class="profile-container">
        <div class="profile-photo">
            <img src="<?php echo htmlspecialchars($artist['photo_path']); ?>" alt="<?php echo htmlspecialchars($artist['name']); ?>">
        </div>
        <div class="artist-name">
            <?php echo htmlspecialchars($artist['name']); ?>
        </div>
        <div class="intro">
            <?php echo htmlspecialchars($artist['about']); ?>
        </div>
        <div class="stats">
        <div class="Follower" id="follower-count">
        <?php echo $artist['followers']; ?>
        
    </div><span>Followers</span>

            <div class="Following">
                <?php echo htmlspecialchars($artist['following']); ?> <span>Following</span></div>
            <div class="Artwork"><?php echo htmlspecialchars($art_result->num_rows); ?> <span>Artwork</span></div>
        </div>
        <div class="cta-btn">
            <?php if ($user_id !== $artist_id) { ?>
                <button id="followBtn" class="followBtn" data-followed="<?php echo $followed ? 'true' : 'false'; ?>"><?php echo $followed ? 'Unfollow' : 'Follow'; ?></button>
                <button class="contactBtn">Get in touch</button>
            <?php } ?>

        </div>
    </div>

    <div class="tab">
        <div class="button-group">
            <button class="tablinks active" onclick="openTab(event, 'Work')">Work</button>
            <button class="tablinks" onclick="openTab(event, 'About')">About</button>
        </div>
    </div>

    <!-- Tab content -->
    <div id="Work" class="tabcontent default">
        <div class="art-wrap">
            <?php while ($art = $art_result->fetch_assoc()) { ?>
                <div class="box">
                    <a href="openArt.php?id=<?php echo htmlspecialchars($art['id']); ?>">
                        <img src="<?php echo htmlspecialchars($art['image_path']); ?>" alt="<?php echo htmlspecialchars($art['painting_name']); ?>">
                        <div class="text">
                            <div class="text-inner"><?php echo htmlspecialchars($art['painting_name']); ?></div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>

    <div id="About" class="tabcontent">
        <div class="about-inner">
            <div class="left">
                <div class="biography">
                    <div class="title">Biography</div>
                    <p><?php echo htmlspecialchars($artist['bio']); ?></p>
                </div>
            </div>
            <div class="right">
                <div class="title">Social</div>
                <div class="social-buttons">
                    <button class="social-btn">
                        <a href="<?php echo htmlspecialchars($artist['facebook_url']); ?>">
                            <img src="../Assets/icon/facebook-logo.png" alt="">
                        </a>
                    </button>
                    <button class="social-btn">
                        <a href="<?php echo htmlspecialchars($artist['linkedin_url']); ?>">
                            <img src="../Assets/icon/linkedin.png" alt="">
                        </a>
                    </button>
                    <button class="social-btn">
                        <a href="<?php echo htmlspecialchars($artist['instagram_url']); ?>">
                            <img src="../Assets/icon/instagram.png" alt="">
                        </a>
                    </button>
                    <button class="social-btn">
                        <a href="<?php echo htmlspecialchars($artist['x_url']); ?>">
                            <img src="../Assets/icon/twitter.png" alt="">
                        </a>
                    </button>
                </div>
            </div>
        </div>
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
        document.getElementById('followBtn').addEventListener('click', function() {
            const followed = this.getAttribute('data-followed') === 'true';
            const action = followed ? 'unfollow' : 'follow';

            fetch('follow.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    followee_id: <?php echo $artist_id; ?>,
                    action: action,
                    user_role: "<?php echo $user_role; ?>"
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.setAttribute('data-followed', !followed);
                    this.textContent = !followed ? 'Unfollow' : 'Follow';
                    document.querySelector('#follower-count').textContent = data.follow_count;
                } else {
                    console.error('Error:', data.message);
                    alert('An error occurred. Please try again.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred. Please try again.');
            });
        });
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
            document.body.classList.remove("Work", "About");

            // Add tab-specific class to body
            document.body.classList.add(tabName.replace(/\s+/g, '-').toLowerCase());

            // Initialize chart if Activity tab is opened
            if (tabName === 'Activity') {
                initChart();
            }
        }

        // Inisialisasi tab default saat halaman pertama kali dimuat
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelector(".tablinks.active").click();
        });
    </script>
</body>

</html>
