<?php
session_start();
require_once '../utils/function.php';

requireAdmin();

$admin = getCurrentUser();
$categories = getAllCategories();

if (isset($_GET['delete_id'])) {
    $categoryId = (int)$_GET['delete_id'];
    
    $conn = getDBConnection();
    $checkQuery = "SELECT COUNT(*) as model_count FROM Model WHERE CategoryID = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "i", $categoryId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['model_count'] > 0) {
        setFlashMessage('error', 'Cannot delete category with associated models. Please reassign or delete models first.');
    } else {
        $deleteQuery = "DELETE FROM Category WHERE CategoryID = ?";
        $stmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        
        if (mysqli_stmt_execute($stmt)) {
            setFlashMessage('success', 'Category deleted successfully');
        } else {
            setFlashMessage('error', 'Failed to delete category');
        }
    }
    
    mysqli_close($conn);
    header('Location: managecategories.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $categoryId = (int)$_POST['category_id'];
    $categoryName = sanitizeInput($_POST['category_name']);
    
    if (!empty($categoryName)) {
        $conn = getDBConnection();
        $updateQuery = "UPDATE Category SET CategoryName = ? WHERE CategoryID = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "si", $categoryName, $categoryId);
        
        if (mysqli_stmt_execute($stmt)) {
            setFlashMessage('success', 'Category updated successfully');
        } else {
            setFlashMessage('error', 'Failed to update category');
        }
        
        mysqli_close($conn);
        header('Location: managecategories.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/adminPage.css">
    <link rel="stylesheet" href="../assets/css/managemodel.css">
    <link rel="stylesheet" href="../assets/css/managecategories.css">
    
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
                <li><a href="managecategories.php" class="active">Manage Categories</a></li>
                <li><a href="postrequests.php">Post Requests</a></li>
                <li><a href="../index.php">View Site</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <header class="admin-header">
                <h1>Category Management</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['Name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="categories-section">
                <div class="section-header">
                    <h2>All Categories (<?php echo count($categories); ?>)</h2>
                    <button class="add-category-btn" onclick="openAddModal()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M12 5V19M5 12H19" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Add New Category
                    </button>
                </div>
                
                <?php if (empty($categories)): ?>
                    <div class="no-categories">
                        <h3>No categories found</h3>
                        <p>There are no categories in the database yet.</p>
                        <button class="add-category-btn" onclick="openAddModal()">Add Your First Category</button>
                    </div>
                <?php else: ?>
                    <table class="categories-table">
                        <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Models</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($category['CategoryName']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="model-count"><?php echo $category['model_count']; ?> models</span>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($category['CreatedAt'])); ?>
                                    </td>
                                    <td>
                                        <div class="category-actions">
                                            <button class="btn-edit" onclick="openEditModal(<?php echo $category['CategoryID']; ?>, '<?php echo htmlspecialchars($category['CategoryName'], ENT_QUOTES); ?>')">
                                                Edit
                                            </button>
                                            <a href="managecategories.php?delete_id=<?php echo $category['CategoryID']; ?>" 
                                               class="btn-delete" 
                                               onclick="return confirm('Are you sure you want to delete this category?')">
                                                Delete
                                            </a>
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
    
    <div class="modal-overlay" id="addModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add New Category</h3>
                <button class="close-btn" onclick="closeAddModal()">×</button>
            </div>
            <form method="POST" action="addcategories.php" class="modal-form">
                <div class="form-group">
                    <label class="form-label" for="category_name">Category Name</label>
                    <input type="text" 
                           id="category_name" 
                           name="category_name" 
                           class="form-input" 
                           required 
                           placeholder="Enter category name">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn-save">Add Category</button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Edit Category</h3>
                <button class="close-btn" onclick="closeEditModal()">×</button>
            </div>
            <form method="POST" action="" class="modal-form" id="editForm">
                <input type="hidden" name="category_id" id="edit_category_id">
                <input type="hidden" name="edit_category" value="1">
                <div class="form-group">
                    <label class="form-label" for="edit_category_name">Category Name</label>
                    <input type="text" 
                           id="edit_category_name" 
                           name="category_name" 
                           class="form-input" 
                           required 
                           placeholder="Enter category name">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-save">Update Category</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        
        function openEditModal(categoryId, categoryName) {
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_category_name').value = categoryName;
            document.getElementById('editForm').action = window.location.href;
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        document.addEventListener('click', function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>