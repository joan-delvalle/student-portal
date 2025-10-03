<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Require student role
$auth->requireRole('student');

$student_id = $_SESSION['user_id'];

// Get student's enrolled classes
$query = "SELECT c.*, s.name as subject_name, s.code as subject_code,
                 u.first_name as teacher_first_name, u.last_name as teacher_last_name,
                 e.enrollment_date
          FROM classes c
          JOIN subjects s ON c.subject_id = s.id
          JOIN users u ON c.teacher_id = u.id
          JOIN enrollments e ON c.id = e.class_id
          WHERE e.student_id = :student_id AND e.status = 'active'
          ORDER BY c.name";

$stmt = $db->prepare($query);
$stmt->bindParam(":student_id", $student_id);
$stmt->execute();
$enrolled_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent grades
$query = "SELECT g.*, c.name as class_name, s.name as subject_name
          FROM grades g
          JOIN classes c ON g.class_id = c.id
          JOIN subjects s ON c.subject_id = s.id
          WHERE g.student_id = :student_id
          ORDER BY g.grade_date DESC
          LIMIT 5";

$stmt = $db->prepare($query);
$stmt->bindParam(":student_id", $student_id);
$stmt->execute();
$recent_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats = [];

// Total enrolled classes
$stats['total_classes'] = count($enrolled_classes);

// Overall GPA calculation
$query = "SELECT AVG((grade/max_grade)*4.0) as gpa
          FROM grades g
          JOIN classes c ON g.class_id = c.id
          JOIN enrollments e ON c.id = e.class_id
          WHERE e.student_id = :student_id AND e.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(":student_id", $student_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['gpa'] = $result['gpa'] ? number_format($result['gpa'], 2) : 'N/A';

// Total assignments completed
$query = "SELECT COUNT(*) as count
          FROM grades g
          JOIN classes c ON g.class_id = c.id
          JOIN enrollments e ON c.id = e.class_id
          WHERE e.student_id = :student_id AND e.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(":student_id", $student_id);
$stmt->execute();
$stats['total_assignments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Attendance rate
$query = "SELECT 
            COUNT(*) as total_records,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count
          FROM attendance a
          JOIN classes c ON a.class_id = c.id
          JOIN enrollments e ON c.id = e.class_id
          WHERE e.student_id = :student_id AND e.status = 'active'";
$stmt = $db->prepare($query);
$stmt->bindParam(":student_id", $student_id);
$stmt->execute();
$attendance_data = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['attendance_rate'] = $attendance_data['total_records'] > 0 ? 
    number_format(($attendance_data['present_count'] / $attendance_data['total_records']) * 100, 1) : 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body class="dashboard logged-in">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Student Dashboard</h1>
                <p class="page-subtitle">Welcome back, <?php echo $_SESSION['first_name']; ?>!</p>
            </div>
            
            <!-- Academic Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon classes-icon">📚</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_classes']; ?></h3>
                        <p>Enrolled Classes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon gpa-icon">🎯</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['gpa']; ?></h3>
                        <p>Current GPA</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon assignments-icon">📝</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_assignments']; ?></h3>
                        <p>Assignments Completed</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon attendance-icon">✅</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['attendance_rate']; ?><?php echo $stats['attendance_rate'] !== 'N/A' ? '%' : ''; ?></h3>
                        <p>Attendance Rate</p>
                    </div>
                </div>
            </div>
            
            <!-- Enrolled Classes -->
            <div class="dashboard-section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">My Classes</h2>
                    </div>
                    <?php if(!empty($enrolled_classes)): ?>
                    <div class="classes-grid">
                        <?php foreach($enrolled_classes as $class): ?>
                        <div class="class-card">
                            <div class="class-header">
                                <h3><?php echo htmlspecialchars($class['name']); ?></h3>
                                <span class="subject-code"><?php echo htmlspecialchars($class['subject_code']); ?></span>
                            </div>
                            <div class="class-info">
                                <p><strong>Subject:</strong> <?php echo htmlspecialchars($class['subject_name']); ?></p>
                                <p><strong>Teacher:</strong> <?php echo htmlspecialchars($class['teacher_first_name'] . ' ' . $class['teacher_last_name']); ?></p>
                                <p><strong>Academic Year:</strong> <?php echo $class['academic_year']; ?></p>
                                <p><strong>Semester:</strong> <?php echo $class['semester']; ?></p>
                            </div>
                            <div class="class-actions">
                                <a href="class_details.php?id=<?php echo $class['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                <a href="grades.php?class_id=<?php echo $class['id']; ?>" class="btn btn-secondary btn-sm">View Grades</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <p>You are not enrolled in any classes yet. Contact your administrator for enrollment.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Grades -->
            <?php if(!empty($recent_grades)): ?>
            <div class="dashboard-section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Grades</h2>
                        <a href="grades.php" class="btn btn-outline btn-sm">View All Grades</a>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Assignment</th>
                                    <th>Grade</th>
                                    <th>Percentage</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_grades as $grade): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($grade['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['assignment_name']); ?></td>
                                    <td>
                                        <span class="grade-display">
                                            <?php echo $grade['grade']; ?>/<?php echo $grade['max_grade']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $percentage = ($grade['grade'] / $grade['max_grade']) * 100;
                                        $grade_class = '';
                                        if($percentage >= 90) $grade_class = 'grade-a';
                                        elseif($percentage >= 80) $grade_class = 'grade-b';
                                        elseif($percentage >= 70) $grade_class = 'grade-c';
                                        elseif($percentage >= 60) $grade_class = 'grade-d';
                                        else $grade_class = 'grade-f';
                                        ?>
                                        <span class="percentage-badge <?php echo $grade_class; ?>">
                                            <?php echo number_format($percentage, 1); ?>%
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
            
            <!-- Quick Actions -->
            <div class="dashboard-section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Quick Actions</h2>
                    </div>
                    <div class="quick-actions">
                        <a href="grades.php" class="action-btn">
                            <span class="action-icon">📊</span>
                            <span>View All Grades</span>
                        </a>
                        <a href="attendance.php" class="action-btn">
                            <span class="action-icon">📋</span>
                            <span>Check Attendance</span>
                        </a>
                        <a href="transcript.php" class="action-btn">
                            <span class="action-icon">📜</span>
                            <span>Academic Transcript</span>
                        </a>
                        <a href="profile.php" class="action-btn">
                            <span class="action-icon">👤</span>
                            <span>Update Profile</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/student.js"></script>
</body>
</html>
