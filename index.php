<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strathmore Connect - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="header">
                <h1>Strathmore Connect</h1>
                <p>University Community Platform</p>
                
                <?php
                // Display status messages
                if (isset($_GET['error'])) {
                    echo '<div class="alert error">Please use your Strathmore University email</div>';
                }
                if (isset($_GET['registered'])) {
                    echo '<div class="alert success">Registration successful! Please login.</div>';
                }
                if (isset($_GET['logout'])) {
                    echo '<div class="alert info">You have been logged out successfully.</div>';
                }
                ?>
            </div>
            
            <!-- Login Form -->
            <form action="auth.php" method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="email">University Email:</label>
                    <input type="email" id="email" name="email" placeholder="name@strathmore.edu" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn-primary">Login</button>
                <button type="button" onclick="showRegister()" class="btn-secondary">Create New Account</button>
                
                <div class="links">
                    <a href="#" onclick="alert('Password reset link will be sent to your email')">Forgot Password?</a>
                </div>
            </form>

            <!-- Registration Form -->
            <div id="registerForm" class="register-form" style="display: none;">
                <h3>Create Account</h3>
                <form action="auth.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="form-group">
                        <input type="text" name="fullname" placeholder="Full Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="University Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Create Password" required>
                    </div>
                    <div class="form-group">
                        <select name="role">
                            <option value="student">Student</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Register</button>
                    <button type="button" onclick="showLogin()" class="btn-secondary">Back to Login</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showRegister() {
            document.querySelector('form').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
        }

        function showLogin() {
            document.querySelector('form').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';
        }
    </script>
</body>
</html>