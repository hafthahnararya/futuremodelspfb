<?php
require_once '../utils/function.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = (int)$_POST['post_id'];
    $reason = isset($_POST['reason']) ? sanitizeInput($_POST['reason']) : 'No reason provided';
    
    if (rejectPost($postId, $reason)) { 
        $post = getPostById($postId);
        if ($post) {
            createNotification(
                $post['UserID'],
                "Your post '{$post['Title']}' has been rejected. Reason: {$reason}"
            );
        }
        
        setFlashMessage('success', 'Post rejected successfully');
    } else {
        setFlashMessage('error', 'Failed to reject post');
    }
} else {
    setFlashMessage('error', 'Invalid request');
}

redirect('adminPage.php');
?>