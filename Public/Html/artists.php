<?php
session_start();


include 'connect.php'; // Ensure your database connection is correct

// Redirect to home page if user is not logged in
$user_id = null;
$role = null;
$row = array();

// Check if session variables are set
if (isset($_SESSION["user_id"]) && isset($_SESSION["user_role"])) {
    $user_id = $_SESSION["user_id"];
    $role = $_SESSION["user_role"];

}

if ($user_id && $role) {
    if ($role == "artist") {
        $sql = "SELECT * FROM artist WHERE id=?";
    } else {
        $sql = "SELECT * FROM visitor WHERE id=?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    }

    $stmt->close();
}

// Fetch artist data from database
$sql = "SELECT id, name, photo_path FROM artist";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$artists = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $artists[] = $row;
    }
}
$conn->close();

// Set default image to the first artist's photo
$defaultImage = !empty($artists) ? $artists[0]['photo_path'] : 'default.jpg';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arte | Virtual Art Gallery</title>
    <link rel="icon" type="image/x-icon" href="../Assets/icon/title-logo.svg">
    <link rel="stylesheet" href="../Style/artists.css">
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

    <!-- REGISTRATION FORM MODAL -->
    <!-- Sign In Modal -->
    <div id="signinModal" class="modal signPop">
    <div class="content">
        <div class="leftContent">
            <div class="greet">
                <div class="textGreet">Welcome!</div>
            </div>
            <div class="description">
                <h1> Be an <span class="italic">Artist</span></h1>
                <p>Join our art education platform and develop your creative talents. Discover inspiration,
                    hone your skills, and become part of a vibrant community of passionate artists!</p>
            </div>
            <div class="question">    
                <div class="question-inner">
                    <p id="question-text">Not a member yet?</p>
                    <a href="#" id="switch-link" onclick="switchModal()">Register now</a>
                </div>
            </div>
        </div> 
        <div class="rightContent">
            <div class="mark" id="modal-title">Log in</div>
            <form action="login.php" method="POST" id="login-form">
                <label for="login-email">EMAIL</label>
                <input type="email" id="login-email" name="email" placeholder="Email" required>
                <label for="login-password">PASSWORD</label>
                <input type="password" id="login-password" name="password" placeholder="Password" required>
                <div class="checker" id="keep-logged-in">
                    <input type="checkbox" name="keep_logged_in">
                    <label for="keep_logged_in">Keep me logged in</label>
                </div>
                <input type="submit" value="Log in now" id="submit-button">
            </form>
            <div class="reset">
                <button>Forgot your password?</button>
            </div>
        </div>
    </div>
</div>

    <!-- Register Modal -->
<div id="registerModal" class="modal signPop">
    <div class="content">
        <div class="leftContent">
            <div class="greet">
                <div class="textGreet">Welcome!</div>
            </div>
            <div class="description">
                <h1> Be an <span class="italic">Artist</span></h1>
                <p>Join our art education platform and develop your creative talents. Discover inspiration,
                    hone your skills, and become part of a vibrant community of passionate artists!</p>
            </div>
            <div class="question">
                <div class="question-inner">
                    <p id="question-text">Are you a member?</p>
                    <a href="#" id="switch-link" onclick="switchModal()">Log in now</a>
                </div>
            </div>
        </div>
        <div class="rightContent">
            <div class="mark" id="modal-title">Let's start something</div>
            <form action="signIn.php" method="POST" id="modal-form">
                <label for="register-name">NAME</label>
                <input type="text" id="register-name" name="name" placeholder="Name" required>
                <label for="register-email">EMAIL</label>
                <input type="email" id="register-email" name="email" placeholder="Email" required>
                <label for="register-password">PASSWORD</label>
                <input type="password" id="register-password" name="password" placeholder="Password" required>
                <label for="register-role">ROLE</label>
                <select id="register-role" name="role" required>
                    <option value="" disabled selected class="selected">Select</option>
                    <option value="artist">Artist</option>
                    <option value="visitor">Visitor</option>
                </select>
                <div class="checker" id="terms-and-condition">
                    <input type="checkbox" required>
                    <label for="">I have read and accept the Terms and Condition</label>
                </div>
                <input type="submit" value="Register now" id="submit-button">
            </form>
        </div>
    </div>
</div>

    <!-- END SIGN IN MODAL -->

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
    <!--  -->


    <!-- MAIN CONTENT -->
   
    <div class="parent" data-scroll>
        <div class="titleSect">
            <div class="title">Artists</div>
        </div>
        <div class="list-wrap" >
            <ul>
                <?php foreach ($artists as $artist): ?>
                    <li>
                        <a href="profile.php?artist_id=<?php echo $artist['id']; ?>" 
                           data-image="<?php echo htmlspecialchars($artist['photo_path']); ?>" 
                           class="artist-name">
                           <?php echo htmlspecialchars($artist['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="image-wrap" data-scroll data-scroll-sticky data-scroll-target="#fixed">
            <div id="default-image" style="background-image: url('<?php echo htmlspecialchars($defaultImage); ?>')" data-scroll
                data-scroll-offset="100%">
            </div>
        </div>
    </div>
    <section>
        <div class="text-long">
            <p>Each artwork is a unique journey, where beauty and value can ebb and flow. Cherish the experience,
                but be mindful of the risks.</p>
        </div>
    </section>

    <div id="main">
        
    </div>


    <script src="../Javascript/locomotive-scroll.min.js"></script>
    <script>

        // Locomotive Scroll initialization (as per your existing setup)
        const scroll = new LocomotiveScroll({
            el: document.querySelector('#main'),
            smooth: true,
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

        // Function to handle opening and closing of sign-in modal
        const openSign = () => {
            openModal('signinModal');
        };

        const collapseSign = () => {
            closeModal('signinModal');
        };

        // Function to handle overlay navigation
        const openNav = () => {
            document.getElementById("mySidenav").style.width = "50%";
            document.getElementById("overlay").style.display = "block";
        };

        const closeNav = () => {
            document.getElementById("mySidenav").style.width = "0";
            document.getElementById("overlay").style.display = "none";
        };

        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            document.getElementById('overlay').style.display = 'block';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        // Function to switch between sign-in and register forms
        function switchModal() {
            const signInModal = document.getElementById('signinModal');
            const registerModal = document.getElementById('registerModal');

            if (signInModal.style.display === 'flex') {
                closeModal('signinModal');
                openModal('registerModal');

                document.getElementById('question-text').textContent = 'Are you a member?';
                const switchLink = document.getElementById('switch-link');
                switchLink.textContent = 'Log in now';
                switchLink.setAttribute('onclick', 'switchModal()');

                document.getElementById('modal-title').textContent = "Let's start something";
                document.getElementById('submit-button').value = 'Register now';
            } else if (registerModal.style.display === 'flex') {
                closeModal('registerModal');
                openModal('signinModal');

                document.getElementById('question-text').textContent = 'Not a member yet?';
                const switchLink = document.getElementById('switch-link');
                switchLink.textContent = 'Register now';
                switchLink.setAttribute('onclick', 'switchModal()');

                document.getElementById('modal-title').textContent = "Log in";
                document.getElementById('submit-button').value = 'Log in now';
            }
        }

        // Event listener to close modals when clicking outside
        window.onclick = function (event) {
            const signInModal = document.getElementById('signinModal');
            const registerModal = document.getElementById('registerModal');

            if (event.target === signInModal) {
                closeModal('signinModal');
            }
            if (event.target === registerModal) {
                closeModal('registerModal');
            }
        };

        // Add 'filled' class for inputs with value
        document.querySelectorAll('input, select').forEach(element => {
            const updateFilledClass = () => {
                if (element.value !== '') {
                    element.classList.add('filled');
                } else {
                    element.classList.remove('filled');
                }
            };

            element.addEventListener('input', updateFilledClass);
            element.addEventListener('blur', updateFilledClass);

            // Initialize the class on page load
            updateFilledClass();
        });

        // image view
        document.addEventListener("DOMContentLoaded", function () {
            const artistLinks = document.querySelectorAll(".artist-name");
            const defaultImage = document.getElementById("default-image");

            function setDefaultImage() {
                if (artistLinks.length > 0) {
                    const defaultImageUrl = artistLinks[0].getAttribute("data-image");
                    defaultImage.style.backgroundImage = `url('${defaultImageUrl}')`;
                    artistLinks[0].style.color = "#FFFFFF";
                }
            }

            setDefaultImage();

            artistLinks.forEach(function (link) {
                link.addEventListener("mouseover", function () {
                    const imageUrl = this.getAttribute("data-image");

                    // Tambahkan kelas fade-out untuk memulai transisi
                    defaultImage.classList.add("fade-out");

                    // Setel ulang warna teks untuk semua link seniman
                    artistLinks.forEach(function (link) {
                        link.style.color = "#818181";
                    });

                    // Tetapkan warna teks hitam untuk link yang dihover
                    this.style.color = "#FFFFFF";

                    // Setelah animasi fade-out selesai, ubah latar belakang dengan gambar yang baru
                    setTimeout(function () {
                        defaultImage.style.backgroundImage = `url('${imageUrl}')`;
                    }, 120); // Waktu yang sama dengan durasi transisi

                    // Setelah transisi selesai, hapus kelas fade-out
                    setTimeout(function () {
                        defaultImage.classList.remove("fade-out");
                    }, 120); // Waktu yang sedikit lebih lama dari durasi transisi
                });
            });
        });






    </script>
</body>

</html>