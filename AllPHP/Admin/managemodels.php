<?php
session_start();
require_once '../utils/function.php';

requireAdmin();

$admin = getCurrentUser();
$models = getAllModels();
$categories = getAllCategories();
if (isset($_GET['delete_id'])) {
    $modelId = (int)$_GET['delete_id'];
    
    $conn = getDBConnection();
    $deleteViewQuery = "DELETE FROM ModelView WHERE ModelID = ?";
    $stmt = mysqli_prepare($conn, $deleteViewQuery);
    mysqli_stmt_bind_param($stmt, "i", $modelId);
    mysqli_stmt_execute($stmt);
    $deleteModelQuery = "DELETE FROM Model WHERE ModelID = ?";
    $stmt = mysqli_prepare($conn, $deleteModelQuery);
    mysqli_stmt_bind_param($stmt, "i", $modelId);
    
    if (mysqli_stmt_execute($stmt)) {
        setFlashMessage('success', 'Model deleted successfully');
    } else {
        setFlashMessage('error', 'Failed to delete model');
    }
    
    mysqli_close($conn);
    header('Location: manageModels.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Models - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/adminPage.css">
    <link rel="stylesheet" href="../assets/css/managemodel.css">
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
                <li><a href="managemodels.php" class="active">Manage Models</a></li>
                <li><a href="managecategories.php">Manage Categories</a></li>
                <li><a href="postrequests.php">Post Requests</a></li>
                <li><a href="../index.php">View Site</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h1>Model Management</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['Name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="models-section">
                <div class="section-header">
                    <h2>All Models (<?php echo count($models); ?>)</h2>
                    <a href="addmodel.php" class="add-model-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5V19M5 12H19" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Add New Model
                    </a>
                </div>
                
                <?php if (empty($models)): ?>
                    <div class="no-models">
                        <h3>No models found</h3>
                        <p>There are no models in the database yet.</p>
                        <a href="addmodel.php" class="add-model-btn" style="margin-top: 1rem;">Add Your First Model</a>
                    </div>
                <?php else: ?>
                    <table class="models-table">
                        <thead>
                            <tr>
                                <th>Model</th>
                                <th>Category</th>
                                <th>Measurements</th>
                                <th>Sizes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($models as $model): 
                                $conn = getDBConnection();
                                $statusQuery = "SELECT Status FROM ModelView WHERE ModelID = ?";
                                $stmt = mysqli_prepare($conn, $statusQuery);
                                mysqli_stmt_bind_param($stmt, "i", $model['ModelID']);
                                mysqli_stmt_execute($stmt);
                                $statusResult = mysqli_stmt_get_result($stmt);
                                $statusRow = mysqli_fetch_assoc($statusResult);
                                $modelStatus = $statusRow ? $statusRow['Status'] : 'inactive';
                                mysqli_close($conn);
                                
                                $initials = strtoupper(substr($model['ModelName'], 0, 2));
                            ?>
                                <tr>
                                    <td>
                                        <div class="model-info">
                                            <div class="model-image">
                                                <?php if (!empty($model['ModelImage'])): ?>
                                                    <img src="../uploads/models/<?php echo htmlspecialchars($model['ModelImage']); ?>" 
                                                         alt="<?php echo htmlspecialchars($model['ModelName']); ?>">
                                                <?php else: ?>
                                                    <div class="placeholder-image"><?php echo $initials; ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="model-details">
                                                <h4><?php echo htmlspecialchars($model['ModelName']); ?></h4>
                                                <p><?php echo htmlspecialchars($model['ModelEmail']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo !empty($model['CategoryName']) ? htmlspecialchars($model['CategoryName']) : '<span style="color: #9ca3af;">Uncategorized</span>'; ?>
                                    </td>
                                    <td>
                                        <div class="model-measurements">
                                            <?php if (!empty($model['Height'])): ?>
                                                <span class="measurement">H: <?php echo $model['Height']; ?>cm</span>
                                            <?php endif; ?>
                                            <?php if (!empty($model['Bust'])): ?>
                                                <span class="measurement">B: <?php echo $model['Bust']; ?>cm</span>
                                            <?php endif; ?>
                                            <?php if (!empty($model['Waist'])): ?>
                                                <span class="measurement">W: <?php echo $model['Waist']; ?>cm</span>
                                            <?php endif; ?>
                                            <?php if (!empty($model['Hips'])): ?>
                                                <span class="measurement">H: <?php echo $model['Hips']; ?>cm</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="model-measurements">
                                            <?php if (!empty($model['Top'])): ?>
                                                <span class="measurement">Top: <?php echo htmlspecialchars($model['Top']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($model['Pants'])): ?>
                                                <span class="measurement">Pants: <?php echo htmlspecialchars($model['Pants']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($model['Shoes'])): ?>
                                                <span class="measurement">Shoes: <?php echo htmlspecialchars($model['Shoes']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $modelStatus; ?>">
                                            <?php echo ucfirst($modelStatus); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="editmodel.php?id=<?php echo $model['ModelID']; ?>" class="btn-edit">Edit</a>
                                            <a href="manageModels.php?delete_id=<?php echo $model['ModelID']; ?>" 
                                               class="btn-delete" 
                                               onclick="return confirm('Are you sure you want to delete this model?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>