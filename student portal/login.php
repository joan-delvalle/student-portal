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

// Redirect if already logged in
if($auth->isLoggedIn()) {
    switch($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'teacher':
            header("Location: teacher/dashboard.php");
            break;
        case 'student':
            header("Location: student/dashboard.php");
            break;
    }
    exit();
}

if($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
   /* echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
    echo "<strong>Debug Info:</strong><br>";
    echo "Database connection: " . ($db ? "Connected" : "Failed") . "<br>";
    echo "Username entered: " . htmlspecialchars($username) . "<br>";
    echo "Password entered: " . (strlen($password) > 0 ? "Yes (" . strlen($password) . " chars)" : "No") . "<br>";
    
    // Test if user exists in database
    $stmt = $db->prepare("SELECT id, username, password, role, is_active FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user_data) {
        echo "User found in database: " . $user_data['username'] . "<br>";
        echo "User role: " . $user_data['role'] . "<br>";
        echo "User active: " . ($user_data['is_active'] ? "Yes" : "No") . "<br>";
        echo "Password hash starts with: " . substr($user_data['password'], 0, 10) . "...<br>";
        echo "Password verify result: " . (password_verify($password, $user_data['password']) ? "SUCCESS" : "FAILED") . "<br>";
    } else {
        echo "User NOT found in database<br>";
    }
    echo "</div>";*/
    
    if($user->login($username, $password)) {
        $auth->startSession($user);
        
        // Redirect based on role
        switch($user->role) {
            case 'admin':
                header("Location: admin/dashboard.php");
                break;
            case 'teacher':
                header("Location: teacher/dashboard.php");
                break;
            case 'student':
                header("Location: student/dashboard.php");
                break;
        }
        exit();
    } else {
        $error_message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>School Management System</h1>
                <h2>Login</h2>
            </div>
            
            <?php if($error_message): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Login</button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
            </div>
        </div>
    </div>
    
    <script src="assets/js/auth.js"></script>
</body>
</html>
