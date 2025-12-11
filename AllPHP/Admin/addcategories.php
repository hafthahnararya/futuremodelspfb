<?php
session_start();
require_once '../utils/function.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = isset($_POST['category_name']) ? sanitizeInput($_POST['category_name']) : '';
    
    if (!empty($categoryName)) {
        $conn = getDBConnection();
        $checkQuery = "SELECT CategoryID FROM Category WHERE CategoryName = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $categoryName);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            setFlashMessage('error', 'Category already exists');
        } else {
            $result = addCategory($categoryName);
            
            if ($result) {
                setFlashMessage('success', 'Category added successfully');
            } else {
                setFlashMessage('error', 'Failed to add category');
            }
        }
        
        mysqli_close($conn);
    } else {
        setFlashMessage('error', 'Please enter a category name');
    }
    
    header('Location: managecategories.php');
    exit();
}
?>