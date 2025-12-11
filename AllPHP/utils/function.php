<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'futuremodel');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); 

if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
    mkdir(UPLOAD_PATH . 'models/', 0777, true);
    mkdir(UPLOAD_PATH . 'posts/', 0777, true);
    mkdir(UPLOAD_PATH . 'media/', 0777, true);
}
function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $createDb = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (!mysqli_query($conn, $createDb)) {
        die("Error creating database: " . mysqli_error($conn));
    }
    
    mysqli_select_db($conn, DB_NAME);
    return $conn;
}
function createDatabaseAndTables() {
    $conn = getDBConnection();
    
    createCategoryTable($conn);
    createModelTable($conn);
    createModelViewTable($conn);
    createUserTable($conn);
    createPostTable($conn);
    createMediaTable($conn);
    createPostMediaTable($conn);
    createNotificationTable($conn);
    createAdminLogTable($conn);
    
    mysqli_close($conn);
    return true;
}
function createCategoryTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS Category (
        CategoryID INT AUTO_INCREMENT PRIMARY KEY,
        CategoryName VARCHAR(255) NOT NULL UNIQUE,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!mysqli_query($conn, $query)) {
        die("Error creating Category table: " . mysqli_error($conn));
    }
}
function createModelTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS Model (
        ModelID INT AUTO_INCREMENT PRIMARY KEY,
        ModelName VARCHAR(255) NOT NULL,
        ModelEmail VARCHAR(255) NOT NULL UNIQUE,
        CategoryID INT,
        ModelImage VARCHAR(500),
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        Height DECIMAL(5,2),
        Bust DECIMAL(5,2),
        Waist DECIMAL(5,2),
        Hips DECIMAL(5,2),
        Top VARCHAR(10),
        Pants VARCHAR(10),
        Shoes VARCHAR(10),
        Description TEXT,
        FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID) ON DELETE SET NULL
    )";
    
    if (!mysqli_query($conn, $query)) {
        die("Error creating Model table: " . mysqli_error($conn));
    }
}

function createModelViewTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS ModelView (
        ModelViewID INT AUTO_INCREMENT PRIMARY KEY,
        ModelID INT NOT NULL,
        ModelName VARCHAR(255) NOT NULL,
        Status ENUM('active', 'inactive') DEFAULT 'active',
        FOREIGN KEY (ModelID) REFERENCES Model(ModelID) ON DELETE CASCADE
    )";
    
    if (!mysqli_query($conn, $query)) {
        die("Error creating ModelView table: " . mysqli_error($conn));
    }
}

function createUserTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS User (
        UserID INT AUTO_INCREMENT PRIMARY KEY,
        UserName VARCHAR(255) NOT NULL UNIQUE,
        Name VARCHAR(255) NOT NULL,
        UserEmail VARCHAR(255) NOT NULL UNIQUE,
        UserPassword VARCHAR(255) NOT NULL,
        PhoneNumber VARCHAR(20),
        DateOfBirth DATE,
        Role ENUM('user', 'admin') DEFAULT 'user',
        Status ENUM('active', 'inactive') DEFAULT 'active',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!mysqli_query($conn, $query)) {
        die("Error creating User table: " . mysqli_error($conn));
    }
}
function createAdminTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS Admin (
        AdminID INT AUTO_INCREMENT PRIMARY KEY,
        AdminName VARCHAR(255) NOT NULL,
        AdminEmail VARCHAR(255) NOT NULL UNIQUE,
        AdminPassword VARCHAR(255) NOT NULL,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!mysqli_query($conn, $query)) {
        die("Error creating Admin table: " . mysqli_error($conn));
    }
}
function createPostTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS Post (
        PostId INT AUTO_INCREMENT PRIMARY KEY,
        TypeID INT DEFAULT 1,
        UserID INT NOT NULL,
        UserName VARCHAR(255) NOT NULL,
        Title VARCHAR(500) NOT NULL,
        Description TEXT,
        UploadDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        Status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        ReviewedBy INT NULL,
        ReviewedAt TIMESTAMP NULL,
        RejectionReason TEXT NULL,
        FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE,
        FOREIGN KEY (UserName) REFERENCES User(UserName) ON DELETE CASCADE,
        FOREIGN KEY (ReviewedBy) REFERENCES User(UserID) ON DELETE SET NULL
    )";
    
    if (!mysqli_query($conn, $query)) {
        die("Error creating Post table: " . mysqli_error($conn));
    }
}
function createAdminLogTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS AdminLog (
        LogID INT AUTO_INCREMENT PRIMARY KEY,
        AdminID INT NOT NULL,
        Action VARCHAR(255) NOT NULL,
        TargetTable VARCHAR(100),
        TargetID INT,
        Details TEXT,
        IPAddress VARCHAR(45),
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (AdminID) REFERENCES User(UserID) ON DELETE CASCADE
    )";
    
    if (!mysqli_query($conn, $query)) {
        die("Error creating AdminLog table: " . mysqli_error($conn));
    }
}
function createMediaTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS Media (
        MediaId INT AUTO_INCREMENT PRIMARY KEY,
        MediaType ENUM('image', 'video') NOT NULL,
        FilePath VARCHAR(500) NOT NULL,
        FormatFile VARCHAR(10),
        Size INT,
        Resolution VARCHAR(20),
        Duration INT,
        UploadedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UploadedBy VARCHAR(100)
    )";
    
    if (!mysqli_query($conn, $query)) {
        die("Error creating Media table: " . mysqli_error($conn));
    }
}
function createPostMediaTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS PostMedia (
        PostMediaId INT AUTO_INCREMENT PRIMARY KEY,
        PostId INT NOT NULL,
        MediaId INT NOT NULL,
        Position INT DEFAULT 0,
        FOREIGN KEY (PostId) REFERENCES Post(PostId) ON DELETE CASCADE,
        FOREIGN KEY (MediaId) REFERENCES Media(MediaId) ON DELETE CASCADE
    )";
    
    if (!mysqli_query($conn, $query)) {
        die("Error creating PostMedia table: " . mysqli_error($conn));
    }
}
function createNotificationTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS Notification (
        NotificationID INT AUTO_INCREMENT PRIMARY KEY,
        NotificationName TEXT NOT NULL,
        Status ENUM('unread', 'read') DEFAULT 'unread',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UserID INT,
        FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE
    )";
    
    if (!mysqli_query($conn, $query)) {
        die("Error creating Notification table: " . mysqli_error($conn));
    }
}
function createNotification($userId, $message) {
    $conn = getDBConnection();
    
    $query = "INSERT INTO Notification (NotificationName, UserID) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $message, $userId);
    $result = mysqli_stmt_execute($stmt);
    
    mysqli_close($conn);
    return $result;
}
function getUserById($userId) {
    $conn = getDBConnection();
    
    $query = "SELECT * FROM User WHERE UserID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $user;
}

function isUserLoggedIn() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}


function getAllUsers($limit = 50, $offset = 0) {
    $conn = getDBConnection();
    
    $query = "SELECT * FROM User ORDER BY CreatedAt DESC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    mysqli_close($conn);
    return $users;
}
function isAnyoneLoggedIn() {
    return isset($_SESSION['role']);
}

function getCurrentUserData() {
    if (isUserLoggedIn() && isset($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }
    return null;
}
function requireUserLogin() {
    if (!isUserLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        setFlashMessage('error', 'Please login to continue');
        redirect('../HomePage/login.php');
        exit();
    }
}
function addModel($modelName, $modelEmail, $categoryId, $height, $bust, $waist, $hips, $top, $pants, $shoes, $description, $imagePath = null) {
    $conn = getDBConnection();
    
    mysqli_begin_transaction($conn);
    
    try {
        $query = "INSERT INTO Model (ModelName, ModelEmail, CategoryID, Height, Bust, Waist, Hips, Top, Pants, Shoes, Description, ModelImage) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssidddddssss", $modelName, $modelEmail, $categoryId, $height, $bust, $waist, $hips, $top, $pants, $shoes, $description, $imagePath);
        mysqli_stmt_execute($stmt);
        
        $modelId = mysqli_insert_id($conn);
        $viewQuery = "INSERT INTO ModelView (ModelID, ModelName, Status) VALUES (?, ?, 'active')";
        $stmt2 = mysqli_prepare($conn, $viewQuery);
        mysqli_stmt_bind_param($stmt2, "is", $modelId, $modelName);
        mysqli_stmt_execute($stmt2);
        
        mysqli_commit($conn);
        mysqli_close($conn);
        return $modelId;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        return false;
    }
}

function getAllModels($categoryId = null, $limit = 50, $offset = 0) {
    $conn = getDBConnection();
    
    if ($categoryId) {
        $query = "SELECT m.*, c.CategoryName FROM Model m 
                  LEFT JOIN Category c ON m.CategoryID = c.CategoryID 
                  WHERE m.CategoryID = ? LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iii", $categoryId, $limit, $offset);
    } else {
        $query = "SELECT m.*, c.CategoryName FROM Model m 
                  LEFT JOIN Category c ON m.CategoryID = c.CategoryID 
                  LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $models = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $models[] = $row;
    }
    
    mysqli_close($conn);
    return $models;
}

function getModelById($modelId) {
    $conn = getDBConnection();
    
    $query = "SELECT m.*, c.CategoryName FROM Model m 
              LEFT JOIN Category c ON m.CategoryID = c.CategoryID 
              WHERE m.ModelID = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $modelId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $model = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $model;
}

function getModelsByCategory($categoryId) {
    $conn = getDBConnection();
    
    $query = "SELECT m.* FROM Model m 
              INNER JOIN ModelView mv ON m.ModelID = mv.ModelID 
              WHERE m.CategoryID = ? AND mv.Status = 'active'";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $categoryId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $models = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $models[] = $row;
    }
    
    mysqli_close($conn);
    return $models;
}
function addCategory($categoryName) {
    $conn = getDBConnection();
    
    $query = "INSERT INTO Category (CategoryName) VALUES (?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $categoryName);
    $result = mysqli_stmt_execute($stmt);
    
    mysqli_close($conn);
    return $result;
}

function getAllCategories() {
    $conn = getDBConnection();
    
    $query = "SELECT c.*, COUNT(m.ModelID) as model_count 
              FROM Category c 
              LEFT JOIN Model m ON c.CategoryID = m.CategoryID 
              GROUP BY c.CategoryID, c.CategoryName";
    
    $result = mysqli_query($conn, $query);
    $categories = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    mysqli_close($conn);
    return $categories;
}

function getCategoryById($categoryId) {
    $conn = getDBConnection();
    
    $query = "SELECT * FROM Category WHERE CategoryID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $categoryId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $category = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $category;
}


function createPost($userId, $userName, $typeId, $title, $description) {
    $conn = getDBConnection();
    
    $query = "INSERT INTO Post (UserID, UserName, TypeID, Title, Description, Status) VALUES (?, ?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isiss", $userId, $userName, $typeId, $title, $description);
    
    if (mysqli_stmt_execute($stmt)) {
        $postId = mysqli_insert_id($conn);
        mysqli_close($conn);
        return $postId;
    } else {
        $error = mysqli_error($conn);
        mysqli_close($conn);
        error_log("Error creating post: " . $error);
        return false;
    }
}

function getAllPosts($status = 'approved', $limit = 20, $offset = 0) {
    $conn = getDBConnection();
    
    $query = "SELECT p.*, u.Name as AuthorName, u.UserEmail as AuthorEmail 
              FROM Post p 
              LEFT JOIN User u ON p.UserID = u.UserID 
              WHERE p.Status = ? 
              ORDER BY p.UploadDate DESC 
              LIMIT ? OFFSET ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sii", $status, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    mysqli_close($conn);
    return $posts;
}
function getPendingPosts($limit = 50, $offset = 0) {
    $conn = getDBConnection();
    
    $query = "SELECT p.*, u.Name as AuthorName, u.UserEmail as AuthorEmail 
              FROM Post p 
              LEFT JOIN User u ON p.UserID = u.UserID 
              WHERE p.Status = 'pending' 
              ORDER BY p.UploadDate DESC 
              LIMIT ? OFFSET ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    mysqli_close($conn);
    return $posts;
}
function getPostById($postId) {
    $conn = getDBConnection();
    
    $query = "SELECT p.*, u.Name as AuthorName, u.UserEmail as AuthorEmail 
              FROM Post p 
              LEFT JOIN User u ON p.UserID = u.UserID 
              WHERE p.PostId = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $post;
}

function getPostsByUser($userId, $limit = 20, $offset = 0) {
    $conn = getDBConnection();
    
    $query = "SELECT p.*, u.Name as AuthorName 
              FROM Post p 
              LEFT JOIN User u ON p.UserID = u.UserID 
              WHERE p.UserID = ? 
              ORDER BY p.UploadDate DESC 
              LIMIT ? OFFSET ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iii", $userId, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    mysqli_close($conn);
    return $posts;
}

function getPostFirstMedia($postId) {
    $conn = getDBConnection();
    
    $query = "SELECT m.* FROM Media m 
              INNER JOIN PostMedia pm ON m.MediaId = pm.MediaId 
              WHERE pm.PostId = ? 
              ORDER BY pm.Position 
              LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $media = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $media;
}

function getPostMedia($postId) {
    $conn = getDBConnection();
    
    $query = "SELECT m.* FROM Media m 
              INNER JOIN PostMedia pm ON m.MediaId = pm.MediaId 
              WHERE pm.PostId = ? 
              ORDER BY pm.Position";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $postId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $media = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $media[] = $row;
    }
    
    mysqli_close($conn);
    return $media;
}

function addMediaToPost($postId, $mediaId, $position = 0) {
    $conn = getDBConnection();
    
    $query = "INSERT INTO PostMedia (PostId, MediaId, Position) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iii", $postId, $mediaId, $position);
    $result = mysqli_stmt_execute($stmt);
    
    mysqli_close($conn);
    return $result;
}

function uploadMedia($file, $mediaType, $uploadedBy) {
    $conn = getDBConnection();
    
    $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedVideoTypes = ['mp4', 'mov', 'avi', 'webm'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error: ' . $file['error']];
    }
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($mediaType === 'image' && !in_array($fileExtension, $allowedImageTypes)) {
        return ['success' => false, 'message' => 'Invalid image type. Allowed: ' . implode(', ', $allowedImageTypes)];
    }
    
    if ($mediaType === 'video' && !in_array($fileExtension, $allowedVideoTypes)) {
        return ['success' => false, 'message' => 'Invalid video type. Allowed: ' . implode(', ', $allowedVideoTypes)];
    }
    $maxSize = $mediaType === 'video' ? 52428800 : MAX_FILE_SIZE;
    
    if ($file['size'] > $maxSize) {
        $maxSizeMB = $maxSize / 1048576;
        return ['success' => false, 'message' => 'File too large. Maximum size: ' . $maxSizeMB . 'MB'];
    }
    $destination = UPLOAD_PATH . ($mediaType === 'image' ? 'posts/' : 'media/');
    if (!file_exists($destination)) {
        mkdir($destination, 0777, true);
    }
    $filename = uniqid() . '_' . time() . '.' . $fileExtension;
    $filepath = $destination . $filename;
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
    $fileSize = $file['size'];
    $filePath = str_replace(UPLOAD_PATH, 'uploads/', $filepath);
    $resolution = null;
    if ($mediaType === 'image') {
        $imageInfo = getimagesize($filepath);
        if ($imageInfo) {
            $resolution = $imageInfo[0] . 'x' . $imageInfo[1];
        }
    }
    $duration = null;
    if ($mediaType === 'video' && function_exists('shell_exec')) {
        $cmd = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filepath);
        $output = shell_exec($cmd);
        if ($output) {
            $duration = (int)$output;
        }
    }
    $query = "INSERT INTO Media (MediaType, FilePath, FormatFile, Size, Resolution, Duration, UploadedBy) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssisss", $mediaType, $filePath, $fileExtension, $fileSize, $resolution, $duration, $uploadedBy);
    
    if (!mysqli_stmt_execute($stmt)) {
        unlink($filepath);
        mysqli_close($conn);
        return ['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)];
    }
    
    $mediaId = mysqli_insert_id($conn);
    mysqli_close($conn);
    
    return [
        'success' => true,
        'mediaId' => $mediaId,
        'filePath' => $filePath,
        'filename' => $filename,
        'mediaType' => $mediaType
    ];
}
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function uploadFile($file, $destination, $allowedTypes = ['jpg', 'jpeg', 'png', 'webp']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $filename = uniqid() . '_' . time() . '.' . $fileExtension;
    $filepath = $destination . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

function redirect($url) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header("Location: $url");
    exit();
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function escapeString($conn, $string) {
    return mysqli_real_escape_string($conn, $string);
}
function getTotalModels() {
    $conn = getDBConnection();
    
    $query = "SELECT COUNT(*) as total FROM Model";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $row['total'];
}

function TotalUser() {
    $conn = getDBConnection();
    
    $query = "SELECT COUNT(*) as total FROM User";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $row['total'];
}

function TotalPost() {
    $conn = getDBConnection();
    
    $query = "SELECT COUNT(*) as total FROM Post";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $row['total'];
}
function tryLogin($email, $password) {
    $conn = getDBConnection();
    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $query = "SELECT UserID, Name, UserName, UserEmail, UserPassword, PhoneNumber, DateOfBirth, Role, Status 
                  FROM User WHERE UserEmail = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if ($user['Status'] !== 'active') {
                mysqli_close($conn);
                return [
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact support.'
                ];
            }
            if (password_verify($password, $user['UserPassword'])) {
                mysqli_close($conn);
                return [
                    'success' => true,
                    'role' => $user['Role'],
                    'data' => $user
                ];
            }
        }
    
        mysqli_close($conn);
        return [
            'success' => false,
            'message' => 'Invalid email or password'
        ];
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        mysqli_close($conn);
        return [
            'success' => false,
            'message' => 'System error. Please try again.'
        ];
    }
}
function loginAsUser($email, $password) {
    $conn = getDBConnection();
    
    $query = "SELECT UserID, Name, UserName, UserEmail, UserPassword, PhoneNumber, DateOfBirth, Status 
              FROM User WHERE UserEmail = ?"; 
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        if ($user['Status'] !== 'active') {
            mysqli_close($conn);
            return false; 
        }
        
        if (verifyPassword($password, $user['UserPassword'])) {
            mysqli_close($conn);
            return $user;
        }
    }
    
    mysqli_close($conn);
    return false;
}
function isAdmin() {
    if (!isset($_SESSION['role']) || !isset($_SESSION['user_data'])) {
        return false;
    }
    return in_array($_SESSION['role'], ['admin']);
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

function requireAdmin() {
    if (!isAdmin()) {
        setFlashMessage('error', 'Admin access required');
        redirect('../HomePage/login.php');
        exit();
    }
}

function requireLogin() {
    if (!isset($_SESSION['role'])) {
        setFlashMessage('error', 'Please login first');
        redirect('../HomePage/login.php');
        exit();
    }
}
function getCurrentUser() {
    if (isset($_SESSION['user_data'])) {
        return $_SESSION['user_data'];
    }
    return null;
}

function registerUser($username, $name, $email, $password, $phone, $dob, $role = 'user') {
    $conn = getDBConnection();
    
    $checkQuery = "SELECT UserID FROM User WHERE UserEmail = ? OR UserName = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "ss", $email, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        mysqli_close($conn);
        return ['success' => false, 'message' => 'Email or username already exists'];
    }
    
    if (!in_array($role, ['user', 'admin'])) {
        $role = 'user';
    }
    
    $hashedPassword = hashPassword($password);
    $query = "INSERT INTO User (UserName, Name, UserEmail, UserPassword, PhoneNumber, DateOfBirth, Role) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssss", $username, $name, $email, $hashedPassword, $phone, $dob, $role);
    
    if (mysqli_stmt_execute($stmt)) {
        $userId = mysqli_insert_id($conn);
        mysqli_close($conn);
        return ['success' => true, 'userId' => $userId];
    } else {
        $error = mysqli_error($conn);
        mysqli_close($conn);
        return ['success' => false, 'message' => 'Registration failed: ' . $error];
    }
}
function logAdminAction($adminId, $action, $targetTable = null, $targetId = null, $details = null) {
    $conn = getDBConnection();
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    
    $query = "INSERT INTO AdminLog (AdminID, Action, TargetTable, TargetID, Details, IPAddress) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ississ", $adminId, $action, $targetTable, $targetId, $details, $ipAddress);
    $result = mysqli_stmt_execute($stmt);
    
    mysqli_close($conn);
    return $result;
}
function getActiveModels() {
    $conn = getDBConnection();
    
    $query = "SELECT COUNT(*) as count FROM ModelView WHERE Status = 'active'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $row['count'];
}

function getTotalPendingPosts() {
    $conn = getDBConnection();
    
    $query = "SELECT COUNT(*) as count FROM Post WHERE Status = 'pending'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $row['count'];
}
function approvePost($postId) {
    $conn = getDBConnection();
    
    $adminId = null;
    if (isAdmin()) {
        $currentUser = getCurrentUser();
        $adminId = $currentUser['UserID'];
    }
    
    $query = "UPDATE Post SET Status = 'approved', ReviewedBy = ?, ReviewedAt = NOW() WHERE PostId = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $adminId, $postId);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result && $adminId) {
        logAdminAction($adminId, 'Approved Post', 'Post', $postId, "Post ID: $postId approved");
    }
    
    mysqli_close($conn);
    return $result;
}

function rejectPost($postId, $reason = null) {
    $conn = getDBConnection();
    
    $adminId = null;
    if (isAdmin()) {
        $currentUser = getCurrentUser();
        $adminId = $currentUser['UserID'];
    }
    
    $query = "UPDATE Post SET Status = 'rejected', ReviewedBy = ?, ReviewedAt = NOW(), RejectionReason = ? WHERE PostId = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isi", $adminId, $reason, $postId);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result && $adminId) {
        logAdminAction($adminId, 'Rejected Post', 'Post', $postId, "Post ID: $postId rejected. Reason: $reason");
    }
    
    mysqli_close($conn);
    return $result;
}
function getTotalAdmins() {
    $conn = getDBConnection();
    
    $query = "SELECT COUNT(*) as total FROM User WHERE Role IN ('admin')";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $row['total'];
}
function destroySession() { 
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
session_destroy();
    session_start();
}
function getNewUsersToday() {
    $conn = getDBConnection();
    $query = "SELECT COUNT(*) as count FROM User WHERE DATE(CreatedAt) = CURDATE()";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    return $row['count'];
}

function getTotalRegularUsers() {
    $conn = getDBConnection();
    
    $query = "SELECT COUNT(*) as total FROM User WHERE Role = 'user'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $row['total'];
}

function getUserRegistrationsLast7Days() {
    $conn = getDBConnection();
    $query = "SELECT DATE(CreatedAt) as date, COUNT(*) as count 
              FROM User 
              WHERE CreatedAt >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
              GROUP BY DATE(CreatedAt) 
              ORDER BY date";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    mysqli_close($conn);
    return $data;
}

function getPostsLast7Days() {
    $conn = getDBConnection();
    $query = "SELECT DATE(UploadDate) as date, COUNT(*) as count 
              FROM Post 
              WHERE UploadDate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
              GROUP BY DATE(UploadDate) 
              ORDER BY date";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    mysqli_close($conn);
    return $data;
}

function getPostsByStatus($status) {
    $conn = getDBConnection();
    
    $query = "SELECT p.*, u.Name as AuthorName, u.UserEmail as AuthorEmail 
              FROM Post p 
              LEFT JOIN User u ON p.UserID = u.UserID 
              WHERE p.Status = ? 
              ORDER BY p.UploadDate DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    
    mysqli_close($conn);
    return $posts;
}

function updatePostStatus($postId, $status) {
    $conn = getDBConnection();
    
    $query = "UPDATE Post SET Status = ? WHERE PostId = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $postId);
    $result = mysqli_stmt_execute($stmt);
    
    mysqli_close($conn);
    return $result;
}

function getEstimatedPageViews() {
    $conn = getDBConnection();
    $query = "SELECT COUNT(*) as total_posts FROM Post WHERE Status = 'approved'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $estimatedViews = $row['total_posts'] * 10;
    mysqli_close($conn);
    return $estimatedViews;
}
function updateUserStatus($userId, $status) {
    $conn = getDBConnection();
    
    $query = "UPDATE User SET Status = ? WHERE UserID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $userId);
    $result = mysqli_stmt_execute($stmt);
    
    mysqli_close($conn);
    return $result;
}
function getUserPostCount($userId) {
    $conn = getDBConnection();
    
    $query = "SELECT COUNT(*) as count FROM Post WHERE UserID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $row['count'];
}
function getUserLastLogin($userId) {
    $conn = getDBConnection();
    
    $query = "SELECT CreatedAt FROM User WHERE UserID = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_close($conn);
    return $row['CreatedAt'];
}

function searchUsers($searchTerm) {
    $conn = getDBConnection();
    
    $query = "SELECT * FROM User 
              WHERE UserName LIKE ? 
              OR Name LIKE ? 
              OR UserEmail LIKE ? 
              ORDER BY CreatedAt DESC 
              LIMIT 50";
    
    $searchTerm = '%' . $searchTerm . '%';
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    mysqli_close($conn);
    return $users;
}
function updateModel($modelId, $modelName, $modelEmail, $categoryId, $height, $bust, $waist, $hips, $top, $pants, $shoes, $description, $imagePath, $status) {
    $conn = getDBConnection();
    
    mysqli_begin_transaction($conn);
    
    try {
        $query = "UPDATE Model SET 
                  ModelName = ?, 
                  ModelEmail = ?, 
                  CategoryID = ?, 
                  Height = ?, 
                  Bust = ?, 
                  Waist = ?, 
                  Hips = ?, 
                  Top = ?, 
                  Pants = ?, 
                  Shoes = ?, 
                  Description = ?, 
                  ModelImage = ? 
                  WHERE ModelID = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssisssssssssi", 
            $modelName, $modelEmail, $categoryId, $height, $bust, $waist, $hips, 
            $top, $pants, $shoes, $description, $imagePath, $modelId);
        mysqli_stmt_execute($stmt);
        
        $viewQuery = "UPDATE ModelView SET ModelName = ?, Status = ? WHERE ModelID = ?";
        $stmt2 = mysqli_prepare($conn, $viewQuery);
        mysqli_stmt_bind_param($stmt2, "ssi", $modelName, $status, $modelId);
        mysqli_stmt_execute($stmt2);
        
        mysqli_commit($conn);
        mysqli_close($conn);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        error_log("Error updating model: " . $e->getMessage());
        return false;
    }
}
function getFlashMessageHTML() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        $type = $flash['type'];
        $message = htmlspecialchars($flash['message']);
        
        $icon = $type === 'success' ? '✓' : '✕';
        $bgColor = $type === 'success' ? '#10b981' : '#ef4444';
        
        $html = "
        <div id='flashOverlay' class='flash-overlay'>
            <div class='flash-message flash-{$type}'>
                <div class='flash-icon' style='background-color: {$bgColor};'>{$icon}</div>
                <div class='flash-content'>
                    <h3>" . ucfirst($type) . "</h3>
                    <p>{$message}</p>
                </div>
                <button class='flash-close' onclick='closeFlash()'>×</button>
            </div>
        </div>
        
        <style>
            .flash-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                animation: fadeIn 0.3s ease;
            }
            
            .flash-message {
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                padding: 2rem;
                min-width: 400px;
                max-width: 500px;
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                position: relative;
                animation: slideDown 0.3s ease;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes slideDown {
                from {
                    transform: translateY(-20px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            .flash-icon {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 24px;
                font-weight: bold;
                flex-shrink: 0;
            }
            
            .flash-content {
                flex: 1;
            }
            
            .flash-content h3 {
                margin: 0 0 0.5rem 0;
                font-size: 1.25rem;
                font-weight: 600;
                color: #111827;
            }
            
            .flash-content p {
                margin: 0;
                color: #6b7280;
                font-size: 0.95rem;
                line-height: 1.5;
            }
            
            .flash-close {
                position: absolute;
                top: 1rem;
                right: 1rem;
                background: none;
                border: none;
                font-size: 28px;
                color: #9ca3af;
                cursor: pointer;
                padding: 0;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
                transition: all 0.2s;
            }
            
            .flash-close:hover {
                background-color: #f3f4f6;
                color: #4b5563;
            }
            
            @media (max-width: 640px) {
                .flash-message {
                    min-width: 90%;
                    margin: 1rem;
                }
            }
        </style>
        
        <script>
            function closeFlash() {
                const overlay = document.getElementById('flashOverlay');
                overlay.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    overlay.remove();
                }, 300);
            }
            
            setTimeout(closeFlash, 5000);
            
            document.getElementById('flashOverlay').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeFlash();
                }
            });
        
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        </script>";
        
        unset($_SESSION['flash']);
        return $html;
    }
    return '';
}

function displayFlashMessage() {
    echo getFlashMessageHTML();
}
function getImagePath($imagePath, $baseDir = 'models', $prefix = '../') {
    if (empty($imagePath)) {
        return '';
    }
    
    if (strpos($imagePath, 'uploads/') === 0) {
        return $prefix . $imagePath;
    }
    
    return $prefix . 'uploads/' . $baseDir . '/' . basename($imagePath);
}
?>
