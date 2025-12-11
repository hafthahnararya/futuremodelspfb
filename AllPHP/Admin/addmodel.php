<?php
session_start();
require_once '../utils/function.php';

requireAdmin();

$admin = getCurrentUser();
$categories = getAllCategories();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelName = isset($_POST['model_name']) ? sanitizeInput($_POST['model_name']) : '';
    $modelEmail = isset($_POST['model_email']) ? sanitizeInput($_POST['model_email']) : '';
    $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $height = isset($_POST['height']) && $_POST['height'] !== '' ? floatval($_POST['height']) : null;
    $bust = isset($_POST['bust']) && $_POST['bust'] !== '' ? floatval($_POST['bust']) : null;
    $waist = isset($_POST['waist']) && $_POST['waist'] !== '' ? floatval($_POST['waist']) : null;
    $hips = isset($_POST['hips']) && $_POST['hips'] !== '' ? floatval($_POST['hips']) : null;
    $top = isset($_POST['top']) ? sanitizeInput($_POST['top']) : null;
    $pants = isset($_POST['pants']) ? sanitizeInput($_POST['pants']) : null;
    $shoes = isset($_POST['shoes']) ? sanitizeInput($_POST['shoes']) : null;
    $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : null;
    if (empty($modelName)) {
        $errors[] = 'Model name is required';
    }
    
    if (empty($modelEmail)) {
        $errors[] = 'Model email is required';
    } elseif (!validateEmail($modelEmail)) {
        $errors[] = 'Invalid email format';
    }
    
    if ($categoryId && $categoryId <= 0) {
        $errors[] = 'Please select a valid category';
    }
    if (empty($errors)) {
        $conn = getDBConnection();
        $checkQuery = "SELECT ModelID FROM Model WHERE ModelEmail = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $modelEmail);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'Model with this email already exists';
        }
        mysqli_close($conn);
    }
    $imagePath = null;
    if (isset($_FILES['model_image']) && $_FILES['model_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        $file = $_FILES['model_image'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedTypes)) {
            $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes);
        }
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File too large. Maximum size: 5MB';
        }
        
        if (empty($errors)) {
            $uploadDir = '../uploads/models/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $filename = uniqid() . '_' . time() . '.' . $fileExtension;
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $imagePath = $filename;
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    }
    if (empty($errors)) {
        try {
            $modelId = addModel(
                $modelName,
                $modelEmail,
                $categoryId,
                $height,
                $bust,
                $waist,
                $hips,
                $top,
                $pants,
                $shoes,
                $description,
                $imagePath
            );
            
            if ($modelId) {
                $success = true;
                setFlashMessage('success', 'Model added successfully!');
                $_POST = [];
                
                header('Location: manageModels.php');
                exit();
            } else {
                $errors[] = 'Failed to add model. Please try again.';
            }
        } catch (Exception $e) {
            $errors[] = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Model - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/adminPage.css">
    <link rel="stylesheet" href="../assets/css/addmodel.css">
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
                <h1>Add New Model</h1>
                <div class="admin-info">
                    <span>Welcome, <?php echo htmlspecialchars($admin['Name']); ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <div class="add-model-container">
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <strong>Please fix the following errors:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message">
                        Model added successfully! Redirecting...
                    </div>
                <?php endif; ?>
                
                <div class="form-container">
                    <div class="form-header">
                        <h2>Model Information</h2>
                        <p>Fill in the details below to add a new model to the system.</p>
                    </div>
                    
                    <form method="POST" action="" enctype="multipart/form-data" id="addModelForm">
                        <div class="form-group">
                            <label class="form-label required" for="model_name">Model Name</label>
                            <input type="text" 
                                   id="model_name" 
                                   name="model_name" 
                                   class="form-input" 
                                   value="<?php echo isset($_POST['model_name']) ? htmlspecialchars($_POST['model_name']) : ''; ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required" for="model_email">Email Address</label>
                            <input type="email" 
                                   id="model_email" 
                                   name="model_email" 
                                   class="form-input" 
                                   value="<?php echo isset($_POST['model_email']) ? htmlspecialchars($_POST['model_email']) : ''; ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-select">
                                <option value="">Select a category (optional)</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['CategoryID']; ?>"
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['CategoryID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['CategoryName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="model_image">Profile Image</label>
                            <div class="file-upload" onclick="document.getElementById('fileInput').click()">
                                <input type="file" 
                                       id="fileInput" 
                                       name="model_image" 
                                       accept="image/jpeg,image/png,image/webp"
                                       onchange="previewImage(event)">
                                <div class="upload-placeholder" id="uploadPlaceholder">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <p>Click to upload image (JPG, PNG, WebP)</p>
                                    <p style="font-size: 0.75rem; margin-top: 0.5rem;">Max size: 5MB</p>
                                </div>
                                <div class="preview-image" id="imagePreview" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Body Measurements (in cm)</label>
                            <div class="measurements-grid">
                                <div class="measurement-input">
                                    <input type="number" 
                                           id="height" 
                                           name="height" 
                                           class="form-input" 
                                           step="0.01"
                                           placeholder="Height"
                                           value="<?php echo isset($_POST['height']) ? htmlspecialchars($_POST['height']) : ''; ?>">
                                    <span class="measurement-unit">cm</span>
                                </div>
                                <div class="measurement-input">
                                    <input type="number" 
                                           id="bust" 
                                           name="bust" 
                                           class="form-input" 
                                           step="0.01"
                                           placeholder="Bust"
                                           value="<?php echo isset($_POST['bust']) ? htmlspecialchars($_POST['bust']) : ''; ?>">
                                    <span class="measurement-unit">cm</span>
                                </div>
                                <div class="measurement-input">
                                    <input type="number" 
                                           id="waist" 
                                           name="waist" 
                                           class="form-input" 
                                           step="0.01"
                                           placeholder="Waist"
                                           value="<?php echo isset($_POST['waist']) ? htmlspecialchars($_POST['waist']) : ''; ?>">
                                    <span class="measurement-unit">cm</span>
                                </div>
                                <div class="measurement-input">
                                    <input type="number" 
                                           id="hips" 
                                           name="hips" 
                                           class="form-input" 
                                           step="0.01"
                                           placeholder="Hips"
                                           value="<?php echo isset($_POST['hips']) ? htmlspecialchars($_POST['hips']) : ''; ?>">
                                    <span class="measurement-unit">cm</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Clothing Sizes</label>
                            <div class="measurements-grid">
                                <div>
                                    <input type="text" 
                                           id="top" 
                                           name="top" 
                                           class="form-input" 
                                           placeholder="Top (e.g., S, M, L)"
                                           value="<?php echo isset($_POST['top']) ? htmlspecialchars($_POST['top']) : ''; ?>">
                                </div>
                                <div>
                                    <input type="text" 
                                           id="pants" 
                                           name="pants" 
                                           class="form-input" 
                                           placeholder="Pants (e.g., 28, 30)"
                                           value="<?php echo isset($_POST['pants']) ? htmlspecialchars($_POST['pants']) : ''; ?>">
                                </div>
                                <div>
                                    <input type="text" 
                                           id="shoes" 
                                           name="shoes" 
                                           class="form-input" 
                                           placeholder="Shoes (e.g., 7, 8)"
                                           value="<?php echo isset($_POST['shoes']) ? htmlspecialchars($_POST['shoes']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="description">Description</label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-textarea" 
                                      placeholder="Add a description about the model (optional)"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="managemodels.php" class="btn-cancel">Cancel</a>
                            <button type="submit" class="btn-submit">Add Model</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function previewImage(event) {
            const input = event.target;
            const uploadPlaceholder = document.getElementById('uploadPlaceholder');
            const imagePreview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    uploadPlaceholder.style.display = 'none';
                    imagePreview.style.display = 'block';
                    previewImg.src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                uploadPlaceholder.style.display = 'block';
                imagePreview.style.display = 'none';
                previewImg.src = '';
            }
        }
        
        // Form validation
        document.getElementById('addModelForm').addEventListener('submit', function(e) {
            const modelName = document.getElementById('model_name').value.trim();
            const modelEmail = document.getElementById('model_email').value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!modelName) {
                e.preventDefault();
                alert('Please enter the model name');
                document.getElementById('model_name').focus();
                return false;
            }
            
            if (!modelEmail) {
                e.preventDefault();
                alert('Please enter the model email');
                document.getElementById('model_email').focus();
                return false;
            }
            
            if (!emailPattern.test(modelEmail)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                document.getElementById('model_email').focus();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>