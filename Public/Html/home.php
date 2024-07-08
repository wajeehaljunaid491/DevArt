<?php
session_start();
include 'connect.php'; // Database connection

// Initialize variables
$user_id = null;
$role = null;
$row = array();

// Check if session variables are set
if (isset($_SESSION["user_id"]) && isset($_SESSION["user_role"])) {
    $user_id = $_SESSION["user_id"];
    $role = $_SESSION["user_role"];
}

// Query to fetch the latest event
$query = "SELECT title, description, image_path FROM events ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($query);

$event = array(
    'title' => 'Default Title',
    'description' => 'Default Description',
    'image_path' => '../Assets/image/exhibition.jpg' // Default image path
);

if ($result->num_rows > 0) {
    $event = $result->fetch_assoc();
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

// Fetch popular artists and their artworks
$sql = "SELECT a.id, a.painting_name, a.image_path, a.created_year, art.name AS artist_name 
        FROM art AS a 
        JOIN artist AS art ON a.artist_id = art.id 
        ORDER BY a.likes DESC 
        LIMIT 10";
$artistsResult = $conn->query($sql);

// Queries to fetch the latest news for each type
$newsTypes = ['Company Announcement', 'Best Practice', 'Product News','Artists News'];
$newsArticles = [];

foreach ($newsTypes as $type) {
    $newsQuery = "SELECT title, type, image_path FROM news WHERE type = ? ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($newsQuery);
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $newsResult = $stmt->get_result();

    if ($newsResult->num_rows > 0) {
        $newsArticles[] = $newsResult->fetch_assoc();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arte | Virtual Art Gallery</title>
    <link rel="icon" type="image/x-icon" href="../Assets/icon/title-logo.svg">
    <link rel="stylesheet" href="../Style/home.css">
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


    <!-- QUIZ SECTION -->
    <div class="quiz-wrap" id="quizWrap">
        <button id="openQuizBtn">Take a Quiz</button>
        <div class="brain-wrap">
            <img src="../Assets/icon/brain.png" alt="">
        </div>
    </div>

    <!-- Quiz Modal -->
    <div id="quiz-modal" class="quiz-modal">
        <div id="intro-content" class="quiz-content active">
            <h2>Welcome to the Art Quiz!</h2>
            <p>Discover your inner artist! This quiz will take you on a journey to explore your appreciation and love
                for art. Whether you're a seasoned art lover or just beginning to explore the world of art, this quiz
                will help you understand how deeply art influences your life.</p>
            <p>You'll answer a series of questions about your experiences, preferences, and feelings towards art. At the
                end, you'll receive a personalized result that reflects your artistic level.</p>
            <p>Ready to uncover your artistic side? Click the button below to start the quiz and dive into the
                fascinating world of art!</p>
            <div class="button-group">
                <button id="start-btn">Start Quiz</button>
            </div>
        </div>

        <form id="quiz-form">
            <div id="quiz-content-1" class="quiz-content">
                <p>Question 1: How often do you visit art galleries or museums?</p>
                <div>
                    <input type="radio" id="answer-1-never" name="answer-1" value="Never">
                    <label for="answer-1-never">Never</label><br>
                    <input type="radio" id="answer-1-occasionally" name="answer-1" value="Occasionally">
                    <label for="answer-1-occasionally">Occasionally</label><br>
                    <input type="radio" id="answer-1-sometimes" name="answer-1" value="Sometimes">
                    <label for="answer-1-sometimes">Sometimes</label><br>
                    <input type="radio" id="answer-1-often" name="answer-1" value="Often">
                    <label for="answer-1-often">Often</label><br>
                    <input type="radio" id="answer-1-very-often" name="answer-1" value="Very Often">
                    <label for="answer-1-very-often">Very Often</label><br>
                </div>
                <div class="button-group">
                    <button type="button" id="next-btn-1">Next</button>
                </div>
            </div>

            <div id="quiz-content-2" class="quiz-content">
                <p>Question 2: How much do you enjoy creating art (e.g., drawing, painting, crafting)?</p>
                <div>
                    <input type="radio" id="answer-2-not-at-all" name="answer-2" value="Not at all">
                    <label for="answer-2-not-at-all">Not at all</label><br>
                    <input type="radio" id="answer-2-a-little" name="answer-2" value="A little">
                    <label for="answer-2-a-little">A little</label><br>
                    <input type="radio" id="answer-2-moderately" name="answer-2" value="Moderately">
                    <label for="answer-2-moderately">Moderately</label><br>
                    <input type="radio" id="answer-2-quite-a-bit" name="answer-2" value="Quite a bit">
                    <label for="answer-2-quite-a-bit">Quite a bit</label><br>
                    <input type="radio" id="answer-2-very-much" name="answer-2" value="Very much">
                    <label for="answer-2-very-much">Very much</label><br>
                </div>
                <div class="button-group">
                    <button type="button" id="prev-btn-2">Previous</button>
                    <button type="button" id="next-btn-2">Next</button>
                </div>
            </div>

            <div id="quiz-content-3" class="quiz-content">
                <p>Question 3: How important is art in your daily life?</p>
                <div>
                    <input type="radio" id="answer-3-not-important" name="answer-3" value="Not important">
                    <label for="answer-3-not-important">Not important</label><br>
                    <input type="radio" id="answer-3-slightly-important" name="answer-3" value="Slightly important">
                    <label for="answer-3-slightly-important">Slightly important</label><br>
                    <input type="radio" id="answer-3-moderately-important" name="answer-3" value="Moderately important">
                    <label for="answer-3-moderately-important">Moderately important</label><br>
                    <input type="radio" id="answer-3-very-important" name="answer-3" value="Very important">
                    <label for="answer-3-very-important">Very important</label><br>
                    <input type="radio" id="answer-3-extremely-important" name="answer-3" value="Extremely important">
                    <label for="answer-3-extremely-important">Extremely important</label><br>
                </div>
                <div class="button-group">
                    <button type="button" id="prev-btn-3">Previous</button>
                    <button type="button" id="next-btn-3">Next</button>
                </div>
            </div>

            <div id="quiz-content-4" class="quiz-content">
                <p>Question 4: How frequently do you engage in art-related activities (e.g., watching art videos, reading about art)?</p>
                <div>
                    <input type="radio" id="answer-4-never" name="answer-4" value="Never">
                    <label for="answer-4-never">Never</label><br>
                    <input type="radio" id="answer-4-rarely" name="answer-4" value="Rarely">
                    <label for="answer-4-rarely">Rarely</label><br>
                    <input type="radio" id="answer-4-sometimes" name="answer-4" value="Sometimes">
                    <label for="answer-4-sometimes">Sometimes</label><br>
                    <input type="radio" id="answer-4-often" name="answer-4" value="Often">
                    <label for="answer-4-often">Often</label><br>
                    <input type="radio" id="answer-4-very-often" name="answer-4" value="Very often">
                    <label for="answer-4-very-often">Very often</label><br>
                </div>
                <div class="button-group">
                    <button type="button" id="prev-btn-4">Previous</button>
                    <button type="button" id="next-btn-4">Next</button>
                </div>
            </div>

            <div id="quiz-content-5" class="quiz-content">
                <p>Question 5: How do you feel when you see a piece of art that you like?</p>
                <div>
                    <input type="radio" id="answer-5-indifferent" name="answer-5" value="Indifferent">
                    <label for="answer-5-indifferent">Indifferent</label><br>
                    <input type="radio" id="answer-5-slightly-happy" name="answer-5" value="Slightly happy">
                    <label for="answer-5-slightly-happy">Slightly happy</label><br>
                    <input type="radio" id="answer-5-moderately-happy" name="answer-5" value="Moderately happy">
                    <label for="answer-5-moderately-happy">Moderately happy</label><br>
                    <input type="radio" id="answer-5-very-happy" name="answer-5" value="Very happy">
                    <label for="answer-5-very-happy">Very happy</label><br>
                    <input type="radio" id="answer-5-extremely-happy" name="answer-5" value="Extremely happy">
                    <label for="answer-5-extremely-happy">Extremely happy</label><br>
                </div>
                <div class="button-group">
                    <button type="button" id="prev-btn-5">Previous</button>
                    <button type="submit" id="submit-btn">Submit</button>
                </div>
            </div>
        </form>

        <div id="quiz-results" class="quiz-content">
            <p>Thank you for completing the quiz! Your result is:</p>
            <p id="result-text">[Result will be generated by AI]</p>
            <div class="button-group">
                <button id="close-btn">Close</button>
            </div>
        </div>
    </div>




    <!-- HERO SECTION -->
    <div id="main">
        <main class="parent">
            <span class="title-hero" data-scroll>
                <h1>One Stop Edu-Art Platform</h1>
            </span>
            <div class="image-wrap">
                <span class="left-hand" data-scroll-direction="horizontal"></span>
                <span class="right-hand"></span>
            </div>
        </main>

        <section class="word-container" data-scroll>
            <div class="word">
                <p>Each artwork is a unique story inviting you to <span class="coloring">see the world from a new
                        perspective</span></p>
            </div>
        </section>

       
        <!-- DISCOVER ART SECTION -->
        <section class="most-art" data-scroll>
    <div class="title-section">
        <h2>DISCOVER ART</h2>
    </div>
    <div>
        <div class="line"></div>
    </div>
    <div class="artwork-container">
        <div class="art-row">
            <div class="item">
                <div class="img-wrap">
                <?php if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    // Set a flag to indicate that the modal should be opened
    echo '<script>var openSignInModal = true;</script>';
} else {
    echo '<script>var openSignInModal = false;</script>';
}?>
                    <a href="artwork.php?art_type=Abstract">
                        <img src="../Assets/image/abstract.jpg" alt="">
                    </a>
                </div>
                <p class="caption">Abstract</p>
            </div>
            <div class="item">
                <div class="img-wrap">
                    <a href="artwork.php?art_type=Animal">
                        <img src="../Assets/image/animal.jpg" alt="">
                    </a>
                </div>
                <p class="caption">Animal</p>
            </div>
            <div class="item">
                <div class="img-wrap">
                    <a href="artwork.php?art_type=Botanical">
                        <img src="../Assets/image/botanical.jpg" alt="">
                    </a>
                </div>
                <p class="caption">Botanical</p>
            </div>
            <div class="item">
                <div class="img-wrap">
                    <a href="artwork.php?art_type=Drawings">
                        <img src="../Assets/image/drawing.jpg" alt="">
                    </a>
                </div>
                <p class="caption">Drawings</p>
            </div>
            <div class="item">
                <div class="img-wrap">
                    <a href="artwork.php?art_type=Illustration">
                        <img src="../Assets/image/illustration.jpg" alt="">
                    </a>
                </div>
                <p class="caption">Illustration</p>
            </div>
        </div>
        <div class="art-row">
            <div class="item">
                <div class="img-wrap">
                    <a href="artwork.php?art_type=Figurative">
                        <img src="../Assets/image/figurative.jpg" alt="">
                    </a>
                </div>
                <p class="caption">Figurative</p>
            </div>
            <div class="item">
                <div class="img-wrap">
                    <a href="artwork.php?art_type=Landscape">
                        <img src="../Assets/image/landscape.jpg" alt="">
                    </a>
                </div>
                <p class="caption">Landscape</p>
            </div>
            <div class="item">
                <div class="img-wrap">
                    <a href="artwork.php?art_type=Mythology">
                        <img src="../Assets/image/mythology.jpg" alt="">
                    </a>
                </div>
                <p class="caption">Mythology</p>
            </div>
            <div class="item">
                <div class="img-wrap">
                    <a href="artwork.php?art_type=Religion">
                        <img src="../Assets/image/religion.jpg" alt="">
                    </a>
                </div>
                <p class="caption">Religion</p>
            </div>
            <div class="item">
                <div class="img-wrap">
                    <a href="artwork.php?art_type=Still Life">
                        <img src="../Assets/image/stillLife.jpg" alt="">
                    </a>
                </div>
                <p class="caption">Still Life</p>
            </div>
        </div>
    </div>
</section>

<!-- POPULAR ARTISTS SECTION -->
<section class="popular" data-scroll>
    <div class="title-section">
        <h2>POPULAR ARTISTS</h2>
    </div>
    <div class="content-divide">
        <div id="default-image" style="background-image: url('default.jpg')"></div>
        <div class="list">
            <!-- scrolling -->
            <div class="scrolling-text-container">
                <div class="scrolling-text-inner" style="--marquee-speed: 20s; --direction:scroll-left" role="marquee">
                    <div class="scrolling-text">
                        <div class="scrolling-text-item">Most Celebrate Artists</div>
                        <div class="scrolling-text-item">➛</div>
                        <div class="scrolling-text-item">Most Popular Artwork</div>
                        <div class="scrolling-text-item">➛</div>
                        <div class="scrolling-text-item">Most Celebrate Artists</div>
                        <div class="scrolling-text-item">➛</div>
                        <div class="scrolling-text-item">Most Popular Artwork</div>
                        <div class="scrolling-text-item">➛</div>
                    </div>
                    <div class="scrolling-text">
                        <div class="scrolling-text-item">Most Celebrate Artists</div>
                        <div class="scrolling-text-item">➛</div>
                        <div class="scrolling-text-item">Most Popular Artwork</div>
                        <div class="scrolling-text-item">➛</div>
                        <div class="scrolling-text-item">Most Celebrate Artists</div>
                        <div class="scrolling-text-item">➛</div>
                        <div class="scrolling-text-item">Most Popular Artwork</div>
                        <div class="scrolling-text-item">➛</div>
                    </div>
                </div>
            </div>
            <!-- End scroll -->
            
            <?php
            if ($artistsResult->num_rows > 0) {
                while ($artist = $artistsResult->fetch_assoc()) {
                    echo '<div class="row">
                        <a href="openArt.php?id=' . htmlspecialchars($artist['id']) . '" class="art-show" data-image="' . htmlspecialchars($artist['image_path']) . '"> 
                            <span class="name">' . htmlspecialchars($artist['artist_name']) . '</span>
                            <span class="artName">' . htmlspecialchars($artist['painting_name']) . '</span>
                            <span class="year">' . htmlspecialchars($artist['created_year']) . '</span>
                        </a>
                    </div>';
                }
            } else {
                echo "<p>No popular artists found.</p>";
            }
            ?>
        </div>
    </div>
</section>


        <!-- EXHIBITION INFO -->
      
        <section class="exhibition">
    <div class="exhibition-container">
        <div class="exhibition-image">
            <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="">
        </div>
        <div class="explainer-contain">
            <div class="title-exhibition"><?php echo htmlspecialchars($event['title']); ?></div>
            <p><?php echo htmlspecialchars($event['description']); ?></p>
            <button onclick="openInfoModal()">MORE INFO</button>
        </div>
    </div>
</section>

        <!-- NEWS SECTION -->
        <!-- <section data-scroll class="news">
            <div class="title-section">
                <h2>NEWS & INSIGHTS</h2>
            </div>
            <div class="article-container">
                <a class="article" href="">
                    <div class="articleImage">
                        <img src="../Assets/image/article1.jpg" alt="">
                    </div>
                    <div class="title">
                        <p>The Most Famous Van Gogh Paintings Everyone Should Know</p>
                    </div>
                    <div class="tag">
                        <p>Company Announcement</p>
                    </div>
                    <div class="line2"></div>
                </a>
                <a class="article" href="">
                    <div class="articleImage">
                        <img src="../Assets/image/article2.jpg" alt="">
                    </div>
                    <div class="title">
                        <p>Banksy Street Art Tour: The Best Graffiti by the Most Secretive of Artists</p>
                    </div>
                    <div class="tag">
                        <p>Best Practice</p>
                    </div>
                    <div class="line2"></div>
                </a>
                <a class="article" href="">
                    <div class="articleImage">
                        <img src="../Assets/image/article3.jpg" alt="">
                    </div>
                    <div class="title">
                        <p>10 Controversial Artworks That Changed Art History</p>
                    </div>
                    <div class="tag">
                        <p>Best Practice</p>
                    </div>
                    <div class="line2"></div>
                </a>
                <a class="article" href="">
                    <div class="articleImage">
                        <img src="../Assets/image/article4.jpg" alt="">
                    </div>
                    <div class="title">
                        <p>The 30 Most Popular Modern & Contemporary Artists</p>
                    </div>
                    <div class="tag">
                        <p>Product News</p>
                    </div>
                    <div class="line2"></div>
                </a>
            </div>
        </section> -->
        <section data-scroll class="news">
    <div class="title-section">
        <h2>NEWS & INSIGHTS</h2>
    </div>
    <div class="article-container">
        <?php foreach ($newsArticles as $article): ?>
            <a class="article" href="">
                <div class="articleImage">
                    <img src="<?php echo htmlspecialchars($article['image_path']); ?>" alt="">
                </div>
                <div class="title">
                    <p><?php echo htmlspecialchars($article['title']); ?></p>
                </div>
                <div class="tag">
                    <p><?php echo htmlspecialchars($article['type']); ?></p>
                </div>
                <div class="line2"></div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

        <section data-scroll>
            <div class="text-long">
                <p>Each artwork is a unique journey, where beauty and value can ebb and flow. Cherish the experience,
                    but be mindful of the risks.</p>
            </div>
        </section>


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
    </div>
    <!--  -->




    <script src="../Javascript/locomotive-scroll.min.js"></script>
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
        // Locomotive Scroll initialization (as per your existing setup)
        const scroll = new LocomotiveScroll({
            el: document.querySelector('#main'),
            smooth: true,
        });
        const leftHand = document.querySelector('.left-hand');
        const rightHand = document.querySelector('.right-hand');

        scroll.on('scroll', (obj) => {
            const scrollY = obj.scroll.y;
            leftHand.style.transform = `translateX(-${scrollY}px)`;
            rightHand.style.transform = `translateX(${scrollY}px)`;
        });

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
            const infoModal = document.getElementById('infoModal');

            if (event.target === signInModal) {
                closeModal('signinModal');
            }
            if (event.target === registerModal) {
                closeModal('registerModal');
            }
            if (event.target === infoModal) {
                closeInfoModal();
            }
        };

        // Hover effect for default image (as per your existing setup)
        document.addEventListener("DOMContentLoaded", function () {
            const artistLinks = document.querySelectorAll(".art-show");
            const defaultImage = document.getElementById("default-image");

            function setDefaultImage() {
                if (artistLinks.length > 0) {
                    const defaultImageUrl = artistLinks[0].getAttribute("data-image");
                    defaultImage.style.backgroundImage = `url('${defaultImageUrl}')`;
                }
            }

            setDefaultImage();

            artistLinks.forEach(function (link) {
                link.addEventListener("mouseover", function () {
                    const imageUrl = this.getAttribute("data-image");
                    defaultImage.classList.add("fade-out");
                    setTimeout(function () {
                        defaultImage.style.backgroundImage = `url('${imageUrl}')`;
                    }, 120);

                    setTimeout(function () {
                        defaultImage.classList.remove("fade-out");
                    }, 120);
                });
            });
        });

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

        function disableScroll() {
            scroll.stop();
        }

        function enableScroll() {
            scroll.start();
        }

        function resetQuizModal() {
            const answers = document.querySelectorAll('input[type="radio"], input[type="checkbox"]');
            answers.forEach(answer => {
                answer.checked = false;
            });
        }

        // QUIZ JAVASCRIPT
        document.addEventListener('DOMContentLoaded', function () {
            const openQuizBtn = document.getElementById('openQuizBtn');
            const quizModal = document.getElementById('quiz-modal');
            const startBtn = document.getElementById('start-btn');
            const introContent = document.getElementById('intro-content');
            const submitBtn = document.getElementById('submit-btn');
            const closeBtn = document.getElementById('close-btn');
            let currentQuestion = 1;

            // Function to show the quiz modal
            openQuizBtn.addEventListener('click', () => {
                disableScroll(); // Menonaktifkan Locomotive Scroll
                quizModal.style.display = 'block';
                quizModal.classList.add('fade-in');
                document.body.classList.add('modal-open'); // Tambahkan kelas modal-open ke body
            });

            // Function to start the quiz
            startBtn.addEventListener('click', () => {
                introContent.classList.remove('active');
                document.getElementById(`quiz-content-${currentQuestion}`).classList.add('active');
            });

            // Function to handle the next button click
            const nextBtns = Array.from(document.querySelectorAll('[id^=next-btn]'));
            nextBtns.forEach((btn, index) => {
                btn.addEventListener('click', () => {
                    if (isAnswerSelected(currentQuestion)) {
                        if (currentQuestion < 5) {
                            document.getElementById(`quiz-content-${currentQuestion}`).classList.remove('active');
                            currentQuestion++;
                            document.getElementById(`quiz-content-${currentQuestion}`).classList.add('active');
                        }
                    } else {
                        alert('Please select an answer before proceeding.');
                    }
                });
            });

            // Function to handle the previous button click
            const prevBtns = Array.from(document.querySelectorAll('[id^=prev-btn]'));
            prevBtns.forEach((btn, index) => {
                btn.addEventListener('click', () => {
                    if (currentQuestion > 1) {
                        document.getElementById(`quiz-content-${currentQuestion}`).classList.remove('active');
                        currentQuestion--;
                        document.getElementById(`quiz-content-${currentQuestion}`).classList.add('active');
                    }
                });
            });

            // Function to handle the submit button click
            submitBtn.addEventListener('click', () => {
                if (isAnswerSelected(currentQuestion)) {
                    document.getElementById(`quiz-content-${currentQuestion}`).classList.remove('active');
                    document.getElementById('quiz-results').classList.add('active');

                    // Here you can add code to calculate and display the result
                    const resultText = document.getElementById('result-text');
                    resultText.textContent = 'Your artistic level is ... [generated result]';
                } else {
                    alert('Please select an answer before proceeding.');
                }
            });

            // Function to close the quiz modal
            closeBtn.addEventListener('click', () => {
                enableScroll(); // Mengaktifkan kembali Locomotive Scroll
                quizModal.classList.add('fade-out');
                setTimeout(() => {
                    quizModal.style.display = 'none';
                    quizModal.classList.remove('fade-out');

                    resetQuizModal();

                    // Reset to initial state
                    document.querySelectorAll('.quiz-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    currentQuestion = 1;
                    introContent.classList.add('active');
                }, 500);
            });

            // Function to check if an answer is selected for the current question
            function isAnswerSelected(questionNumber) {
                const answers = document.getElementsByName(`answer-${questionNumber}`);
                for (let i = 0; i < answers.length; i++) {
                    if (answers[i].checked) {
                        return true;
                    }
                }
                return false;
            }
        });


        // Hide quiz-wrap initially
        quizWrap.style.display = 'none';

        // Set timeout to show quiz-wrap after 10 seconds
        setTimeout(() => {
            quizWrap.style.display = 'flex';
            quizWrap.classList.add('fade-in');
        }, 3000); // 10000 milliseconds = 10 seconds

        document.getElementById('start-btn').addEventListener('click', function() {
            document.getElementById('intro-content').classList.remove('active');
            document.getElementById('quiz-content-1').classList.add('active');
        });

        // Add navigation and form handling script here
        document.getElementById('quiz-form').addEventListener('submit', async function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });

            const questions = [
                "Question 1: How often do you visit art galleries or museums?",
                "Question 2: How much do you enjoy creating art (e.g., drawing, painting, crafting)?",
                "Question 3: How important is art in your daily life?",
                "Question 4: How frequently do you engage in art-related activities (e.g., watching art videos, reading about art)?",
                "Question 5: How do you feel when you see a piece of art that you like?"
            ];

            const payload = {
                questions,
                answers: data
            };

            const response = await fetch('http://localhost:3000/submit-quiz', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            document.getElementById('result-text').innerText = result.message;
            document.getElementById('quiz-content-5').classList.remove('active');
            document.getElementById('quiz-results').classList.add('active');
        });
    </script>
</body>

</html>