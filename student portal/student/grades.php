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

// Get student's classes for filter
$query = "SELECT c.*, s.name as subject_name
          FROM classes c
          JOIN subjects s ON c.subject_id = s.id
          JOIN enrollments e ON c.id = e.class_id
          WHERE e.student_id = :student_id AND e.status = 'active'
          ORDER BY c.name";
$stmt = $db->prepare($query);
$stmt->bindParam(":student_id", $student_id);
$stmt->execute();
$student_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get grades based on selected class
$selected_class = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$grades = [];
$class_info = null;

if($selected_class) {
    // Verify student is enrolled in this class
    $query = "SELECT c.*, s.name as subject_name, s.code as subject_code,
                     u.first_name as teacher_first_name, u.last_name as teacher_last_name
              FROM classes c
              JOIN subjects s ON c.subject_id = s.id
              JOIN users u ON c.teacher_id = u.id
              JOIN enrollments e ON c.id = e.class_id
              WHERE c.id = :class_id AND e.student_id = :student_id AND e.status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":class_id", $selected_class);
    $stmt->bindParam(":student_id", $student_id);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $class_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get grades for selected class
        $query = "SELECT g.*
                  FROM grades g
                  WHERE g.class_id = :class_id AND g.student_id = :student_id
                  ORDER BY g.grade_date DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":class_id", $selected_class);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    // Get all grades for student
    $query = "SELECT g.*, c.name as class_name, s.name as subject_name
              FROM grades g
              JOIN classes c ON g.class_id = c.id
              JOIN subjects s ON c.subject_id = s.id
              WHERE g.student_id = :student_id
              ORDER BY g.grade_date DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":student_id", $student_id);
    $stmt->execute();
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate class average if specific class selected
$class_average = null;
if($selected_class && !empty($grades)) {
    $total_percentage = 0;
    foreach($grades as $grade) {
        $total_percentage += ($grade['grade'] / $grade['max_grade']) * 100;
    }
    $class_average = number_format($total_percentage / count($grades), 1);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body class="dashboard logged-in">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">My Grades</h1>
                <?php if($class_info): ?>
                <p class="page-subtitle"><?php echo htmlspecialchars($class_info['name']); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Class Selection -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Select Class</h2>
                </div>
                <div class="class-selection">
                    <form method="GET" class="selection-form">
                        <div class="form-group">
                            <select name="class_id" onchange="this.form.submit()">
                                <option value="">All Classes</option>
                                <?php foreach($student_classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" 
                                        <?php echo $selected_class == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name'] . ' - ' . $class['subject_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if($class_info): ?>
            <!-- Class Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Class Information</h2>
                </div>
                <div class="class-info-display">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Subject:</label>
                            <span><?php echo htmlspecialchars($class_info['subject_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Teacher:</label>
                            <span><?php echo htmlspecialchars($class_info['teacher_first_name'] . ' ' . $class_info['teacher_last_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Academic Year:</label>
                            <span><?php echo htmlspecialchars($class_info['academic_year']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Class Average:</label>
                            <span><?php echo $class_average ? $class_average . '%' : 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Grades Table -->
            <?php if(!empty($grades)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <?php echo $selected_class ? 'Class Grades' : 'All Grades'; ?>
                    </h2>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <?php if(!$selected_class): ?>
                                <th>Class</th>
                                <?php endif; ?>
                                <th>Assignment</th>
                                <th>Grade</th>
                                <th>Percentage</th>
                                <th>Date</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($grades as $grade): ?>
                            <tr>
                                <?php if(!$selected_class): ?>
                                <td><?php echo htmlspecialchars($grade['class_name']); ?></td>
                                <?php endif; ?>
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
                                <td><?php echo htmlspecialchars($grade['comments']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="empty-state">
                    <p><?php echo $selected_class ? 'No grades recorded for this class yet.' : 'No grades recorded yet.'; ?></p>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="../assets/js/student.js"></script>
</body>
</html>
