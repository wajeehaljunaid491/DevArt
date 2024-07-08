<?php
session_start();

include 'connect.php'; // Assuming connect.php includes your database connection

// Get the art_type from the URL
$art_type = isset($_GET['art_type']) ? $_GET['art_type'] : '';

if ($art_type == '') {
    // Redirect to the homepage or show an error if art_type is not provided
    header("Location: home.php");
    exit;
}

// Query to fetch all artworks of the given art type
$sql = "SELECT id, painting_name, image_path, art_description FROM art WHERE art_type=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $art_type);
$stmt->execute();
$result = $stmt->get_result();

$artworks = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $artworks[] = $row;
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link rel="stylesheet" href="../Style/artwork.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
  </head>

  <body>
    <!-- NAVBAR -->
    <nav id="navbar">
      <a class="logo" href="home.php">
        <img src="../Assets/icon/arté.png" alt="" />
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
              }?>
        </div>
          <div class="open-menu">
            <button onclick="openNav()">
              <img src="../Assets/icon/icon-menu.png" alt="" />
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

    <div id="main">
      <div class="container" data-scroll>
        <div class="category">
        <div class="title-sect">
         <?php echo htmlspecialchars($art_type); ?>
        </div>
        <div class="meaning">
                    <p>
                        <?php
                        // You can provide a description for each art type here
                        if ($art_type == 'Abstract') {
                            echo 'Style that does not attempt to represent reality accurately but instead uses shapes, colors, forms, and textures to achieve its effect.';
                        }
                        // Add descriptions for other art types similarly
                        ?>
                    </p>
                </div>
          <div class="line"></div>
        </div>

        <div class="artwork-container" data-scroll>
                <div class="art-wrap">
                    <?php
                    foreach ($artworks as $artwork) {
                        ?>
                        <div class="box">
                            <a href="openArt.php?id=<?php echo urlencode($artwork['id']); ?>">
                                <img src="<?php echo htmlspecialchars($artwork['image_path']); ?>" alt="">
                                <div class="text">
                                    <div class="text-inner">See Artwork</div>
                                </div>
                            </a>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
      </div>
    </div>

    <!-- FOOTER SECTION -->
    <section class="footer" data-scroll>
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

    <section class="watermark" data-scroll>
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

      // Function to handle opening and closing of sign-in modal
      const openSign = () => {
        openModal("signinModal");
      };

      const collapseSign = () => {
        closeModal("signinModal");
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
        modal.style.display = "flex";
        document.getElementById("overlay").style.display = "block";
      }

      function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = "none";
        document.getElementById("overlay").style.display = "none";
      }

      // Function to switch between sign-in and register forms
      function switchModal() {
        const signInModal = document.getElementById("signinModal");
        const registerModal = document.getElementById("registerModal");

        if (signInModal.style.display === "flex") {
          closeModal("signinModal");
          openModal("registerModal");

          document.getElementById("question-text").textContent =
            "Are you a member?";
          const switchLink = document.getElementById("switch-link");
          switchLink.textContent = "Log in now";
          switchLink.setAttribute("onclick", "switchModal()");

          document.getElementById("modal-title").textContent =
            "Let's start something";
          document.getElementById("submit-button").value = "Register now";
        } else if (registerModal.style.display === "flex") {
          closeModal("registerModal");
          openModal("signinModal");

          document.getElementById("question-text").textContent =
            "Not a member yet?";
          const switchLink = document.getElementById("switch-link");
          switchLink.textContent = "Register now";
          switchLink.setAttribute("onclick", "switchModal()");

          document.getElementById("modal-title").textContent = "Log in";
          document.getElementById("submit-button").value = "Log in now";
        }
      }

      // Event listener to close modals when clicking outside
      window.onclick = function (event) {
        const signInModal = document.getElementById("signinModal");
        const registerModal = document.getElementById("registerModal");

        if (event.target === signInModal) {
          closeModal("signinModal");
        }
        if (event.target === registerModal) {
          closeModal("registerModal");
        }
      };

      // Add 'filled' class for inputs with value
      document.querySelectorAll("input, select").forEach((element) => {
        const updateFilledClass = () => {
          if (element.value !== "") {
            element.classList.add("filled");
          } else {
            element.classList.remove("filled");
          }
        };

        element.addEventListener("input", updateFilledClass);
        element.addEventListener("blur", updateFilledClass);

        // Initialize the class on page load
        updateFilledClass();
      });
    </script>
  </body>
</html>
