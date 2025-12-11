<?php
include '../utils/function.php';

if (!isUser()) {
    setFlashMessage('error', 'Please login to view your profile');
    redirect('login.php');
    exit();
}

$userData = null;
$userId = null;
$username = 'User';
$email = '';

if (isset($_SESSION['user_data']['UserID'])) {
    $userId = $_SESSION['user_data']['UserID'];
    $username = $_SESSION['user_data']['Name'] ?? 'User';
    $email = $_SESSION['user_data']['UserEmail'] ?? '';
} elseif (isset($_SESSION['user']['id'])) {
    $userId = $_SESSION['user']['id'];
    $username = $_SESSION['user']['name'] ?? 'User';
    $email = $_SESSION['user']['email'] ?? '';
} elseif (isset($_SESSION['user_data'])) {
    $userData = $_SESSION['user_data'];
    $userId = $userData['UserID'] ?? $userData['id'] ?? null;
    $username = $userData['Name'] ?? $userData['name'] ?? 'User';
    $email = $userData['UserEmail'] ?? $userData['email'] ?? '';
}

if (!$userId) {
    setFlashMessage('error', 'Session expired. Please login again.');
    redirect('login.php');
    exit();
}

$conn = getDBConnection();

$userQuery = "SELECT * FROM User WHERE UserID = ?";
$stmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$fullUserData = mysqli_fetch_assoc($userResult);

if (!$fullUserData) {
    mysqli_close($conn);
    setFlashMessage('error', 'User not found. Please login again.');
    redirect('login.php');
    exit();
}

$fullName = $fullUserData['Name'] ?? $username;
$email = $fullUserData['UserEmail'] ?? $email;

$userPosts = [];
$postsQuery = "SELECT p.*, 
               (SELECT COUNT(*) FROM PostMedia pm WHERE pm.PostId = p.PostId) as media_count
               FROM Post p 
               WHERE p.UserID = ? 
               ORDER BY p.UploadDate DESC";
$stmt = mysqli_prepare($conn, $postsQuery);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$postsResult = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($postsResult)) {
    $userPosts[] = $row;
}

$totalPosts = count($userPosts);

$approvedCount = 0;
$pendingCount = 0;
$rejectedCount = 0;

foreach ($userPosts as $post) {
    switch ($post['Status']) {
        case 'approved':
            $approvedCount++;
            break;
        case 'pending':
            $pendingCount++;
            break;
        case 'rejected':
            $rejectedCount++;
            break;
    }
}
$notifications = [];
$notificationsQuery = "SELECT * FROM Notification 
                      WHERE UserID = ? 
                      ORDER BY CreatedAt DESC 
                      LIMIT 20";
$stmt = mysqli_prepare($conn, $notificationsQuery);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$notificationsResult = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($notificationsResult)) {
    $notifications[] = $row;
}
if (!empty($notifications)) {
    $updateQuery = "UPDATE Notification SET Status = 'read' WHERE UserID = ? AND Status = 'unread'";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
}

$unreadCount = 0;
foreach ($notifications as $notification) {
    if ($notification['Status'] === 'unread') {
        $unreadCount++;
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($username); ?> - Profile</title>
    <link rel="stylesheet" href="../assets/css/userProfile.css">
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
            <li><a href="userProfile.php" class="active">PROFILE</a></li>
            <li><a href="../Admin/logout.php">LOGOUT</a></li>
        </ul>
    </nav>

    <section class="profile-section">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <svg width="150" height="150" viewBox="0 0 150 150" fill="none">
                        <circle cx="75" cy="75" r="75" fill="#e0e0e0" />
                        <circle cx="75" cy="60" r="25" fill="#999" />
                        <path d="M30 120C30 95 47.5 82.5 75 82.5C102.5 82.5 120 95 120 120" fill="#999" />
                    </svg>
                </div>
                <div class="profile-info">
                    <div class="profile-username">
                        <h1><?php echo htmlspecialchars($username); ?></h1>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notification-indicator" title="<?php echo $unreadCount; ?> unread notifications">
                                <?php echo $unreadCount; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-count"><?php echo $totalPosts; ?></span>
                            <span class="stat-label">posts</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-count"><?php echo $approvedCount; ?></span>
                            <span class="stat-label">approved</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-count"><?php echo $pendingCount; ?></span>
                            <span class="stat-label">pending</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-count"><?php echo $rejectedCount; ?></span>
                            <span class="stat-label">rejected</span>
                        </div>
                    </div>
                    <div class="profile-bio">
                        <p class="profile-name"><?php echo htmlspecialchars($fullName); ?></p>
                        <?php if ($email): ?>
                            <p class="profile-email"><?php echo htmlspecialchars($email); ?></p>
                        <?php endif; ?>
                        <?php if ($fullUserData['PhoneNumber']): ?>
                            <p class="profile-phone"><?php echo htmlspecialchars($fullUserData['PhoneNumber']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="profile-tabs">
                <button class="profile-tab active" onclick="switchTab('posts')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="13" y="3" width="7" height="7" />
                        <rect x="3" y="13" width="7" height="7" />
                        <rect x="13" y="13" width="7" height="7" />
                    </svg>
                    POSTS
                </button>
                <button class="profile-tab" onclick="switchTab('notifications')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                    </svg>
                    NOTIFICATIONS
                    <?php if ($unreadCount > 0): ?>
                        <span class="tab-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </button>
            </div>
            <div id="posts" class="tab-content active">
                <div class="profile-posts">
                    <?php if ($totalPosts > 0): ?>
                        <div class="posts-grid">
                            <?php foreach ($userPosts as $post):
                                $conn = getDBConnection();
                                $mediaQuery = "SELECT m.* FROM Media m 
                                              INNER JOIN PostMedia pm ON m.MediaId = pm.MediaId 
                                              WHERE pm.PostId = ? 
                                              ORDER BY pm.Position 
                                              LIMIT 1";
                                $stmt = mysqli_prepare($conn, $mediaQuery);
                                mysqli_stmt_bind_param($stmt, "i", $post['PostId']);
                                mysqli_stmt_execute($stmt);
                                $mediaResult = mysqli_stmt_get_result($stmt);
                                $media = mysqli_fetch_assoc($mediaResult);
                                mysqli_close($conn);
                            ?>
                                <div class="post-item">
                                    <div class="post-thumbnail">
                                        <?php if ($media && !empty($media['FilePath'])): ?>
                                            <?php if ($media['MediaType'] == 'image'): ?>
                                                <img src="../<?php echo htmlspecialchars($media['FilePath']); ?>"
                                                    alt="<?php echo htmlspecialchars($post['Title']); ?>">
                                            <?php elseif ($media['MediaType'] == 'video'): ?>
                                                <video>
                                                    <source src="../<?php echo htmlspecialchars($media['FilePath']); ?>"
                                                        type="video/<?php echo htmlspecialchars($media['FormatFile']); ?>">
                                                </video>
                                                <div class="video-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                                        <path d="M8 5v14l11-7z" />
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="placeholder-thumbnail">
                                                <span>No Media</span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($post['media_count'] > 1): ?>
                                            <div class="multiple-indicator">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="white" stroke-width="2" fill="none" />
                                                    <rect x="7" y="7" width="10" height="10" rx="1" fill="white" />
                                                </svg>
                                            </div>
                                        <?php endif; ?>

                                        <div class="post-overlay">
                                            <div class="post-stats">
                                                <span class="post-title"><?php echo htmlspecialchars($post['Title']); ?></span>
                                                <?php if (!empty($post['Description'])): ?>
                                                    <span class="post-description">
                                                        <?php echo htmlspecialchars(substr($post['Description'], 0, 60)) . (strlen($post['Description']) > 60 ? '...' : ''); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="post-date">
                                                    <?php echo date('M d, Y', strtotime($post['UploadDate'])); ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="post-status <?php echo strtolower($post['Status']); ?>">
                                            <?php echo ucfirst($post['Status']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-posts">
                            <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor" />
                                <path d="M21 15l-5-5L5 21" stroke-width="2" />
                            </svg>
                            <h3>No Posts Yet</h3>
                            <p>When you share photos and videos, they will appear on your profile.</p>
                            <a href="forum.php" class="btn-create-post">Create Your First Post</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div id="notifications" class="tab-content">
                <div class="notifications-section">
                    <?php if (!empty($notifications)): ?>
                        <div class="notifications-list">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item <?php echo $notification['Status'] === 'unread' ? 'unread' : ''; ?>">
                                    <div class="notification-content <?php echo strpos($notification['NotificationName'], 'rejected') !== false ? 'rejected' : ''; ?>">
                                        <?php echo htmlspecialchars($notification['NotificationName']); ?>
                                    </div>
                                    <div class="notification-date">
                                        <?php echo date('M j, Y g:i A', strtotime($notification['CreatedAt'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-notifications">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                            </svg>
                            <h3>No Notifications</h3>
                            <p>You don't have any notifications yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
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
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelectorAll('.profile-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    <?php if ($unreadCount > 0): ?>
            setTimeout(() => {
                switchTab('notifications');
            }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>