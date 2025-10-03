<?php

class Auth {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    // Start user session
    public function startSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        $_SESSION['first_name'] = $user->first_name;
        $_SESSION['last_name'] = $user->last_name;
        $_SESSION['logged_in'] = true;
        
        // Create session token
        $session_token = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $session_token;
        
        // Store session in database
        $query = "INSERT INTO user_sessions (user_id, session_token, expires_at) 
                  VALUES (:user_id, :session_token, DATE_ADD(NOW(), INTERVAL 24 HOUR))";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user->id);
        $stmt->bindParam(":session_token", $session_token);
        $stmt->execute();
    }

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // Check user role
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    // Logout user
    public function logout() {
        if(isset($_SESSION['session_token'])) {
            // Remove session from database
            $query = "DELETE FROM user_sessions WHERE session_token = :session_token";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":session_token", $_SESSION['session_token']);
            $stmt->execute();
        }
        
        // Destroy session
        session_destroy();
        session_start();
    }

    // Require login
    public function requireLogin() {
        if(!$this->isLoggedIn()) {
            header("Location: ../login.php");
            exit();
        }
    }

    // Require specific role
    public function requireRole($role) {
        $this->requireLogin();
        if(!$this->hasRole($role)) {
            header("Location: ../unauthorized.php");
            exit();
        }
    }

    // Clean expired sessions
    public function cleanExpiredSessions() {
        $query = "DELETE FROM user_sessions WHERE expires_at < NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    }
}
?>
