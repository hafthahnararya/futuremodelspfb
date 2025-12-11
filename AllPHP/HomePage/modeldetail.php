<?php
require_once '../utils/function.php';

$modelId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($modelId <= 0) {
    setFlashMessage('error', 'Model not found');
    redirect('model.php');
    exit();
}

$model = getModelById($modelId);

if (!$model) {
    setFlashMessage('error', 'Model not found');
    redirect('model.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($model['ModelName']); ?> - Future Models</title>
    <link rel="stylesheet" href="../assets/css/model.css">
    <link rel="stylesheet" href="../assets/css/modeldetail.css">
</head>
<body>
    <?php displayFlashMessage(); ?>
    <nav>
        <div class="logo">
            <a href="../index.php">
                <img src="../assets/image/Future.png" alt="Future Models">
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="about.php">ABOUT</a></li>
            <li><a href="model.php" class="active">MODELS</a></li>
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
    
    <section class="model-detail-section">
        <div class="model-detail-container">
            <div class="model-detail-header">
                <div class="model-image-container">
                    <?php if (!empty($model['ModelImage'])): ?>
                        <img src="../uploads/models/<?php echo htmlspecialchars($model['ModelImage']); ?>" 
                             alt="<?php echo htmlspecialchars($model['ModelName']); ?>">
                    <?php else: ?>
                        <div class="placeholder-detail-image">
                            <?php echo strtoupper(substr($model['ModelName'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="model-basic-info">
                    <h1><?php echo htmlspecialchars($model['ModelName']); ?></h1>
                    <?php if (!empty($model['CategoryName'])): ?>
                        <span class="model-category-badge"><?php echo htmlspecialchars($model['CategoryName']); ?></span>
                    <?php endif; ?>
                    
                    <div class="model-contact">
                        <?php if (!empty($model['ModelEmail'])): ?>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M20 4H4C2.9 4 2.01 4.9 2.01 6L2 18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 8L12 13L4 8V6L12 11L20 6V8Z" fill="#666"/>
                                    </svg>
                                </div>
                                <span><?php echo htmlspecialchars($model['ModelEmail']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2ZM12 11.5C10.62 11.5 9.5 10.38 9.5 9C9.5 7.62 10.62 6.5 12 6.5C13.38 6.5 14.5 7.62 14.5 9C14.5 10.38 13.38 11.5 12 11.5Z" fill="#666"/>
                                </svg>
                            </div>
                            <span>Available for bookings</span>
                        </div>
                    </div>

                    <?php if (isAdmin()): ?>
                        <div class="action-buttons">
                            <a href="../Admin/editmodel.php?id=<?php echo $modelId; ?>" class="edit-button">Edit Model</a>
                            <a href="../Admin/deletemodel.php?id=<?php echo $modelId; ?>" 
                               class="delete-button" 
                               onclick="return confirm('Are you sure you want to delete this model?')">Delete Model</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="model-detail-content">
                <div class="detail-grid">
                    <?php if (!empty($model['Height'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Height</span>
                            <span class="detail-value cm"><?php echo $model['Height']; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($model['Bust'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Bust</span>
                            <span class="detail-value cm"><?php echo $model['Bust']; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($model['Waist'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Waist</span>
                            <span class="detail-value cm"><?php echo $model['Waist']; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($model['Hips'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Hips</span>
                            <span class="detail-value cm"><?php echo $model['Hips']; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($model['Top'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Top Size</span>
                            <span class="detail-value"><?php echo htmlspecialchars($model['Top']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($model['Pants'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Pants Size</span>
                            <span class="detail-value"><?php echo htmlspecialchars($model['Pants']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($model['Shoes'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Shoes Size</span>
                            <span class="detail-value"><?php echo htmlspecialchars($model['Shoes']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($model['Description'])): ?>
                    <div class="model-description">
                        <h3>About <?php echo htmlspecialchars($model['ModelName']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($model['Description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <a href="model.php" class="back-button">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back to Models
                </a>
            </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.pathname.includes('model.php') && !window.location.search.includes('id=')) {
                const modelCards = document.querySelectorAll('.model-card');
                modelCards.forEach(card => {
                    card.style.cursor = 'pointer';
                    card.addEventListener('click', function() {
                        const modelId = this.getAttribute('data-model-id');
                        if (modelId) {
                            window.location.href = `model_detail.php?id=${modelId}`;
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>