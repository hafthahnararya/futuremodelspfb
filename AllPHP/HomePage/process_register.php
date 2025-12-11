<?php
session_start();
require_once '../utils/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $username = sanitizeInput($_POST['username']);
    $dob = sanitizeInput($_POST['dob']);
    $phone = sanitizeInput($_POST['phone']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmpassword'];
    
    $_SESSION['form_data'] = [
        'name' => $name,
        'username' => $username,
        'dob' => $dob,
        'phone' => $phone,
        'email' => $email
    ];
    
    $errors = [];
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) < 3) {
        $errors[] = 'Name must be at least 3 characters long';
    }
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = 'Username must be between 3-20 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }
    
    if (empty($dob)) {
        $errors[] = 'Date of birth is required';
    } else {
        $birthDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        if ($age < 13) {
            $errors[] = 'You must be at least 13 years old to sign up';
        } elseif ($age > 120) {
            $errors[] = 'Please enter a valid date of birth';
        }
    }
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    } else {
        $phoneDigits = preg_replace('/\D/', '', $phone);
        if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
            $errors[] = 'Phone number must be between 10-15 digits';
        }
    }
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Please enter a valid email address';
    }
    if (empty($errors)) {
        $conn = getDBConnection();
        $checkQuery = "SELECT UserID FROM User WHERE UserEmail = ? OR UserName = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "ss", $email, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $checkQuery2 = "SELECT UserID FROM User WHERE UserEmail = ?";
            $stmt2 = mysqli_prepare($conn, $checkQuery2);
            mysqli_stmt_bind_param($stmt2, "s", $email);
            mysqli_stmt_execute($stmt2);
            $result2 = mysqli_stmt_get_result($stmt2);
            
            if (mysqli_num_rows($result2) > 0) {
                $errors[] = 'This email is already registered';
            } else {
                $errors[] = 'This username is already taken';
            }
        }
        mysqli_close($conn);
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    } elseif (!validatePassword($password)) {
        $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
    }
    if (empty($confirmPassword)) {
        $errors[] = 'Please confirm your password';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    if (!isset($_POST['terms'])) {
        $errors[] = 'You must agree to the Terms of Service and Privacy Policy';
    }
    
    if (!empty($errors)) {
        setFlashMessage('error', implode('<br>', $errors));
        redirect('signUp.php');
        exit();
    }
    
    $result = registerUser($username, $name, $email, $password, $phone, $dob);
    
    if ($result['success']) {
        unset($_SESSION['form_data']);
        setFlashMessage('success', 'Registration successful! Please login with your credentials.');
        redirect('login.php');
    } else {
        setFlashMessage('error', $result['message']);
        redirect('signUp.php');
    }
} else {
    setFlashMessage('error', 'Invalid request method');
    redirect('signUp.php');
}
?>