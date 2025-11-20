<?php
session_start();
require_once 'config.php';

if ($_POST['action'] == 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Check if it's a Strathmore email
    if (strpos($email, '@strathmore.edu') === false) {
        header('Location: index.php?error=1');
        exit();
    }
    
    // Check user in database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // For demo purposes, accept any password for sample accounts
    // In real system, use: password_verify($password, $user['password'])
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        header('Location: feed.php');
        exit();
    } else {
        header('Location: index.php?error=1');
        exit();
    }
}

if ($_POST['action'] == 'register') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Validate Strathmore email
    if (strpos($email, '@strathmore.edu') === false) {
        header('Location: index.php?error=1');
        exit();
    }
    
    try {
        // Hash password for real system: $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $hashed_password = password_hash('password123', PASSWORD_DEFAULT); // Demo only
        
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fullname, $email, $hashed_password, $role]);
        
        header('Location: index.php?registered=1');
        exit();
    } catch(PDOException $e) {
        header('Location: index.php?error=2');
        exit();
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php?logout=1');
    exit();
}
?>