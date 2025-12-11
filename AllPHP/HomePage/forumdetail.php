<?php
require_once '../utils/function.php';

$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId <= 0) {
    setFlashMessage('error', 'Post not found');
    redirect('forum.php');
    exit();
}

$post = getPostById($postId);

if (!$post) {
    setFlashMessage('error', 'Post not found');
    redirect('forum.php');
    exit();
}
if ($post['Status'] !== 'approved') {
    $user = getCurrentUser();
    $isAdmin = isAdmin();
    $isAuthor = ($user && $user['UserID'] == $post['UserID']);
    
    if (!$isAdmin && !$isAuthor) {
        setFlashMessage('error', 'This post is not available');
        redirect('forum.php');
        exit();
    }
}

$media = getPostMedia($postId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['Title']); ?> - Future Models Forum</title>
    <link rel="stylesheet" href="../assets/css/forum.css">
    <link rel="stylesheet" href="../assets/css/forumdetail.css">
    <style>
        .forum-detail-section {
            padding: 2rem 0;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .forum-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .forum-detail-header {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        @media (min-width: 768px) {
            .forum-detail-header {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .forum-media-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .forum-media-slider {
            position: relative;
            width: 100%;
            height: 500px;
        }
        
        .media-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }
        
        .media-slide.active {
            opacity: 1;
            z-index: 1;
        }
        
        .media-slide img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .media-slide video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .media-navigation {
            position: absolute;
            bottom: 1rem;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            z-index: 10;
        }
        
        .nav-btn {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .nav-btn:hover {
            background: white;
            transform: scale(1.1);
        }
        
        .nav-btn svg {
            width: 20px;
            height: 20px;
        }
        
        .media-counter {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .media-dots {
            position: absolute;
            bottom: 1rem;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            z-index: 10;
        }
        
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .dot.active {
            background: white;
            transform: scale(1.2);
        }
        
        .forum-basic-info {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .forum-basic-info h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 1rem 0;
            line-height: 1.2;
        }
        
        .forum-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .meta-item svg {
            width: 16px;
            height: 16px;
        }
        
        .author-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .author-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .author-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .author-details h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
        }
        
        .author-details p {
            margin: 0.25rem 0 0 0;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .post-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .forum-detail-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .forum-description {
            margin-bottom: 2rem;
        }
        
        .forum-description h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 1rem 0;
        }
        
        .forum-description p {
            color: #374151;
            line-height: 1.6;
            margin: 0;
            white-space: pre-wrap;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn-edit {
            background: #10b981;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-edit:hover {
            background: #059669;
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-delete:hover {
            background: #dc2626;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #3b82f6;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s ease;
            margin-top: 2rem;
        }
        
        .back-button:hover {
            background: #2563eb;
        }
        
        .back-button svg {
            width: 20px;
            height: 20px;
        }
        
        .no-media {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 400px;
            background: #f3f4f6;
            border-radius: 8px;
            color: #6b7280;
            font-size: 1.125rem;
        }
        
        @media (max-width: 768px) {
            .forum-media-slider {
                height: 400px;
            }
            
            .forum-basic-info h1 {
                font-size: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
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
            <li><a href="model.php">MODELS</a></li>
            <li><a href="forum.php" class="active">FORUM</a></li>
            <?php if (isUserLoggedIn()): ?>
                <li><a href="userProfile.php">PROFILE</a></li>
                <li><a href="../Admin/logout.php">LOGOUT</a></li>
            <?php else: ?>
                <li><a href="login.php">LOGIN</a></li>
                <li><a href="signUp.php">SIGN UP</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <section class="forum-detail-section">
        <div class="forum-detail-container">
            <div class="forum-detail-header">
                <div class="forum-media-container">
                    <?php if (!empty($media)): ?>
                        <div class="forum-media-slider" data-post-id="<?php echo $postId; ?>">
                            <?php foreach ($media as $index => $mediaItem): ?>
                                <div class="media-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                    <?php if ($mediaItem['MediaType'] == 'image'): ?>
                                        <img src="../<?php echo htmlspecialchars($mediaItem['FilePath']); ?>"
                                             alt="<?php echo htmlspecialchars($post['Title']); ?>"
                                             loading="lazy">
                                    <?php elseif ($mediaItem['MediaType'] == 'video'): ?>
                                        <video controls>
                                            <source src="../<?php echo htmlspecialchars($mediaItem['FilePath']); ?>"
                                                    type="video/<?php echo htmlspecialchars($mediaItem['FormatFile']); ?>">
                                            Your browser does not support the video tag.
                                        </video>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($media) > 1): ?>
                                <div class="media-navigation">
                                    <button class="nav-btn prev-btn" onclick="changeMediaSlide(<?php echo $postId; ?>, -1)">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                    </button>
                                    <span class="media-counter"><?php echo count($media); ?> media</span>
                                    <button class="nav-btn next-btn" onclick="changeMediaSlide(<?php echo $postId; ?>, 1)">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="media-dots">
                                    <?php for ($i = 0; $i < count($media); $i++): ?>
                                        <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" 
                                              onclick="goToMediaSlide(<?php echo $postId; ?>, <?php echo $i; ?>)"></span>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-media">
                            <p>No media available</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="forum-basic-info">
                    <h1><?php echo htmlspecialchars($post['Title']); ?></h1>
                    
                    <div class="forum-meta">
                        <div class="meta-item">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php echo date('F j, Y', strtotime($post['UploadDate'])); ?>
                        </div>
                        
                        <div class="meta-item">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="post-status status-<?php echo $post['Status']; ?>">
                                <?php echo ucfirst($post['Status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="author-info">
                        <div class="author-avatar">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                <circle cx="24" cy="24" r="24" fill="#e0e0e0" />
                                <circle cx="24" cy="18" r="6" fill="#999" />
                                <path d="M8 38c0-6 4-10 12-10s12 4 12 10" fill="#999" />
                            </svg>
                        </div>
                        <div class="author-details">
                            <h3><?php echo htmlspecialchars($post['AuthorName'] ?? 'Anonymous'); ?></h3>
                            <p><?php echo htmlspecialchars($post['AuthorEmail'] ?? ''); ?></p>
                        </div>
                    </div>
                    
                    <?php if (isAdmin() || (isUserLoggedIn() && $post['UserID'] == $_SESSION['user_data']['id'])): ?>
                        <div class="action-buttons">
                            <?php if (isAdmin()): ?>
                                <?php if ($post['Status'] == 'pending'): ?>
                                    <a href="../Admin/approve_post.php?id=<?php echo $postId; ?>" 
                                       class="btn-edit">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path d="M5 13l4 4L19 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Approve Post
                                    </a>
                                    <a href="../Admin/reject.php?id=<?php echo $postId; ?>" 
                                       class="btn-delete"
                                       onclick="return confirm('Are you sure you want to reject this post?')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 18L18 6M6 6l12 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Reject Post
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if (isUserLoggedIn() && $post['UserID'] == $_SESSION['user_data']['id']): ?>
                                <a href="edit_post.php?id=<?php echo $postId; ?>" class="btn-edit">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Edit Post
                                </a>
                                <a href="delete_post.php?id=<?php echo $postId; ?>" 
                                   class="btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this post?')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Delete Post
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="forum-detail-content">
                <div class="forum-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($post['Description'] ?? 'No description provided.')); ?></p>
                </div>
                
                <a href="forum.php" class="back-button">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Back to Forum
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
        function changeMediaSlide(postId, direction) {
            const slider = document.querySelector(`.forum-media-slider[data-post-id="${postId}"]`);
            if (!slider) return;
            
            const slides = slider.querySelectorAll('.media-slide');
            const dots = slider.querySelectorAll('.dot');
            const activeSlide = slider.querySelector('.media-slide.active');
            const activeDot = slider.querySelector('.dot.active');
            
            let currentIndex = parseInt(activeSlide.dataset.index);
            let newIndex = currentIndex + direction;
            
            if (newIndex < 0) newIndex = slides.length - 1;
            if (newIndex >= slides.length) newIndex = 0;
            
            activeSlide.classList.remove('active');
            slides[newIndex].classList.add('active');
            
            if (activeDot) activeDot.classList.remove('active');
            if (dots[newIndex]) dots[newIndex].classList.add('active');
        }

        function goToMediaSlide(postId, index) {
            const slider = document.querySelector(`.forum-media-slider[data-post-id="${postId}"]`);
            if (!slider) return;
            
            const slides = slider.querySelectorAll('.media-slide');
            const dots = slider.querySelectorAll('.dot');
            
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            if (slides[index]) slides[index].classList.add('active');
            if (dots[index]) dots[index].classList.add('active');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                const activeSlider = document.querySelector('.forum-media-slider:hover');
                if (activeSlider) {
                    const postId = activeSlider.dataset.postId;
                    changeMediaSlide(postId, -1);
                }
            } else if (e.key === 'ArrowRight') {
                const activeSlider = document.querySelector('.forum-media-slider:hover');
                if (activeSlider) {
                    const postId = activeSlider.dataset.postId;
                    changeMediaSlide(postId, 1);
                }
            }
        });
        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', function(e) {
            const mediaSlider = e.target.closest('.forum-media-slider');
            if (mediaSlider) {
                touchStartX = e.changedTouches[0].screenX;
            }
        });

        document.addEventListener('touchend', function(e) {
            const mediaSlider = e.target.closest('.forum-media-slider');
            if (mediaSlider) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe(mediaSlider);
            }
        });

        function handleSwipe(slider) {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                const postId = slider.dataset.postId;
                if (diff > 0) {
                    changeMediaSlide(postId, 1);
                } else {
                    changeMediaSlide(postId, -1);
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const video = entry.target.querySelector('video');
                    if (video) {
                        if (entry.isIntersecting) {
                            video.play().catch(e => console.log('Auto-play prevented:', e));
                        } else {
                            video.pause();
                        }
                    }
                });
            }, { threshold: 0.5 });

            document.querySelectorAll('.media-slide').forEach(slide => {
                observer.observe(slide);
            });
        });

        if (window.location.pathname.includes('forum.php') && !window.location.search.includes('id=')) {
            document.addEventListener('DOMContentLoaded', function() {
                const forumCards = document.querySelectorAll('.model-card');
                forumCards.forEach(card => {
                    card.style.cursor = 'pointer';
                    card.addEventListener('click', function(e) {
                        if (e.target.closest('.nav-btn') || 
                            e.target.closest('.media-dots') ||
                            e.target.tagName === 'A' || 
                            e.target.tagName === 'BUTTON') {
                            return;
                        }
                        
                        const postId = this.querySelector('.post-media-slider')?.dataset.postId;
                        if (postId) {
                            window.location.href = `forumdetail.php?id=${postId}`;
                        }
                    });
                });
            });
        }
    </script>
</body>
</html>