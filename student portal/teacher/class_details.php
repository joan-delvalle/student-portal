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
$class_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Verify teacher owns this class and get class details
$query = "SELECT c.*, s.name as subject_name, s.code as subject_code, s.description as subject_description
          FROM classes c
          LEFT JOIN subjects s ON c.subject_id = s.id
          WHERE c.id = :class_id AND c.teacher_id = :teacher_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":class_id", $class_id);
$stmt->bindParam(":teacher_id", $teacher_id);
$stmt->execute();

if($stmt->rowCount() == 0) {
    header("Location: dashboard.php");
    exit();
}

$class = $stmt->fetch(PDO::FETCH_ASSOC);

// Get enrolled students
$query = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, e.enrollment_date
          FROM users u
          JOIN enrollments e ON u.id = e.student_id
          WHERE e.class_id = :class_id AND e.status = 'active'
          ORDER BY u.last_name, u.first_name";
$stmt = $db->prepare($query);
$stmt->bindParam(":class_id", $class_id);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get class statistics
$stats = [];
$stats['total_students'] = count($students);

// Average grade for this class
$query = "SELECT AVG((grade/max_grade)*100) as avg_grade
          FROM grades
          WHERE class_id = :class_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":class_id", $class_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['avg_grade'] = $result['avg_grade'] ? number_format($result['avg_grade'], 1) : 'N/A';

// Total assignments
$query = "SELECT COUNT(DISTINCT assignment_name) as count
          FROM grades
          WHERE class_id = :class_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":class_id", $class_id);
$stmt->execute();
$stats['total_assignments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($class['name']); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/teacher.css">
</head>
<body class="dashboard logged-in">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title"><?php echo htmlspecialchars($class['name']); ?></h1>
                    <p class="page-subtitle"><?php echo htmlspecialchars($class['subject_name']); ?> - <?php echo htmlspecialchars($class['subject_code']); ?></p>
                </div>
                <div class="header-actions">
                    <a href="grades.php?class_id=<?php echo $class_id; ?>" class="btn btn-primary">Manage Grades</a>
                    <a href="attendance.php?class_id=<?php echo $class_id; ?>" class="btn btn-secondary">Take Attendance</a>
                </div>
            </div>
            
            <!-- Class Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Class Information</h2>
                </div>
                <div class="class-info-grid">
                    <div class="info-item">
                        <label>Academic Year:</label>
                        <span><?php echo htmlspecialchars($class['academic_year']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Semester:</label>
                        <span><?php echo htmlspecialchars($class['semester']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Max Students:</label>
                        <span><?php echo htmlspecialchars($class['max_students']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Subject Description:</label>
                        <span><?php echo htmlspecialchars($class['subject_description']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Class Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon students-icon">👨‍🎓</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_students']; ?></h3>
                        <p>Enrolled Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon average-icon">📊</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['avg_grade']; ?><?php echo $stats['avg_grade'] !== 'N/A' ? '%' : ''; ?></h3>
                        <p>Class Average</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon assignments-icon">📝</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_assignments']; ?></h3>
                        <p>Assignments</p>
                    </div>
                </div>
            </div>
            
            <!-- Enrolled Students -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Enrolled Students</h2>
                </div>
                <?php if(!empty($students)): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Enrollment Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone'] ?: 'N/A'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($student['enrollment_date'])); ?></td>
                                <td class="actions">
                                    <a href="student_grades.php?student_id=<?php echo $student['id']; ?>&class_id=<?php echo $class_id; ?>" 
                                       class="btn btn-sm btn-outline">View Grades</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>No students enrolled in this class yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/teacher.js"></script>
</body>
</html>
