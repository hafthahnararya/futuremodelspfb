<?php
session_start();
require_once '../utils/function.php';

requireAdmin();

$admin = getCurrentUser();
$pendingPosts = getPostsByStatus('pending');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Requests - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/adminPage.css">
    <link rel="stylesheet" href="../assets/css/postrequest.css">
</head>
<body>
    <?php displayFlashMessage(); ?>
    <div class="admin-container">
        <div class="sidebar">
            <div class="logo">
                <img src="../assets/image/future-white.png" alt="Future Models">
            </div>
            <ul class="nav-links">
                <li><a href="adminPage.php">Dashboard</a></li>
                <li><a href="manageposts.php">Content Management</a></li>
                <li><a href="manageusers.php">User Management</a></li>
                <li><a href="managemodels.php">Manage Models</a></li>
                <li><a href="managecategories.php">Manage Categories</a></li>
                <li><a href="postrequests.php" class="active">Post Requests</a></li>
                <li><a href="../index.php">View Site</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h1>Post Requests</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['Name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="page-header">
                <div>
                    <h2 class="page-title">Pending Post Requests</h2>
                    <p class="post-meta"><?php echo count($pendingPosts); ?> posts awaiting review</p>
                </div>
            </div>
            
            <div class="posts-grid">
                <?php if (empty($pendingPosts)): ?>
                    <div class="no-posts">
                        <h3>No pending posts to review</h3>
                        <p>All posts have been reviewed and processed.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingPosts as $post): 
                        $postMedia = getPostMedia($post['PostId']);
                        $hasMultipleMedia = count($postMedia) > 1;
                    ?>
                        <div class="post-card">
                            <div class="post-card-header">
                                <div>
                                    <h3 class="post-title"><?php echo htmlspecialchars($post['Title']); ?></h3>
                                    <div class="post-meta">
                                        <span>By: <?php echo htmlspecialchars($post['AuthorName'] ?? 'Unknown User'); ?></span> • 
                                        <span>Posted: <?php echo date('M j, Y g:i A', strtotime($post['UploadDate'])); ?></span> • 
                                        <span class="status-badge status-pending">Pending</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <?php if (!empty($post['Description'])): ?>
                                    <p class="post-description"><?php echo htmlspecialchars($post['Description']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($postMedia)): ?>
                                    <div class="post-media-slider" data-post-id="<?php echo $post['PostId']; ?>">
                                        <?php foreach ($postMedia as $index => $media): ?>
                                            <div class="media-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                                <?php if ($media['MediaType'] == 'image'): ?>
                                                    <img src="../<?php echo htmlspecialchars($media['FilePath']); ?>" 
                                                         alt="Post media"
                                                         loading="lazy">
                                                <?php elseif ($media['MediaType'] == 'video'): ?>
                                                    <video controls>
                                                        <source src="../<?php echo htmlspecialchars($media['FilePath']); ?>" 
                                                                type="video/<?php echo htmlspecialchars($media['FormatFile']); ?>">
                                                    </video>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if ($hasMultipleMedia): ?>
                                            <div class="media-navigation">
                                                <button class="nav-btn prev-btn" onclick="changeMediaSlide(<?php echo $post['PostId']; ?>, -1)">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2"/>
                                                    </svg>
                                                </button>
                                                <span class="media-counter"><?php echo count($postMedia); ?> media</span>
                                                <button class="nav-btn next-btn" onclick="changeMediaSlide(<?php echo $post['PostId']; ?>, 1)">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                        <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            <div class="media-dots">
                                                <?php for ($i = 0; $i < count($postMedia); $i++): ?>
                                                    <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" 
                                                          onclick="goToMediaSlide(<?php echo $post['PostId']; ?>, <?php echo $i; ?>)"></span>
                                                <?php endfor; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="media-preview-controls">
                                            <button class="fullscreen-btn" onclick="toggleFullscreen(<?php echo $post['PostId']; ?>)">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
                                                </svg>
                                                Fullscreen
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="post-actions">
                                <form method="POST" action="approve_post.php">
                                    <input type="hidden" name="post_id" value="<?php echo $post['PostId']; ?>">
                                    <button type="submit" class="btn-approve" onclick="return confirm('Approve this post?')">Approve Post</button>
                                </form>
                                
                                <form method="POST" action="reject.php">
                                    <input type="hidden" name="post_id" value="<?php echo $post['PostId']; ?>">
                                    <div class="reject-form">
                                        <input type="text" name="reason" class="reject-input" 
                                               placeholder="Reason for rejection" required>
                                        <button type="submit" class="btn-reject" onclick="return confirm('Reject this post?')">Reject Post</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function changeMediaSlide(postId, direction) {
            const slider = document.querySelector(`.post-media-slider[data-post-id="${postId}"]`);
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
            const slider = document.querySelector(`.post-media-slider[data-post-id="${postId}"]`);
            if (!slider) return;
            
            const slides = slider.querySelectorAll('.media-slide');
            const dots = slider.querySelectorAll('.dot');
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            if (slides[index]) slides[index].classList.add('active');
            if (dots[index]) dots[index].classList.add('active');
        }

        function toggleFullscreen(postId) {
            const slider = document.querySelector(`.post-media-slider[data-post-id="${postId}"]`);
            const activeSlide = slider.querySelector('.media-slide.active');
            const mediaElement = activeSlide.querySelector('img, video');
            
            if (!document.fullscreenElement) {
                if (mediaElement.requestFullscreen) {
                    mediaElement.requestFullscreen();
                } else if (mediaElement.webkitRequestFullscreen) {
                    mediaElement.webkitRequestFullscreen();
                } else if (mediaElement.msRequestFullscreen) {
                    mediaElement.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                const activeSlider = document.querySelector('.post-media-slider:hover');
                if (activeSlider) {
                    const postId = activeSlider.dataset.postId;
                    changeMediaSlide(postId, -1);
                }
            } else if (e.key === 'ArrowRight') {
                const activeSlider = document.querySelector('.post-media-slider:hover');
                if (activeSlider) {
                    const postId = activeSlider.dataset.postId;
                    changeMediaSlide(postId, 1);
                }
            } else if (e.key === 'Escape' && document.fullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        });
        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });

        document.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                const activeSlider = document.querySelector('.post-media-slider:hover, .post-media-slider:active');
                if (activeSlider) {
                    const postId = activeSlider.dataset.postId;
                    if (diff > 0) {
                        changeMediaSlide(postId, 1);
                    } else {
                        changeMediaSlide(postId, -1);
                    }
                }
            }
        }

        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('msfullscreenchange', handleFullscreenChange);

        function handleFullscreenChange() {
            const posters = document.querySelectorAll('.post-media-slider');
            posters.forEach(poster => {
                const fullscreenBtn = poster.querySelector('.fullscreen-btn');
                if (document.fullscreenElement) {
                    fullscreenBtn.textContent = 'Exit Fullscreen';
                } else {
                    fullscreenBtn.textContent = 'Fullscreen';
                }
            });
        }
    </script>
</body>
</html>