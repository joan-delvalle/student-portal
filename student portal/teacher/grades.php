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
$message = "";
$message_type = "";

// Handle grade actions
if($_POST) {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add_grade':
                $query = "INSERT INTO grades (student_id, class_id, assignment_name, grade, max_grade, grade_date, comments)
                          VALUES (:student_id, :class_id, :assignment_name, :grade, :max_grade, :grade_date, :comments)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":student_id", $_POST['student_id']);
                $stmt->bindParam(":class_id", $_POST['class_id']);
                $stmt->bindParam(":assignment_name", $_POST['assignment_name']);
                $stmt->bindParam(":grade", $_POST['grade']);
                $stmt->bindParam(":max_grade", $_POST['max_grade']);
                $stmt->bindParam(":grade_date", $_POST['grade_date']);
                $stmt->bindParam(":comments", $_POST['comments']);
                
                if($stmt->execute()) {
                    $message = "Grade added successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to add grade.";
                    $message_type = "error";
                }
                break;
        }
    }
}

// Get teacher's classes for filter
$query = "SELECT c.*, s.name as subject_name
          FROM classes c
          LEFT JOIN subjects s ON c.subject_id = s.id
          WHERE c.teacher_id = :teacher_id
          ORDER BY c.name";
$stmt = $db->prepare($query);
$stmt->bindParam(":teacher_id", $teacher_id);
$stmt->execute();
$teacher_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get grades based on selected class
$selected_class = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$grades = [];
$students = [];

if($selected_class) {
    // Verify teacher owns this class
    $query = "SELECT id FROM classes WHERE id = :class_id AND teacher_id = :teacher_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":class_id", $selected_class);
    $stmt->bindParam(":teacher_id", $teacher_id);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        // Get grades for selected class
        $query = "SELECT g.*, u.first_name, u.last_name, u.username
                  FROM grades g
                  JOIN users u ON g.student_id = u.id
                  WHERE g.class_id = :class_id
                  ORDER BY u.last_name, u.first_name, g.grade_date DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":class_id", $selected_class);
        $stmt->execute();
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get students enrolled in this class
        $query = "SELECT u.id, u.first_name, u.last_name, u.username
                  FROM users u
                  JOIN enrollments e ON u.id = e.student_id
                  WHERE e.class_id = :class_id AND e.status = 'active'
                  ORDER BY u.last_name, u.first_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":class_id", $selected_class);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/teacher.css">
</head>
<body class="dashboard logged-in">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Grade Management</h1>
                <?php if($selected_class): ?>
                <button class="btn btn-primary" onclick="openModal('addGradeModal')">Add Grade</button>
                <?php endif; ?>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Class Selection -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Select Class</h2>
                </div>
                <div class="class-selection">
                    <form method="GET" class="selection-form">
                        <div class="form-group">
                            <select name="class_id" onchange="this.form.submit()">
                                <option value="">Select a class...</option>
                                <?php foreach($teacher_classes as $class): ?>
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
            
            <?php if($selected_class && !empty($grades)): ?>
            <!-- Grades Table -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Class Grades</h2>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student</th>
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
                                <td><?php echo htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']); ?></td>
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
            <?php elseif($selected_class): ?>
            <div class="card">
                <div class="empty-state">
                    <p>No grades recorded for this class yet.</p>
                    <button class="btn btn-primary" onclick="openModal('addGradeModal')">Add First Grade</button>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Add Grade Modal -->
    <?php if($selected_class): ?>
    <div id="addGradeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Grade</h2>
                <span class="close" onclick="closeModal('addGradeModal')">&times;</span>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="add_grade">
                <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                
                <div class="form-group">
                    <label for="student_id">Student</label>
                    <select id="student_id" name="student_id" required>
                        <option value="">Select Student</option>
                        <?php foreach($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>">
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="assignment_name">Assignment Name</label>
                    <input type="text" id="assignment_name" name="assignment_name" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="grade">Grade Earned</label>
                        <input type="number" id="grade" name="grade" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="max_grade">Maximum Grade</label>
                        <input type="number" id="max_grade" name="max_grade" step="0.01" min="0" value="100" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="grade_date">Grade Date</label>
                    <input type="date" id="grade_date" name="grade_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="comments">Comments (Optional)</label>
                    <textarea id="comments" name="comments" rows="3"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addGradeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Grade</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="../assets/js/teacher.js"></script>
</body>
</html>
