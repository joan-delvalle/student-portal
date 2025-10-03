<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Require admin role
$auth->requireRole('admin');

// Get dashboard statistics
$stats = [];

// Total users by role
$query = "SELECT role, COUNT(*) as count FROM users WHERE is_active = 1 GROUP BY role";
$stmt = $db->prepare($query);
$stmt->execute();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stats[$row['role']] = $row['count'];
}

// Total subjects
$query = "SELECT COUNT(*) as count FROM subjects";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['subjects'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total classes
$query = "SELECT COUNT(*) as count FROM classes";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['classes'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total enrollments
$query = "SELECT COUNT(*) as count FROM enrollments WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['enrollments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Recent activities
$query = "SELECT u.first_name, u.last_name, u.role, u.created_at 
          FROM users u 
          WHERE u.is_active = 1 
          ORDER BY u.created_at DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="dashboard logged-in">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Admin Dashboard</h1>
                <p class="page-subtitle">Welcome back, <?php echo $_SESSION['first_name']; ?>!</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon admin-icon">👨‍💼</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['admin'] ?? 0; ?></h3>
                        <p>Administrators</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon teacher-icon">👩‍🏫</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['teacher'] ?? 0; ?></h3>
                        <p>Teachers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon student-icon">👨‍🎓</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['student'] ?? 0; ?></h3>
                        <p>Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon subject-icon">📚</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['subjects']; ?></h3>
                        <p>Subjects</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon class-icon">🏫</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['classes']; ?></h3>
                        <p>Classes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon enrollment-icon">📝</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['enrollments']; ?></h3>
                        <p>Active Enrollments</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="dashboard-section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent User Registrations</h2>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="dashboard-section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Quick Actions</h2>
                    </div>
                    <div class="quick-actions">
                        <a href="users.php" class="action-btn">
                            <span class="action-icon">👥</span>
                            <span>Manage Users</span>
                        </a>
                        <a href="subjects.php" class="action-btn">
                            <span class="action-icon">📖</span>
                            <span>Manage Subjects</span>
                        </a>
                        <a href="classes.php" class="action-btn">
                            <span class="action-icon">🏛️</span>
                            <span>Manage Classes</span>
                        </a>
                        <a href="reports.php" class="action-btn">
                            <span class="action-icon">📊</span>
                            <span>View Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>
