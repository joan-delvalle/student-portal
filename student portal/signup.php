<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$auth = new Auth($db);

$error_message = "";
$success_message = "";

// Redirect if already logged in
if($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

if($_POST) {
    $user->username = $_POST['username'];
    $user->email = $_POST['email'];
    $user->password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user->role = $_POST['role'];
    $user->first_name = $_POST['first_name'];
    $user->last_name = $_POST['last_name'];
    $user->phone = $_POST['phone'];
    $user->address = $_POST['address'];
    $user->date_of_birth = $_POST['date_of_birth'];
    
    // Validation
    if($user->password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif(strlen($user->password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif($user->usernameExists()) {
        $error_message = "Username already exists.";
    } elseif($user->emailExists()) {
        $error_message = "Email already exists.";
    } else {
        if($user->create()) {
            $success_message = "Account created successfully! You can now login.";
        } else {
            $error_message = "Unable to create account. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card signup-card">
            <div class="auth-header">
                <h1>School Management System</h1>
                <h2>Create Account</h2>
            </div>
            
            <?php if($error_message): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form" id="signupForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone (Optional)</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth">
                </div>
                
                <div class="form-group">
                    <label for="address">Address (Optional)</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Create Account</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/auth.js"></script>
</body>
</html>
