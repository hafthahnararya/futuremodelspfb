<?php
require_once '../utils/function.php';
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        redirect('../Admin/adminPage.php');
        exit();
    } else {
        redirect('../index.php');
        exit();
    }
}
$flashMessage = getFlashMessage();

$rememberedEmail = '';
if (isset($_COOKIE['user_email'])) {
    $rememberedEmail = $_COOKIE['user_email'];
} elseif (isset($_COOKIE['admin_email'])) {
    $rememberedEmail = $_COOKIE['admin_email'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Future Models - Login</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <?php displayFlashMessage(); ?>
    <nav>
        <div class="logo">
            <a href="../index.php">
            <img src="../assets/image/Future.png" alt=""></a>
        </div>
        <ul class="nav-links">
            <li><a href="about.php">ABOUT</a></li>
            <li><a href="model.php">MODELS</a></li>
            <li><a href="forum.php">FORUM</a></li>
            <li><a href="login.php" class="active">LOGIN</a></li>
            <li><a href="signUp.php">SIGN UP</a></li>
        </ul>
    </nav>
    
    <section class="login-section">
        <div class="login-background"></div>
        <video class="signup-video" autoplay muted loop playsinline id="bgVideo">
            <source src="../assets/video/background.mp4" type="video/mp4">
        </video>
        <div class="main-content">
            <div class="login-content">
                <p>Jakarta-based model management agency with a focus on diverse, standout talent.</p>
                <p>We represent a curated roster of distinctive faces and personalities.</p>
                <p>Each model embodies individuality that sets new standards in the industry.</p>
                <p>Our mission is to develop and elevate the most exceptional talents in fashion today.</p>
            </div>

            <div class="login-form-container">
                <h2>LOGIN WITH YOUR EMAIL:</h2>
                
                <?php if ($flashMessage): ?>
                    <div class="flash-message <?php echo $flashMessage['type']; ?>">
                        <?php echo htmlspecialchars($flashMessage['message']); ?>
                    </div>
                <?php endif; ?>
                
                <form action="process_login.php" method="post" id="loginForm">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" 
                               name="email" 
                               placeholder="your@email.com" 
                               value="<?php echo htmlspecialchars($rememberedEmail); ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" 
                               name="password" 
                               placeholder="Enter your password" 
                               required>
                    </div>

                    <div class="remember-me">
                        <input type="checkbox" 
                               id="remember" 
                               name="remember"
                               <?php echo !empty($rememberedEmail) ? 'checked' : ''; ?>>
                        <label for="remember">Remember me?</label>
                    </div>
                    
                    <button type="submit" class="login-button">LOGIN</button>
                    
                    <div class="register-link">
                        Don't have an account yet? <a href="signUp.php">Click here to Register.</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <section class="future-quote">
        <div class="quote-text">
            THE FUTURE MOVES FAST — WE MOVE FIRST.
        </div>
    </section>
    
    <footer>
        <div class="footer-logo">
            <img src="../assets/image/Future.png" alt="Future Models">
        </div>
        
        <ul class="footer-nav">
            <li><a href="about.php">ABOUT</a></li>
            <li><a href="model.php">MODELS</a></li>
            <li><a href="forum.php">FORUM</a></li>
            <li><a href="signUp.php">Sign Up</a></li>
        </ul>

        <div class="footer-contact">
            info@futuremodels.id.com
        </div>

        <div class="social-links">
            <a href="#" aria-label="Instagram"><img src="../assets/image/mdi_instagram.png" alt="Instagram"></a>
            <a href="#" aria-label="YouTube"><img src="../assets/image/mdi_youtube.png" alt="YouTube"></a>
            <a href="#" aria-label="TikTok"><img src="../assets/image/ic_baseline-tiktok.png" alt="TikTok"></a>
        </div>
        <div class="copyright">
            Copyright © futuremodels.id 2025
        </div>
    </footer>

    <script src="../js/login.js"></script>

</body>
</html>