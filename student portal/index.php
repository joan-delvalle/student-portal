<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .role-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }
        
        .role-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .role-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .role-card h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .role-card p {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .features-section {
            background: #f8f9fa;
            padding: 4rem 2rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .feature-item {
            text-align: center;
            padding: 1.5rem;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1><?php echo APP_NAME; ?></h1>
            <p>Comprehensive educational management platform for administrators, teachers, and students</p>
            <div style="margin-top: 2rem;">
                <a href="login.php" class="btn btn-primary" style="margin-right: 1rem;">Login</a>
                <a href="signup.php" class="btn btn-outline" style="background: rgba(255,255,255,0.2); border-color: white; color: white;">Sign Up</a>
            </div>
        </div>
    </section>

    <!-- Role Cards Section -->
    <section class="role-cards">
        <div class="role-card">
            <div class="role-icon">👨‍💼</div>
            <h3>Administrators</h3>
            <p>Manage users, oversee system operations, generate reports, and maintain institutional data with comprehensive administrative tools.</p>
            <a href="login.php" class="btn btn-primary">Admin Login</a>
        </div>
        
        <div class="role-card">
            <div class="role-icon">👩‍🏫</div>
            <h3>Teachers</h3>
            <p>Manage classes, track student progress, input grades, monitor attendance, and communicate with students effectively.</p>
            <a href="login.php" class="btn btn-primary">Teacher Login</a>
        </div>
        
        <div class="role-card">
            <div class="role-icon">👨‍🎓</div>
            <h3>Students</h3>
            <p>Access grades, view class schedules, track academic progress, and stay connected with your educational journey.</p>
            <a href="login.php" class="btn btn-primary">Student Login</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 style="color: #333; font-size: 2.5rem; margin-bottom: 1rem;">Key Features</h2>
            <p style="color: #666; font-size: 1.1rem;">Everything you need for effective school management</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">🔐</div>
                <h4>Secure Authentication</h4>
                <p>Role-based access control with secure login system</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">📊</div>
                <h4>Grade Management</h4>
                <p>Comprehensive grading system with GPA calculation</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">📋</div>
                <h4>Attendance Tracking</h4>
                <p>Monitor student attendance with detailed reports</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">👥</div>
                <h4>User Management</h4>
                <p>Efficient management of students, teachers, and staff</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">📚</div>
                <h4>Course Management</h4>
                <p>Organize subjects, classes, and academic schedules</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">📈</div>
                <h4>Progress Reports</h4>
                <p>Generate detailed academic progress reports</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
        <p>Version <?php echo APP_VERSION; ?></p>
    </footer>
</body>
</html>
