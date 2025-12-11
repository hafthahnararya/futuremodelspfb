<?php
require_once '../utils/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (!validateEmail($email)) {
        setFlashMessage('error', 'Please enter a valid email address');
        redirect('login.php');
        exit();
    }
    
    if (empty($password)) {
        setFlashMessage('error', 'Please enter your password');
        redirect('login.php');
        exit();
    }
    
    $result = tryLogin($email, $password);
    
    if ($result['success']) {
        $_SESSION['role'] = $result['role'];
        $_SESSION['user_data'] = $result['data'];
        $_SESSION['name'] = $result['data']['Name'];
        $_SESSION['email'] = $result['data']['UserEmail'];
        
        if ($remember) {
            setcookie('user_email', $email, time() + (86400 * 30), '/');
        } else {
            if (isset($_COOKIE['user_email'])) {
                setcookie('user_email', '', time() - 3600, '/');
            }
        }
        if (in_array($result['role'], ['admin'])) {
            setFlashMessage('success', 'Welcome back, ' . $result['data']['Name'] . '!');
            redirect('../Admin/adminPage.php');
        } else {
            setFlashMessage('success', 'Welcome back, ' . $result['data']['Name'] . '!');
            
            if (isset($_SESSION['redirect_after_login'])) {
                $redirectUrl = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                redirect($redirectUrl);
            } else {
                redirect('../index.php');
            }
        }
    } else {
        setFlashMessage('error', $result['message']);
        redirect('login.php');
    }
} else {
    setFlashMessage('error', 'Invalid request method');
    redirect('login.php');
}
?>