<?php
session_start();
require_once '../utils/function.php';

requireAdmin();

$admin = getCurrentUser();

$totalUsers = TotalUser();
$totalModels = getActiveModels();
$pendingPosts = getTotalPendingPosts();
$totalPosts = TotalPost();
$newUsersToday = getNewUsersToday();
$userRegistrations = getUserRegistrationsLast7Days();
$postsData = getPostsLast7Days();

$last7Days = [];
for ($i = 6; $i >= 0; $i--) {
    $last7Days[] = date('Y-m-d', strtotime("-$i days"));
}

$userRegData = [];
$postsChartData = [];

foreach ($last7Days as $date) {
    $found = false;
    foreach ($userRegistrations as $reg) {
        if ($reg['date'] == $date) {
            $userRegData[] = (int)$reg['count'];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $userRegData[] = 0;
    }
    
    $found = false;
    foreach ($postsData as $post) {
        if ($post['date'] == $date) {
            $postsChartData[] = (int)$post['count'];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $postsChartData[] = 0;
    }
}

$dateLabels = [];
foreach ($last7Days as $date) {
    $dateLabels[] = date('M j', strtotime($date));
}

$notifications = getAllPosts('pending', 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Future Models</title>
    <link rel="stylesheet" href="../assets/css/adminPage.css">
</head>
<body>
    <?php displayFlashMessage(); ?>
    <div class="admin-container">
        <div class="sidebar">
            <div class="logo">
                <img src="../assets/image/future-white.png" alt="Future Models">
            </div>
            <ul class="nav-links">
                <li><a href="adminPage.php" class="active">Dashboard</a></li>
                <li><a href="manageposts.php">Content Management</a></li>
                <li><a href="manageusers.php">User Management</a></li>
                <li><a href="managemodels.php">Manage Models</a></li>
                <li><a href="managecategories.php">Manage Categories</a></li>
                <li><a href="postrequests.php">Post Requests</a></li>
                <li><a href="../index.php">View Site</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h1>Admin Dashboard</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['Name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p class="number"><?php echo $totalUsers; ?></p>
                    <div class="stat-trend">
                        <span class="trend-up">+<?php echo $newUsersToday; ?> today</span>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>Active Models</h3>
                    <p class="number"><?php echo $totalModels; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending Posts</h3>
                    <p class="number"><?php echo $pendingPosts; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Posts</h3>
                    <p class="number"><?php echo $totalPosts; ?></p>
                </div>
            </div>
            
            <div class="analytics-section">
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <h3>User Registrations (Last 7 Days)</h3>
                        <div class="chart-container">
                            <canvas id="userRegistrationsChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                    <div class="analytics-card">
                        <h3>Posts Created (Last 7 Days)</h3>
                        <div class="chart-container">
                            <canvas id="postsChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="notifications-panel">
                <div class="panel-header">
                    <h2>Pending Post Requests (<?php echo $pendingPosts; ?>)</h2>
                    <a href="postRequests.php" class="view-all-btn">View All</a>
                </div>
                <div class="notifications-list">
                    <?php if (empty($notifications)): ?>
                        <div class="no-notifications">
                            <p>No pending posts to review.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $post): 
                            $postMedia = getPostMedia($post['PostId']);
                            $mediaCount = count($postMedia);
                        ?>
                            <div class="notification-item">
                                <div class="notification-content">
                                    <div class="post-header">
                                        <h4><?php echo htmlspecialchars($post['Title']); ?></h4>
                                        <span class="post-date"><?php echo date('M j, Y g:i A', strtotime($post['UploadDate'])); ?></span>
                                    </div>
                                    <p class="post-author">By: <?php echo htmlspecialchars($post['AuthorName'] ?? 'Unknown User'); ?></p>
                                    
                                    <?php if (!empty($post['Description'])): ?>
                                        <p class="post-description">
                                            <?php echo htmlspecialchars(substr($post['Description'], 0, 200)); ?>
                                            <?php if (strlen($post['Description']) > 200): ?>
                                                <span class="read-more">...</span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($mediaCount > 0): ?>
                                        <div class="post-media">
                                            <?php 
                                            $previewMedia = array_slice($postMedia, 0, 3);
                                            foreach ($previewMedia as $media): 
                                            ?>
                                                <div class="post-media-item">
                                                    <?php if ($media['MediaType'] == 'image'): ?>
                                                        <img src="../<?php echo htmlspecialchars($media['FilePath']); ?>" 
                                                             alt="Post media" 
                                                             onclick="openMediaModal('<?php echo htmlspecialchars($media['FilePath']); ?>', 'image')">
                                                    <?php elseif ($media['MediaType'] == 'video'): ?>
                                                        <video controls>
                                                            <source src="../<?php echo htmlspecialchars($media['FilePath']); ?>" 
                                                                    type="video/<?php echo htmlspecialchars($media['FormatFile']); ?>">
                                                        </video>
                                                    <?php endif; ?>
                                                    <?php if ($mediaCount > 3 && $media === end($previewMedia)): ?>
                                                        <div class="media-count">+<?php echo $mediaCount - 3; ?> more</div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="notification-actions">
                                    <form method="POST" action="approve_post.php" style="display: inline;">
                                        <input type="hidden" name="post_id" value="<?php echo $post['PostId']; ?>">
                                        <button type="submit" class="btn-approve" onclick="return confirm('Approve this post?')">Approve</button>
                                    </form>
                                    <form method="POST" action="reject.php" style="display: inline;">
                                        <input type="hidden" name="post_id" value="<?php echo $post['PostId']; ?>">
                                        <div class="reject-form" style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="text" name="reason" placeholder="Rejection reason" required 
                                                   style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 150px;">
                                            <button type="submit" class="btn-reject" onclick="return confirm('Reject this post?')">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div id="mediaModal" class="media-modal" style="display: none;">
        <div class="media-modal-content">
            <span class="close-modal" onclick="closeMediaModal()">&times;</span>
            <img id="modalImage" src="" alt="" style="max-width: 90%; max-height: 90%;">
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const dateLabels = <?php echo json_encode($dateLabels); ?>;
        const userRegData = <?php echo json_encode($userRegData); ?>;
        const postsChartData = <?php echo json_encode($postsChartData); ?>;

        const userCtx = document.getElementById('userRegistrationsChart').getContext('2d');
        const userChart = new Chart(userCtx, {
            type: 'line',
            data: {
                labels: dateLabels,
                datasets: [{
                    label: 'User Registrations',
                    data: userRegData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        const postsCtx = document.getElementById('postsChart').getContext('2d');
        const postsChart = new Chart(postsCtx, {
            type: 'bar',
            data: {
                labels: dateLabels,
                datasets: [{
                    label: 'Posts Created',
                    data: postsChartData,
                    backgroundColor: '#10b981',
                    borderColor: '#10b981',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        function openMediaModal(src, type) {
            if (type === 'image') {
                document.getElementById('modalImage').src = '../' + src;
                document.getElementById('mediaModal').style.display = 'flex';
            }
        }
        
        function closeMediaModal() {
            document.getElementById('mediaModal').style.display = 'none';
        }
        
        document.getElementById('mediaModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMediaModal();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMediaModal();
            }
        });
    </script>
</body>
    <?php displayFlashMessage(); ?>
</html>