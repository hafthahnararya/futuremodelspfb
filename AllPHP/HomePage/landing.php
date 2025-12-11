<?php
require_once '../utils/function.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Future Models - Model Management Agency</title>
    <link rel="stylesheet" href="../assets/css/landing.css">
</head>
<body>
    <section class="hero">
        <video class="hero-video" autoplay muted loop playsinline>
            <source src="../assets/video/background.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <nav>
            <div class="logo">
                <img src="../assets/image/future-white.png" alt="Future Models">
            </div>
            <ul class="nav-links">
                <li><a href="about.php">ABOUT</a></li>
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
    </section>

    <section class="about-section" id="about">
        <div class="about-hero">
            <div></div>
            <div class="about-content">
                <h1>THE FUTURE MOVES FAST — WE MOVE FIRST. WE ARE FUTURE.</h1>
                <a href="about.php" class="discover-link">→ Discover More</a>
            </div>
        </div>

        <div class="about-details">
            <div class="about-image"></div>
            <img src="../assets/image/models.jpg"  alt="" class="gambar">
            <div class="about-text">
                <h2>Jakarta-based model management agency specializing in distinct array of talents.</h2>
                <p>We represent and develop the exclusive few of selected most sought after unique looks and personalities that the industry unequivocally demands.</p>
            </div>
        </div>
    </section>

    <section class="featured-section" id="models">
        <h2>FEATURED</h2>
        <div class="featured-grid">
            <?php
            $models = getAllModels(null, 6, 0);
            if (!empty($models)):
                foreach ($models as $model): 
                    $imagePath = '';
                    if (!empty($model['ModelImage'])) {
                        if (strpos($model['ModelImage'], 'uploads/') === 0) {
                            $imagePath = '../' . $model['ModelImage'];
                        } else {
                            $imagePath = '../uploads/models/' . basename($model['ModelImage']);
                        }
                    }
            ?>
                <div class="featured-item">
                    <?php if (!empty($model['ModelImage'])): ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             alt="<?php echo htmlspecialchars($model['ModelName']); ?>"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'placeholder-image\'><span>No Image Available</span></div>';">
                    <?php else: ?>
                        <div class="placeholder-image">
                            <span>No Image Available</span>
                        </div>
                    <?php endif; ?>
                    <div class="featured-overlay">
                        <h3><?php echo htmlspecialchars($model['ModelName']); ?></h3>
                        <p><?php echo htmlspecialchars($model['CategoryName'] ?? 'Uncategorized'); ?></p>
                    </div>
                </div>
            <?php 
                endforeach;
            else:
            ?>
                <div class="no-models-message">
                    <p>No models available at the moment. Check back soon!</p>
                </div>
            <?php endif; ?>
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
            <li><a href="signUp.php">SIGN UP</a></li>
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
</body>
</html>