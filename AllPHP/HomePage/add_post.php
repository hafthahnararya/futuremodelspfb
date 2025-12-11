<?php
require_once '../utils/function.php';

if (!isUser()) {
    setFlashMessage('error', 'Please login to create a post');
    redirect('login.php');
    exit();
}

$userId = null;
$username = 'User';

if (isset($_SESSION['user_data']['UserID'])) {
    $userId = $_SESSION['user_data']['UserID'];
    $username = $_SESSION['user_data']['UserName'] ?? 'User';
} elseif (isset($_SESSION['user']['id'])) {
    $userId = $_SESSION['user']['id'];
    $username = $_SESSION['user']['username'] ?? 'User';
}

if (!$userId) {
    setFlashMessage('error', 'Invalid user session');
    redirect('login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_post') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);

    if (empty($title)) {
        setFlashMessage('error', 'Title is required');
        redirect('forum.php');
        exit();
    }
    
    if (strlen($description) > 1000) {
        setFlashMessage('error', 'Description must not exceed 1000 characters');
        redirect('forum.php');
        exit();
    }
    
    if (!isset($_FILES['post_media']) || count($_FILES['post_media']['name']) === 0) {
        setFlashMessage('error', 'Please select at least one image or video');
        redirect('forum.php');
        exit();
    }
    $userName = '';
    if (isset($_SESSION['user_data']['UserName'])) {
        $userName = $_SESSION['user_data']['UserName'];
    } elseif (isset($_SESSION['user']['username'])) {
        $userName = $_SESSION['user']['username'];
    } else {
        $user = getUserById($userId);
        $userName = $user['UserName'] ?? 'default_user';
    }

    $postId = createPost($userId, $userName, 1, $title, $description);

    if ($postId) {
        $uploadedCount = 0;
        $fileCount = count($_FILES['post_media']['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['post_media']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['post_media']['name'][$i],
                    'type' => $_FILES['post_media']['type'][$i],
                    'tmp_name' => $_FILES['post_media']['tmp_name'][$i],
                    'error' => $_FILES['post_media']['error'][$i],
                    'size' => $_FILES['post_media']['size'][$i]
                ];
                
                $mediaType = strpos($file['type'], 'video') !== false ? 'video' : 'image';
                $mediaResult = uploadMedia($file, $mediaType, $username);

                if ($mediaResult['success']) {
                    addMediaToPost($postId, $mediaResult['mediaId'], $i);
                    $uploadedCount++;
                }
            }
        }

        if ($uploadedCount > 0) {
            setFlashMessage('success', 'Post created successfully with ' . $uploadedCount . ' media file(s)! Waiting for admin approval.');
            redirect('forum.php');
            exit();
        } else {
            setFlashMessage('error', 'Failed to upload media files');
            redirect('forum.php');
            exit();
        }
    } else {
        setFlashMessage('error', 'Failed to create post');
        redirect('forum.php');
        exit();
    }
} 
displayFlashMessage();
redirect('forum.php');
?>