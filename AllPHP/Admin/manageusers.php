<?php
require_once '../utils/function.php';

requireAdmin();

$admin = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['status'])) {
    $userId = (int)$_POST['user_id'];
    $status = $_POST['status'];
    $reason = $_POST['reason'] ?? '';
    
    if (updateUserStatus($userId, $status)) {
        if ($status === 'inactive' && !empty($reason)) {
            $user = getUserById($userId);
            $notificationMessage = "Your account has been deactivated. Reason: {$reason}";
            createNotification($userId, $notificationMessage);
        } else if ($status === 'active') {
            $user = getUserById($userId);
            $notificationMessage = "Your account has been reactivated.";
            createNotification($userId, $notificationMessage);
        }
        
        setFlashMessage('success', 'User status updated successfully');
        header('Location: manageUsers.php');
        exit();
    } else {
        setFlashMessage('error', 'Failed to update user status');
    }
}
$users = getAllUsers();
$searchTerm = $_GET['search'] ?? '';
if ($searchTerm) {
    $users = searchUsers($searchTerm);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/adminPage.css">
    <link rel="stylesheet" href="../assets/css/manageuser.css">
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
                <li><a href="manageusers.php" class="active">User Management</a></li>
                <li><a href="managemodels.php">Manage Models</a></li>
                <li><a href="managecategories.php">Manage Categories</a></li>
                <li><a href="postrequests.php">Post Requests</a></li>
                <li><a href="../index.php">View Site</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h1>User Management</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['Name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="users-section">
                <div class="section-header">
                    <h2>All Users (<?php echo count($users); ?>)</h2>
                    <form method="GET" class="search-container">
                        <input type="text" 
                               name="search" 
                               class="search-input" 
                               placeholder="Search by name, username, or email..."
                               value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button type="submit" class="search-btn">Search</button>
                        <?php if ($searchTerm): ?>
                            <a href="manageUsers.php" class="search-btn" style="background: #6b7280;">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <?php if (empty($users)): ?>
                    <div class="no-users">
                        <h3>No users found</h3>
                        <p><?php echo $searchTerm ? 'No users match your search.' : 'There are no registered users yet.'; ?></p>
                    </div>
                <?php else: ?>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Posts</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): 
                                $postCount = getUserPostCount($user['UserID']);
                                $initials = strtoupper(substr($user['Name'], 0, 2));
                            ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo $initials; ?>
                                            </div>
                                            <div class="user-details">
                                                <h4><?php echo htmlspecialchars($user['Name']); ?></h4>
                                                <p>@<?php echo htmlspecialchars($user['UserName']); ?></p>
                                                <p>Joined: <?php echo date('M j, Y', strtotime($user['CreatedAt'])); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['UserEmail']); ?></td>
                                    <td>
                                        <span style="font-weight: 600;"><?php echo $postCount; ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($user['Status']); ?>">
                                            <?php echo ucfirst($user['Status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="action-form" id="user-form-<?php echo $user['UserID']; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                            <select name="status" class="status-select" onchange="handleUserStatusChange(this, <?php echo $user['UserID']; ?>)">
                                                <option value="active" <?php echo $user['Status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo $user['Status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                            <input type="hidden" name="reason" id="reason-<?php echo $user['UserID']; ?>">
                                            <button type="submit" class="btn-update" style="display: none;">Update</button>
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

    <!-- Deactivate Reason Modal -->
    <div id="deactivateModal" class="deactivate-modal">
        <div class="deactivate-modal-content">
            <h3>Deactivate User Account</h3>
            <p>Please provide a reason for deactivating this user's account:</p>
            <textarea id="deactivateReason" placeholder="Enter deactivation reason..."></textarea>
            <div class="deactivate-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeDeactivateModal()">Cancel</button>
                <button type="button" class="btn-confirm-deactivate" onclick="confirmDeactivate()">Deactivate Account</button>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;
        let currentSelect = null;
        
        function handleUserStatusChange(select, userId) {
            const status = select.value;
            
            if (status === 'inactive') {
                currentUserId = userId;
                currentSelect = select;
                
                document.getElementById('deactivateModal').style.display = 'flex';
                document.getElementById('deactivateReason').value = '';
                document.getElementById('deactivateReason').focus();
            } else {
                document.getElementById('reason-' + userId).value = '';
                document.getElementById('user-form-' + userId).submit();
            }
        }
        
        function closeDeactivateModal() {
            document.getElementById('deactivateModal').style.display = 'none';
            
            if (currentSelect) {
                const form = currentSelect.closest('form');
                const originalStatus = form.querySelector('input[name="user_id"]').value === currentUserId.toString() ? 
                    (form.querySelector('option[selected]') ? 'active' : 'inactive') : 'active';
                currentSelect.value = originalStatus;
            }
            
            currentUserId = null;
            currentSelect = null;
        }
        
        function confirmDeactivate() {
            const reason = document.getElementById('deactivateReason').value.trim();
            
            if (!reason) {
                alert('Please provide a deactivation reason.');
                document.getElementById('deactivateReason').focus();
                return;
            }
            
            if (currentUserId) {
                document.getElementById('reason-' + currentUserId).value = reason;
                document.getElementById('user-form-' + currentUserId).submit();
            }
            
            closeDeactivateModal();
        }
        
        document.getElementById('deactivateModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeactivateModal();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeactivateModal();
            }
        });
        
        document.getElementById('deactivateReason').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                confirmDeactivate();
            }
        });
    </script>
</body>
</html>