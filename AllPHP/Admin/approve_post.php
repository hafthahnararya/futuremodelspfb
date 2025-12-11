<?php
session_start();
require_once '../utils/function.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = intval($_POST['post_id']);
    
    if (approvePost($postId)) {
        setFlashMessage('success', 'Post approved successfully!');
    } else {
        setFlashMessage('error', 'Failed to approve post.');
    }
}   
redirect('adminPage.php');
?>