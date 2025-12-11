<?php
session_start();
require_once '../utils/function.php';

destroySession();
setFlashMessage('success', 'Logged out successfully!');
redirect('../HomePage/login.php');
?>



