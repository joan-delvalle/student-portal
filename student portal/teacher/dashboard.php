<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Require teacher role
$auth->requireRole('teacher');

$teacher_id = $_SESSION['user_id'];

// Get teacher's classes
$query = "SELECT c.*, s.name as subject_name, s.code as subject_code,
                 COUNT(e.student_id) as enrolled_students
          FROM classes c
          LEFT JOIN subjects s ON c.subject_id = s.id
          LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
          WHERE c.teacher_id = :teacher_id
          GROUP BY c.id
          ORDER BY c.created_at DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(":teacher_id", $teacher_id);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent grades added by this teacher
$query = "SELECT g.*, u.first_name, u.last_name, c.name as class_name
          FROM grades g
          JOIN users u ON g.student_id = u.id
          JOIN classes c ON g.class_id = c.id
          WHERE c.teacher_id = :teacher_id
          ORDER BY g.created_at DESC
          LIMIT 5";

$stmt = $db->prepare($query);
$stmt->bindParam(":teacher_id", $teacher_id);
$stmt->execute();
$recent_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats = [];

// Total classes
$stats['total_classes'] = count($classes);

// Total students across all classes
$query = "SELECT COUNT(DISTINCT e.student_id) as count
          FROM enrollments e
          JOIN classes c ON e.class_id = c.id
          WHERE c.teacher_id = :teacher_id AND e.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(":teacher_id", $teacher_id);
$stmt->execute();
$stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total grades given
$query = "SELECT COUNT(*) as count
          FROM grades g
          JOIN classes c ON g.class_id = c.id
          WHERE c.teacher_id = :teacher_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":teacher_id", $teacher_id);
$stmt->execute();
$stats['total_grades'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/teacher.css">
</head>
<body class="dashboard logged-in">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Teacher Dashboard</h1>
                <p class="page-subtitle">Welcome back, <?php echo $_SESSION['first_name']; ?>!</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon classes-icon">🏫</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_classes']; ?></h3>
                        <p>My Classes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon students-icon">👨‍🎓</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_students']; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon grades-icon">📊</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_grades']; ?></h3>
                        <p>Grades Given</p>
                    </div>
                </div>
            </div>
            
            <!-- My Classes -->
            <div class="dashboard-section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">My Classes</h2>
                    </div>
                    <div class="classes-grid">
                        <?php foreach($classes as $class): ?>
                        <div class="class-card">
                            <div class="class-header">
                                <h3><?php echo htmlspecialchars($class['name']); ?></h3>
                                <span class="subject-code"><?php echo htmlspecialchars($class['subject_code']); ?></span>
                            </div>
                            <div class="class-info">
                                <p><strong>Subject:</strong> <?php echo htmlspecialchars($class['subject_name']); ?></p>
                                <p><strong>Students:</strong> <?php echo $class['enrolled_students']; ?></p>
                                <p><strong>Academic Year:</strong> <?php echo $class['academic_year']; ?></p>
                                <p><strong>Semester:</strong> <?php echo $class['semester']; ?></p>
                            </div>
                            <div class="class-actions">
                                <a href="class_details.php?id=<?php echo $class['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                <a href="grades.php?class_id=<?php echo $class['id']; ?>" class="btn btn-secondary btn-sm">Manage Grades</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if(empty($classes)): ?>
                        <div class="empty-state">
                            <p>No classes assigned yet. Contact your administrator to get classes assigned.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Grades -->
            <?php if(!empty($recent_grades)): ?>
            <div class="dashboard-section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Grades</h2>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Assignment</th>
                                    <th>Grade</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_grades as $grade): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['assignment_name']); ?></td>
                                    <td>
                                        <span class="grade-display">
                                            <?php echo $grade['grade']; ?>/<?php echo $grade['max_grade']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($grade['grade_date'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="../assets/js/teacher.js"></script>
</body>
</html>
