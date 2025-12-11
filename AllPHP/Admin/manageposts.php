<?php
session_start();
require_once '../utils/function.php';

requireAdmin();

$admin = getCurrentUser();
$approvedPosts = getPostsByStatus('approved');
$rejectedPosts = getPostsByStatus('rejected');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['post_id']) && isset($_POST['status'])) {
        $postId = (int)$_POST['post_id'];
        $status = $_POST['status'];
        $reason = $_POST['reason'] ?? '';
        if ($status === 'rejected' && empty($reason)) {
            setFlashMessage('error', 'Reason is required when rejecting a post');
            header('Location: managePosts.php');
            exit();
        }
        
        if (updatePostStatus($postId, $status)) {
            if ($status === 'rejected' && !empty($reason)) {
                $post = getPostById($postId);
                if ($post) {
                    createNotification(
                        $post['UserID'],
                        "Your post '{$post['Title']}' has been rejected. Reason: {$reason}"
                    );
                }
            }
            
            setFlashMessage('success', 'Post status updated successfully');
            header('Location: managePosts.php');
            exit();
        } else {
            setFlashMessage('error', 'Failed to update post status');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Management - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/adminPage.css">
    <link rel="stylesheet" href="../assets/css/managepost.css">
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
                <li><a href="manageposts.php" class="active">Content Management</a></li>
                <li><a href="manageusers.php">User Management</a></li>
                <li><a href="managemodels.php">Manage Models</a></li>
                <li><a href="managecategories.php">Manage Categories</a></li>
                <li><a href="postrequests.php">Post Requests</a></li>
                <li><a href="../index.php">View Site</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h1>Content Management</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['Name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="tabs">
                <button class="tab active" onclick="switchTab('approved')">Approved Posts (<?php echo count($approvedPosts); ?>)</button>
                <button class="tab" onclick="switchTab('rejected')">Rejected Posts (<?php echo count($rejectedPosts); ?>)</button>
            </div>
            <div id="approved" class="tab-content active">
                <?php if (empty($approvedPosts)): ?>
                    <div class="no-posts">
                        <h3>No approved posts</h3>
                        <p>There are no approved posts yet.</p>
                    </div>
                <?php else: ?>
                    <table class="posts-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Date Posted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvedPosts as $post): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($post['Title']); ?></strong>
                                        <?php if (!empty($post['Description'])): ?>
                                            <br><small><?php echo htmlspecialchars(substr($post['Description'], 0, 100)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($post['AuthorName'] ?? 'Unknown User'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($post['UploadDate'])); ?></td>
                                    <td>
                                        <span class="status-badge status-approved">Approved</span>
                                    </td>
                                    <td>
                                        <form method="POST" class="action-form" id="form-<?php echo $post['PostId']; ?>">
                                            <input type="hidden" name="post_id" value="<?php echo $post['PostId']; ?>">
                                            <select name="status" class="status-select" onchange="handleStatusChange(this, <?php echo $post['PostId']; ?>)">
                                                <option value="approved" selected>Approved</option>
                                                <option value="pending">Pending</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                            <input type="hidden" name="reason" id="reason-<?php echo $post['PostId']; ?>">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div id="rejected" class="tab-content">
                <?php if (empty($rejectedPosts)): ?>
                    <div class="no-posts">
                        <h3>No rejected posts</h3>
                        <p>There are no rejected posts yet.</p>
                    </div>
                <?php else: ?>
                    <table class="posts-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Date Posted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rejectedPosts as $post): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($post['Title']); ?></strong>
                                        <?php if (!empty($post['Description'])): ?>
                                            <br><small><?php echo htmlspecialchars(substr($post['Description'], 0, 100)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($post['AuthorName'] ?? 'Unknown User'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($post['UploadDate'])); ?></td>
                                    <td>
                                        <span class="status-badge status-rejected">Rejected</span>
                                    </td>
                                    <td>
                                        <form method="POST" class="action-form" id="form-<?php echo $post['PostId']; ?>">
                                            <input type="hidden" name="post_id" value="<?php echo $post['PostId']; ?>">
                                            <select name="status" class="status-select" onchange="handleStatusChange(this, <?php echo $post['PostId']; ?>)">
                                                <option value="approved">Approved</option>
                                                <option value="pending">Pending</option>
                                                <option value="rejected" selected>Rejected</option>
                                            </select>
                                            <input type="hidden" name="reason" id="reason-<?php echo $post['PostId']; ?>">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div id="rejectModal" class="reject-modal">
        <div class="reject-modal-content">
            <h3>Reject Post</h3>
            <p>Please provide a reason for rejecting this post:</p>
            <textarea id="rejectReason" placeholder="Enter rejection reason..."></textarea>
            <div class="reject-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
                <button type="button" class="btn-confirm-reject" onclick="confirmReject()">Reject Post</button>
            </div>
        </div>
    </div>

    <script>
        let currentPostId = null;
        let currentSelect = null;
        
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.getElementById(tabName).classList.add('active');
            
            event.target.classList.add('active');
        }
        
        function handleStatusChange(select, postId) {
            const status = select.value;
            
            if (status === 'rejected') {
                currentPostId = postId;
                currentSelect = select;
                document.getElementById('rejectModal').style.display = 'flex';
                document.getElementById('rejectReason').value = '';
                document.getElementById('rejectReason').focus();
            } else {
                document.getElementById('reason-' + postId).value = '';
                document.getElementById('form-' + postId).submit();
            }
        }
        
        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
            
            if (currentSelect) {
                const originalForm = currentSelect.closest('form');
                const originalStatus = originalForm.querySelector('input[name="post_id"]').value === currentPostId.toString() ? 
                    (originalForm.querySelector('option[selected]') ? 'approved' : 'rejected') : 'approved';
                currentSelect.value = originalStatus;
            }
            
            currentPostId = null;
            currentSelect = null;
        }
        
        function confirmReject() {
            const reason = document.getElementById('rejectReason').value.trim();
            
            if (!reason) {
                alert('Please provide a rejection reason.');
                document.getElementById('rejectReason').focus();
                return;
            }
            
            if (currentPostId) {
                document.getElementById('reason-' + currentPostId).value = reason;
                document.getElementById('form-' + currentPostId).submit();
            }
            
            closeRejectModal();
        }
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRejectModal();
            }
        });
        document.getElementById('rejectReason').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                confirmReject();
            }
        });
    </script>
</body>
</html>