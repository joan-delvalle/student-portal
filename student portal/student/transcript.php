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

// Get student information
$query = "SELECT * FROM users WHERE id = :student_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":student_id", $student_id);
$stmt->execute();
$student_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Get academic transcript data
$query = "SELECT c.name as class_name, s.name as subject_name, s.code as subject_code,
                 c.academic_year, c.semester,
                 AVG((g.grade/g.max_grade)*4.0) as gpa_points,
                 AVG((g.grade/g.max_grade)*100) as percentage,
                 COUNT(g.id) as total_assignments
          FROM classes c
          JOIN subjects s ON c.subject_id = s.id
          JOIN enrollments e ON c.id = e.class_id
          LEFT JOIN grades g ON c.id = g.class_id AND g.student_id = e.student_id
          WHERE e.student_id = :student_id AND e.status = 'active'
          GROUP BY c.id
          ORDER BY c.academic_year DESC, c.semester DESC, s.name";

$stmt = $db->prepare($query);
$stmt->bindParam(":student_id", $student_id);
$stmt->execute();
$transcript_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate overall GPA
$total_gpa_points = 0;
$total_classes = 0;
foreach($transcript_data as $class) {
    if($class['gpa_points']) {
        $total_gpa_points += $class['gpa_points'];
        $total_classes++;
    }
}
$overall_gpa = $total_classes > 0 ? $total_gpa_points / $total_classes : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/student.css">
    <style>
        @media print {
            .sidebar, .navbar, .btn, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body class="dashboard logged-in">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header no-print">
                <h1 class="page-title">Academic Transcript</h1>
                <button onclick="window.print()" class="btn btn-primary">Print Transcript</button>
            </div>
            
            <!-- Student Information -->
            <div class="card transcript-header">
                <div class="transcript-title">
                    <h1><?php echo APP_NAME; ?></h1>
                    <h2>Official Academic Transcript</h2>
                </div>
                <div class="student-details">
                    <div class="detail-row">
                        <label>Student Name:</label>
                        <span><?php echo htmlspecialchars($student_info['first_name'] . ' ' . $student_info['last_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Student ID:</label>
                        <span><?php echo htmlspecialchars($student_info['username']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($student_info['email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Date of Birth:</label>
                        <span><?php echo $student_info['date_of_birth'] ? date('F j, Y', strtotime($student_info['date_of_birth'])) : 'N/A'; ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Overall GPA:</label>
                        <span class="gpa-highlight"><?php echo number_format($overall_gpa, 2); ?>/4.0</span>
                    </div>
                    <div class="detail-row">
                        <label>Transcript Date:</label>
                        <span><?php echo date('F j, Y'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Academic Record -->
            <?php if(!empty($transcript_data)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Academic Record</h2>
                </div>
                <div class="table-container">
                    <table class="transcript-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>Assignments</th>
                                <th>Average %</th>
                                <th>GPA Points</th>
                                <th>Letter Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_year = '';
                            foreach($transcript_data as $class): 
                                if($current_year !== $class['academic_year']):
                                    $current_year = $class['academic_year'];
                            ?>
                            <tr class="year-separator">
                                <td colspan="8"><strong>Academic Year: <?php echo $current_year; ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($class['subject_code']); ?></td>
                                <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                <td><?php echo htmlspecialchars($class['academic_year']); ?></td>
                                <td><?php echo htmlspecialchars($class['semester']); ?></td>
                                <td><?php echo $class['total_assignments']; ?></td>
                                <td>
                                    <?php if($class['percentage']): ?>
                                        <?php echo number_format($class['percentage'], 1); ?>%
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($class['gpa_points']): ?>
                                        <?php echo number_format($class['gpa_points'], 2); ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    if($class['percentage']) {
                                        $percentage = $class['percentage'];
                                        if($percentage >= 90) echo 'A';
                                        elseif($percentage >= 80) echo 'B';
                                        elseif($percentage >= 70) echo 'C';
                                        elseif($percentage >= 60) echo 'D';
                                        else echo 'F';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="gpa-summary">
                                <td colspan="6"><strong>Cumulative GPA:</strong></td>
                                <td><strong><?php echo number_format($overall_gpa, 2); ?></strong></td>
                                <td><strong>
                                    <?php 
                                    $overall_percentage = $overall_gpa * 25; // Convert 4.0 scale to percentage
                                    if($overall_percentage >= 90) echo 'A';
                                    elseif($overall_percentage >= 80) echo 'B';
                                    elseif($overall_percentage >= 70) echo 'C';
                                    elseif($overall_percentage >= 60) echo 'D';
                                    else echo 'F';
                                    ?>
                                </strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Grading Scale -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Grading Scale</h2>
                </div>
                <div class="grading-scale">
                    <div class="scale-item">
                        <span class="grade-letter">A</span>
                        <span class="grade-range">90-100%</span>
                        <span class="gpa-range">3.7-4.0</span>
                    </div>
                    <div class="scale-item">
                        <span class="grade-letter">B</span>
                        <span class="grade-range">80-89%</span>
                        <span class="gpa-range">2.7-3.6</span>
                    </div>
                    <div class="scale-item">
                        <span class="grade-letter">C</span>
                        <span class="grade-range">70-79%</span>
                        <span class="gpa-range">1.7-2.6</span>
                    </div>
                    <div class="scale-item">
                        <span class="grade-letter">D</span>
                        <span class="grade-range">60-69%</span>
                        <span class="gpa-range">0.7-1.6</span>
                    </div>
                    <div class="scale-item">
                        <span class="grade-letter">F</span>
                        <span class="grade-range">0-59%</span>
                        <span class="gpa-range">0.0-0.6</span>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="empty-state">
                    <p>No academic record available yet.</p>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="../assets/js/student.js"></script>
</body>
</html>
