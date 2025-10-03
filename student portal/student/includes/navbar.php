<nav class="navbar">
    <div class="navbar-content">
        <div class="navbar-brand">
            <a href="dashboard.php"><?php echo APP_NAME; ?></a>
        </div>
        
        <div class="navbar-user">
            <div class="user-info">
                <div class="user-name"><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></div>
                <div class="user-role"><?php echo $_SESSION['role']; ?></div>
            </div>
            <div class="user-actions">
                <a href="../logout.php" class="btn btn-outline">Logout</a>
            </div>
        </div>
    </div>
</nav>
