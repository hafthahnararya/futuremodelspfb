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
$flashMessage=getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Future Models - Sign Up</title>
    <link rel="stylesheet" href="../assets/css/signupstyle.css">
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
            <li><a href="login.php">LOGIN</a></li>
            <li><a href="signUp.php" class="active">SIGN UP</a></li>
        </ul>
    </nav>
    
    <section class="signup-section">
        <div class="signup-background"></div>
        <video class="signup-video" autoplay muted loop playsinline id="bgVideo">
            <source src="../assets/video/background.mp4" type="video/mp4">
        </video>
        <div class="main-content">
            <div class="signup-content">
                <p>Join our exclusive community of models and fashion enthusiasts.</p>
                <p>Create your account to access exclusive content and opportunities.</p>
                <p>Connect with industry professionals and showcase your talent.</p>
                <p>Be part of the future of fashion modeling.</p>
            </div>

            <div class="signup-form-container">
                <h2>CREATE YOUR ACCOUNT:</h2>
                <?php if ($flashMessage): ?>
                    <div class="flash-message <?php echo $flashMessage['type']; ?>">
                        <?php echo $flashMessage['message']; ?>
                    </div>
                <?php endif; ?>
                
                <form action="process_register.php" method="post" id="signupForm">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               placeholder="Enter your full name" 
                               value="<?php echo isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : ''; ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               placeholder="Choose a unique username" 
                               value="<?php echo isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : ''; ?>"
                               required>
                        <small style="color: #666; font-size: 0.85rem; margin-top: 5px; display: block;">
                            3-20 characters, letters, numbers, and underscores only
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" 
                               id="dob" 
                               name="dob" 
                               value="<?php echo isset($_SESSION['form_data']['dob']) ? htmlspecialchars($_SESSION['form_data']['dob']) : ''; ?>"
                               max="<?php echo date('Y-m-d', strtotime('-13 years')); ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               placeholder="+62 812 3456 7890" 
                               value="<?php echo isset($_SESSION['form_data']['phone']) ? htmlspecialchars($_SESSION['form_data']['phone']) : ''; ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               placeholder="your@email.com" 
                               value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="At least 8 characters" 
                               required>
                        <small style="color: #666; font-size: 0.85rem; margin-top: 5px; display: block;">
                            Must contain uppercase, lowercase, and numbers
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" 
                               id="confirmpassword" 
                               name="confirmpassword" 
                               placeholder="Re-enter your password" 
                               required>
                    </div>

                    <div class="terms-agreement">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                            <a href="#" target="_blank">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <button type="submit" id="signup" class="signup-button">CREATE ACCOUNT</button>
                    
                    <div class="login-link">
                        Already have an account? <a href="login.php">Click here to Login.</a>
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

    <script src="../assets/js/signup.js"></script>
</body>
</html>
<?php
unset($_SESSION['form_data']);
?>