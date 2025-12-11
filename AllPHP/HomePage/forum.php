<?php
include '../utils/function.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Future Models - Forum</title>
    <link rel="stylesheet" href="../assets/css/forum.css">
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

    <?php if (isUserLoggedIn()): ?>
        <section class="welcome-section">
            <div class="welcome-container">
                <?php
                $displayName = 'User';
                if (isset($_SESSION['user_data']['name'])) {
                    $displayName = $_SESSION['user_data']['name'];
                } elseif (isset($_SESSION['name'])) {
                    $displayName = $_SESSION['name'];
                } elseif (isset($_SESSION['username'])) {
                    $displayName = $_SESSION['username'];
                }
                ?>
                <h2>Welcome, <?php echo htmlspecialchars($displayName); ?></h2>
                <button onclick="openAddPostModal()" class="btn-add-post">
                    <img src="../assets/image/Plus.png" alt="Add Post">
                </button>
            </div>
        </section>
    <?php endif; ?>

    <section class="models-section">
    <div class="models-grid">
        <?php
        $posts = getAllPosts('approved', 20, 0);

        if (!empty($posts)):
            foreach ($posts as $post):
                $conn = getDBConnection();
                $mediaQuery = "SELECT m.* FROM Media m 
                              INNER JOIN PostMedia pm ON m.MediaId = pm.MediaId 
                              WHERE pm.PostId = ? 
                              ORDER BY pm.Position";
                $stmt = mysqli_prepare($conn, $mediaQuery);
                mysqli_stmt_bind_param($stmt, "i", $post['PostId']);
                mysqli_stmt_execute($stmt);
                $mediaResult = mysqli_stmt_get_result($stmt);
                $allMedia = [];
                while ($media = mysqli_fetch_assoc($mediaResult)) {
                    $allMedia[] = $media;
                }
                mysqli_close($conn);

                $date = date('d.m.Y', strtotime($post['UploadDate']));
                $hasMultipleMedia = count($allMedia) > 1;
        ?>
                <div class="model-card">
                    <div class="model-image">
                        <?php if (!empty($allMedia)): ?>
                            <div class="post-media-slider" data-post-id="<?php echo $post['PostId']; ?>">
                                <?php foreach ($allMedia as $index => $media): ?>
                                    <div class="media-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                        <?php if ($media['MediaType'] == 'image'): ?>
                                            <img src="../<?php echo htmlspecialchars($media['FilePath']); ?>"
                                                alt="<?php echo htmlspecialchars($post['Title']); ?>"
                                                loading="lazy">
                                        <?php elseif ($media['MediaType'] == 'video'): ?>
                                            <video controls>
                                                <source src="../<?php echo htmlspecialchars($media['FilePath']); ?>"
                                                    type="video/<?php echo htmlspecialchars($media['FormatFile']); ?>">
                                                Your browser does not support the video tag.
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
                                        <span class="media-counter"><?php echo count($allMedia); ?> media</span>
                                        <button class="nav-btn next-btn" onclick="changeMediaSlide(<?php echo $post['PostId']; ?>, 1)">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <div class="media-dots">
                                        <?php for ($i = 0; $i < count($allMedia); $i++): ?>
                                            <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" 
                                                  onclick="goToMediaSlide(<?php echo $post['PostId']; ?>, <?php echo $i; ?>)"></span>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="placeholder-media">
                                <span>No Media</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="model-info">
                        <h3><?php echo htmlspecialchars($post['Title']); ?></h3>
                        <p class="model-date"><?php echo $date; ?></p>
                        <?php if (!empty($post['Description'])): ?>
                            <p class="model-description">
                                <?php echo htmlspecialchars(substr($post['Description'], 0, 100)) . (strlen($post['Description']) > 100 ? '...' : ''); ?>
                            </p>
                        <?php endif; ?>
                        <div class="model-tags">
                            <span>Post</span>
                            <?php if (!empty($post['AuthorName'])): ?>
                                <span><?php echo htmlspecialchars($post['AuthorName']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php
            endforeach;
        else:
            ?>
            <div class="no-posts-message">
                <h3>No posts available yet</h3>
                <p>Check back soon for updates!</p>
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
<?php if (isUserLoggedIn()):
    $username = 'User';
    if (isset($_SESSION['user_data']['name'])) {
        $username = $_SESSION['user_data']['name'];
    } elseif (isset($_SESSION['name'])) {
        $username = $_SESSION['name'];
    } elseif (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    }
?>
    <div id="addPostModal" class="modal">
        <div class="modal-content">
            <div id="step1" class="step active">
                <div class="modal-header">
                    <button class="back-btn" onclick="closeAddPostModal()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <button id="nextBtn" class="next-btn disabled" disabled>Next</button>
                </div>

                <div class="upload-area">
                    <div id="imagePreview" class="image-preview">
                        <div class="upload-placeholder">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                                <path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M17 8L12 3L7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M12 3V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p>Select Image or Video</p>
                        </div>
                    </div>
                    <button type="button" class="select-btn" onclick="document.getElementById('fileInput').click()">
                        Select from computer
                    </button>
                    <input type="file" id="fileInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,video/mp4,video/mov,video/avi,video/webm" multiple style="display: none;">

                    <div id="imageControls" class="media-controls-container" style="display: none;">
                        <div class="resize-info">
                            <span>Drag handles to crop/resize: → Width | ↓ Height | ↘ Both</span>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" id="removeCurrentBtn" onclick="removeCurrentMedia()" class="remove-btn" style="display: none;">
                                Remove Current
                            </button>
                            <button type="button" onclick="resetImageSize()" class="remove-btn" style="background: #666;">
                                Reset Size
                            </button>
                            <button type="button" id="removeAllBtn" onclick="removeAllMedia()" class="remove-btn">
                                Remove All
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="step2" class="step">
                <div class="modal-header">
                    <button class="back-btn" onclick="goToStep1()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <button type="submit" form="postForm" class="post-btn">Post</button>
                </div>

                <form id="postForm" method="POST" action="add_post.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create_post">
                    <input type="file" name="post_media[]" id="hiddenFileInput" multiple style="display: none;">

                    <div class="post-content">
                        <div class="post-preview">
                        </div>

                        <div class="post-details">
                            <div class="user-info">
                                <div class="avatar">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                                        <circle cx="20" cy="20" r="20" fill="#e0e0e0" />
                                        <circle cx="20" cy="16" r="6" fill="#999" />
                                        <path d="M8 32C8 26 12 22 20 22C28 22 32 26 32 32" fill="#999" />
                                    </svg>
                                </div>
                                <span><?php echo htmlspecialchars($username); ?></span>
                            </div>

                            <div class="form-group">
                                <input type="text"
                                    name="title"
                                    id="title"
                                    placeholder="Type title here..."
                                    maxlength="100"
                                    required>
                            </div>

                            <div class="form-group">
                                <textarea name="description"
                                    id="description"
                                    placeholder="Type here..."
                                    maxlength="1000"
                                    rows="4"></textarea>
                                <div class="char-count">
                                    <span id="charCount">0</span>/1000
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
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
</script>
    <script src="../assets/js/addPost.js"></script>
    <script>
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
</script>
</body>

</html>