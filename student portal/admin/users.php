<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Auth.php';
require_once '../classes/User.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);
$user = new User($db);

// Require admin role
$auth->requireRole('admin');

$message = "";
$message_type = "";

// Handle user actions
if($_POST) {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $user->username = $_POST['username'];
                $user->email = $_POST['email'];
                $user->password = $_POST['password'];
                $user->role = $_POST['role'];
                $user->first_name = $_POST['first_name'];
                $user->last_name = $_POST['last_name'];
                $user->phone = $_POST['phone'];
                $user->address = $_POST['address'];
                $user->date_of_birth = $_POST['date_of_birth'];
                
                if($user->create()) {
                    $message = "User created successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to create user.";
                    $message_type = "error";
                }
                break;
                
            case 'toggle_status':
                $user_id = $_POST['user_id'];
                $current_status = $_POST['current_status'];
                $new_status = $current_status ? 0 : 1;
                
                $query = "UPDATE users SET is_active = :status WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":status", $new_status);
                $stmt->bindParam(":id", $user_id);
                
                if($stmt->execute()) {
                    $message = "User status updated successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to update user status.";
                    $message_type = "error";
                }
                break;
        }
    }
}

// Get all users
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if($search) {
    $query .= " AND (first_name LIKE :search OR last_name LIKE :search OR username LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

if($role_filter) {
    $query .= " AND role = :role";
    $params[':role'] = $role_filter;
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="dashboard logged-in">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">User Management</h1>
                <button class="btn btn-primary" onclick="openModal('createUserModal')">Add New User</button>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="card">
                <div class="filters">
                    <form method="GET" class="filter-form">
                        <div class="filter-group">
                            <input type="text" name="search" placeholder="Search users..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="filter-group">
                            <select name="role">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="teacher" <?php echo $role_filter === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                <option value="student" <?php echo $role_filter === 'student' ? 'selected' : ''; ?>>Student</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary">Filter</button>
                        <a href="users.php" class="btn btn-outline">Clear</a>
                    </form>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="card">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user_row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user_row['first_name'] . ' ' . $user_row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user_row['username']); ?></td>
                                <td><?php echo htmlspecialchars($user_row['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user_row['role']; ?>">
                                        <?php echo ucfirst($user_row['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user_row['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $user_row['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user_row['created_at'])); ?></td>
                                <td class="actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo $user_row['id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $user_row['is_active']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline">
                                            <?php echo $user_row['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Create User Modal -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <span class="close" onclick="closeModal('createUserModal')">&times;</span>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="create">
                
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
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>
