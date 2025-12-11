<?php
require_once '../utils/function.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Future Models - About</title>
<link rel="stylesheet" href="../assets/css/about.css">
</head>
<body>
    <?php displayFlashMessage(); ?>
    <nav>
        <div class="logo">
            <a href="../index.php">
            <img src="../assets/image/Future.png" alt=""></a>
        </div>
        <ul class="nav-links">
            <li><a href="about.php" class="active">ABOUT</a></li>
            <li><a href="model.php">MODELS</a></li>
            <li><a href="forum.php">FORUM</a></li>
            <?php if (isUserLoggedIn()): ?>
                <li><a href="userProfile.php">PROFILE</a></li>
                <li><a href="../Admin/logout.php">LOGOUT</a></li>
            <?php else: ?>
                <li><a href="login.php">LOGIN</a></li>
                <li><a href="signUp.php">SIGN UP</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <section class="hero-section">
        <div class="hero-content">
            <h1>WE ARE FUTUREMODELS.ID</h1>
            <p class="subtitle">Representing those who redefine confidence and command presence.</p>
        </div>
        <div class="hero-image">
            <img src="../assets/image/About/image 13.png" alt="">
        </div>
    </section>

    <section class="text-section">
        <div class="empty-column"></div>
        <div class="text-column">
            <p>Established in Jakarta, FutureModels.id stands as a pioneering force in modern model management — built on vision, authenticity, and empowerment. Since our inception, we have remained dedicated to cultivating long-term relationships that uplift our talents and redefine the creative landscape through collaboration, integrity, and shared growth.</p>
            <p>To distinguish ourselves, we go beyond representation. We nurture individuality, champion diversity, and shape each model's journey through strategic development and personalized guidance — ensuring every collaboration reflects precision, passion, and purpose.</p>
            <p>Yet, our ambition extends beyond excellence. We aspire to lead — setting new standards of professionalism, inclusivity, and artistic innovation within Indonesia's fashion industry and beyond. Guided by a future-driven mindset, we embody the spirit of leadership and creativity that inspires those who follow.</p>
            <p>At FutureModels.id, being a model transcends the surface. It is a statement of character — a commitment to confidence, authenticity, and influence. We are more than an agency; we are a collective that empowers individuals to stand for something greater, shaping not only their careers but the culture that surrounds them.</p>
            <p>Through every face we represent, FutureModels.id continues to redefine what it means to be seen — building a legacy of vision, diversity, and empowerment for the next generation.</p>
        </div>
    </section>
    <footer>
        <div class="footer-logo">
            <img src="assets/Future.png" alt="">
        </div>
        
        <ul class="footer-nav">
            <li><a href="about.php">ABOUT</a></li>
            <li><a href="model.php">MODELS</a></li>
            <li><a href="forum.php">FORUM</a></li>
            <li><a href="signUp.php">SIGN UP</a></li>
        </ul>
        <div class="footer-contact">
            info@futuremodels.id.com
        </div>

        <div class="social-links">
            <a href="#" aria-label="Instagram"><img src="../assets/image/mdi_instagram.png" alt=""></a>
            <a href="#" aria-label="YouTube"><img src="../assets/image/mdi_youtube.png" alt=""></a>
            <a href="#" aria-label="TikTok"><img src="../assets/image/ic_baseline-tiktok.png" alt=""></a>
        </div>

        <div class="copyright">
            Copyright © futuremodels.id 2025
        </div>
    </footer>
</body>
</html>