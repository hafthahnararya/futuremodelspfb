<?php
session_start();
require_once '../utils/function.php';

requireAdmin();

$admin = getCurrentUser();
$categories = getAllCategories();

if (!isset($_GET['id'])) {
    setFlashMessage('error', 'Model ID is required');
    header('Location: manageModels.php');
    exit();
}

$modelId = (int)$_GET['id'];
$model = getModelById($modelId);

if (!$model) {
    setFlashMessage('error', 'Model not found');
    header('Location: manageModels.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelName = sanitizeInput($_POST['model_name']);
    $modelEmail = sanitizeInput($_POST['model_email']);
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $height = !empty($_POST['height']) ? (float)$_POST['height'] : null;
    $bust = !empty($_POST['bust']) ? (float)$_POST['bust'] : null;
    $waist = !empty($_POST['waist']) ? (float)$_POST['waist'] : null;
    $hips = !empty($_POST['hips']) ? (float)$_POST['hips'] : null;
    $top = sanitizeInput($_POST['top'] ?? '');
    $pants = sanitizeInput($_POST['pants'] ?? '');
    $shoes = sanitizeInput($_POST['shoes'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($modelName) || empty($modelEmail)) {
        setFlashMessage('error', 'Model name and email are required');
    } elseif (!validateEmail($modelEmail)) {
        setFlashMessage('error', 'Invalid email format');
    } else {
        $imagePath = $model['ModelImage'];
        
        if (isset($_FILES['model_image']) && $_FILES['model_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile(
                $_FILES['model_image'],
                UPLOAD_PATH . 'models/',
                ['jpg', 'jpeg', 'png', 'webp']
            );
            
            if ($uploadResult['success']) {
                if (!empty($model['ModelImage'])) {
                    $oldImagePath = UPLOAD_PATH . str_replace('uploads/', '', $model['ModelImage']);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $imagePath = 'uploads/models/' . $uploadResult['filename'];
            } else {
                setFlashMessage('error', $uploadResult['message']);
            }
        }
        
        if (updateModel($modelId, $modelName, $modelEmail, $categoryId, $height, $bust, $waist, $hips, $top, $pants, $shoes, $description, $imagePath, $status)) {
            setFlashMessage('success', 'Model updated successfully');
            header('Location: manageModels.php');
            exit();
        } else {
            setFlashMessage('error', 'Failed to update model');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Model - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/editmodel.css">
    
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
                <h1>Edit Model</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['Name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" class="model-form">
                    <div class="form-section">
                        <h2>Basic Information</h2>
                        
                        <div class="form-group required">
                            <label for="model_name">Model Name</label>
                            <input type="text" id="model_name" name="model_name" 
                                   value="<?php echo htmlspecialchars($model['ModelName']); ?>" 
                                   required
                                   placeholder="Enter model name">
                        </div>
                        
                        <div class="form-group required">
                            <label for="model_email">Email Address</label>
                            <input type="email" id="model_email" name="model_email" 
                                   value="<?php echo htmlspecialchars($model['ModelEmail']); ?>" 
                                   required
                                   placeholder="Enter email address">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['CategoryID']; ?>"
                                                <?php echo ($model['CategoryID'] == $category['CategoryID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['CategoryName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <?php
                                    $conn = getDBConnection();
                                    $statusQuery = "SELECT Status FROM ModelView WHERE ModelID = ?";
                                    $stmt = mysqli_prepare($conn, $statusQuery);
                                    mysqli_stmt_bind_param($stmt, "i", $modelId);
                                    mysqli_stmt_execute($stmt);
                                    $statusResult = mysqli_stmt_get_result($stmt);
                                    $statusRow = mysqli_fetch_assoc($statusResult);
                                    $currentStatus = $statusRow ? $statusRow['Status'] : 'active';
                                    mysqli_close($conn);
                                    ?>
                                    <option value="active" <?php echo ($currentStatus === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($currentStatus === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="model_image">Model Image</label>
                            <?php if (!empty($model['ModelImage'])): ?>
                                <div class="current-image">
                                    <?php
                                    $imagePath = $model['ModelImage'];
                                    if (strpos($imagePath, 'uploads/') === 0) {
                                        $imagePath = '../' . $imagePath;
                                    } else {
                                        $imagePath = '../uploads/models/' . basename($imagePath);
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                         alt="Current model image"
                                         onerror="this.onerror=null; this.src='../assets/image/placeholder.png'; this.alt='Image not found';">
                                    <p>Current image (upload new to replace)</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="model_image" name="model_image" accept="image/*">
                            <small>Max file size: 5MB. Formats: JPG, PNG, WEBP</small>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2>Measurements (cm)</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <div class="measurement-input">
                                    <label for="height">Height</label>
                                    <input type="number" id="height" name="height" step="0.01" 
                                           value="<?php echo $model['Height']; ?>"
                                           placeholder="0.00">
                                    <span class="measurement-unit">cm</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="measurement-input">
                                    <label for="bust">Bust</label>
                                    <input type="number" id="bust" name="bust" step="0.01" 
                                           value="<?php echo $model['Bust']; ?>"
                                           placeholder="0.00">
                                    <span class="measurement-unit">cm</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <div class="measurement-input">
                                    <label for="waist">Waist</label>
                                    <input type="number" id="waist" name="waist" step="0.01" 
                                           value="<?php echo $model['Waist']; ?>"
                                           placeholder="0.00">
                                    <span class="measurement-unit">cm</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="measurement-input">
                                    <label for="hips">Hips</label>
                                    <input type="number" id="hips" name="hips" step="0.01" 
                                           value="<?php echo $model['Hips']; ?>"
                                           placeholder="0.00">
                                    <span class="measurement-unit">cm</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2>Sizes</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="top">Top Size</label>
                                <input type="text" id="top" name="top" 
                                       value="<?php echo htmlspecialchars($model['Top']); ?>" 
                                       placeholder="e.g., S, M, L">
                            </div>
                            
                            <div class="form-group">
                                <label for="pants">Pants Size</label>
                                <input type="text" id="pants" name="pants" 
                                       value="<?php echo htmlspecialchars($model['Pants']); ?>" 
                                       placeholder="e.g., 28, 30, 32">
                            </div>
                            
                            <div class="form-group">
                                <label for="shoes">Shoe Size</label>
                                <input type="text" id="shoes" name="shoes" 
                                       value="<?php echo htmlspecialchars($model['Shoes']); ?>" 
                                       placeholder="e.g., 7, 8, 9">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2>Description</h2>
                        
                        <div class="form-group">
                            <label for="description">Additional Information</label>
                            <textarea id="description" name="description" rows="5" class="form-textarea"
                                      placeholder="Enter any additional information about the model..."><?php echo htmlspecialchars($model['Description']); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="manageModels.php" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-submit">Update Model</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php displayFlashMessage(); ?>
</body>
</html>